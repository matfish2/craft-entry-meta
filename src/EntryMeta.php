<?php

namespace matfish\EntryMeta;

use Closure;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\db\ActiveQuery;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
use craft\events\PopulateElementEvent;
use matfish\EntryMeta\behaviors\ActiveQueryBehavior;
use matfish\EntryMeta\behaviors\ElementBehavior;
use matfish\EntryMeta\models\Settings;
use matfish\EntryMeta\services\ClassesMap;
use matfish\EntryMeta\services\MetadataColumnMigrator;
use matfish\EntryMeta\services\MetadataTableDetector;
use matfish\EntryMeta\twig\EntryMetaExtension;
use yii\base\Event;
use craft\base\Model;
use yii\db\Exception;

class EntryMeta extends Plugin
{
    public const COLUMN_NAME = 'emMetadata';

    public $hasCpSettings = true;

    public function init()
    {
        parent::init();

        $this->registerActiveQueryBehaviors();
        $this->registerElementBehaviors();

        if ($this->settings->displayMetadataInCp && Craft::$app->request->getIsCpRequest()) {
            $this->registerCpMetaHooks();
        }
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): null|string
    {
        return \Craft::$app->getView()->renderTemplate(
            'entry-meta/settings',
            [
                'settings' => $this->getSettings(),
                'options' => $this->getOptions()
            ]
        );
    }

    protected function getOptions()
    {
        $options = [
            [
                'value' => 'entry',
                'label' => 'Entry',
            ],
            [
                'value' => 'category',
                'label' => 'Category'
            ],
            [
                'value' => 'asset',
                'label' => 'Asset'
            ],
            [
                'value' => 'user',
                'label' => 'User'
            ],
            [
                'value' => 'tag',
                'label' => 'Tag'
            ]
        ];

        $enabled = $this->getEnabled();

        foreach ($options as &$option) {
            if (in_array($option['value'], $enabled, true)) {
                $option['checked'] = true;
                $option['disabled'] = true;
            } else {
                $option['checked'] = false;
            }
        }

        return $options;
    }

    protected function getEnabled(): array
    {
        return $this->eMcache('emEnabled', function () {
            $res = [];
            $migrator = new MetadataColumnMigrator();
            $detector = new MetadataTableDetector();
            foreach (array_keys(ClassesMap::LOOK_UP) as $key) {

                try {
                    $table = $detector->detect($key);
                } catch (\Exception $e) {
                    continue;
                }

                if ($migrator->columnExists($table)) {
                    $res[] = $key;
                }
            }

            return $res;
        });
    }

    private function getActiveRecordFromElementClass($elClass)
    {
        return $this->eMcache('emActiveRecordFromElementClass' . $elClass, function () use ($elClass) {
            foreach ($this->getAllEnabled() as $val) {
                if ($val[1] === $elClass) {
                    return $val[0];
                }
            }

            throw new Exception("Cannot retrieve active record class for element {$elClass}");
        });
    }

    public function getAllEnabled()
    {
        return $this->eMcache('emAllEnabled', function () {
            $res = [];

            foreach ($this->getEnabled() as $key) {
                $res[] = ClassesMap::LOOK_UP[$key];
            }

            if (is_array($this->settings->enabledForCustom)) {
                foreach ($this->settings->enabledForCustom as $val) {
                    $res[] = array_reverse($val);
                }
            }

            return $res;
        });
    }

    public function getEnabledActiveRecords(): array
    {
        return $this->eMcache('emEnabledActiveRecords', function () {
            $res = [];

            foreach ($this->getEnabled() as $key) {
                $res[] = ClassesMap::LOOK_UP[$key][0];
            }

            if (is_array($this->settings->enabledForCustom)) {
                foreach ($this->settings->enabledForCustom as $val) {
                    $res[] = $val[1];
                }

            }

            return $res;
        });
    }


    private function getEnabledElements(): array
    {
        return $this->eMcache('emEnabledElements', function () {
            $res = [];

            foreach ($this->getEnabled() as $key) {
                $res[] = ClassesMap::LOOK_UP[$key][1];
            }

            if (is_array($this->settings->enabledForCustom)) {
                foreach ($this->settings->enabledForCustom as $val) {
                    $res[] = $val[0];
                }
            }

            return $res;
        });
    }

    public function afterSaveSettings(): void
    {
        $migrator = new MetadataColumnMigrator();
        $detector = new MetadataTableDetector();

        foreach ($this->settings->enabledFor as $key) {
            $table = $detector->detect($key);

            $migrator->add($table);
        }

        if (is_array($this->settings->enabledForCustom)) {
            foreach ($this->settings->enabledForCustom as $val) {
                $table = $detector->detect($val[1]);

                $migrator->add($table);
            }
        }

        $cache = \Craft::$app->cache;
        $cache->delete('emEnabledElements');
        $cache->delete('emEnabledActiveRecords');
        $cache->delete('emAllEnabled');
        $cache->delete('emEnabled');

        parent::afterSaveSettings();
    }

    private function registerActiveQueryBehaviors(): void
    {
        Event::on(ActiveQuery::class, ActiveQuery::EVENT_INIT, function ($e) {
            $activeRecords = $this->getEnabledActiveRecords();

            if (in_array($e->sender->modelClass, $activeRecords, true)) {
                $e->sender->attachBehaviors([ActiveQueryBehavior::class]);
            }
        });
    }

    private function registerElementBehaviors(): void
    {
        Event::on(ElementQuery::class, ElementQuery::EVENT_AFTER_POPULATE_ELEMENT, function (PopulateElementEvent $event) {
            $element = $event->element;
            $elClass = get_class($element);

            if (in_array($elClass, $this->getEnabledElements(), true)) {
                $arClass = $this->getActiveRecordFromElementClass($elClass);
                $table = (new MetadataTableDetector())->detect($arClass);
                $element->attachBehavior('metadata', new ElementBehavior($table));
            }
        });

    }

    private function registerCpMetaHooks(): void
    {
        Craft::$app->view->registerTwigExtension(new EntryMetaExtension());

        $lookup = [
            'user' => 'cp.users.edit.details',
            'entry' => 'cp.entries.edit.details',
            'category' => 'cp.categories.edit.details',
            'asset' => 'cp.assets.edit.details',
        ];

        $enabled = $this->getEnabled();

        foreach ($enabled as $el) {
            if (isset($lookup[$el])) {
                Craft::$app->getView()->hook($lookup[$el], function (array &$context) use ($el) {

                    $entry = $el==='asset' ? $context['element'] : $context[$el];

                    $meta = $entry->getElementMetadata();

                    return Craft::$app->view->renderTemplate('entry-meta/_meta', [
                        'data' => $meta
                    ]);
                });
            }
        }

    }

    private function eMcache($handle, Closure $callback)
    {
        return \Craft::$app->cache->getOrSet($handle, function () use ($handle, $callback) {
            return $callback($handle);
        }, 60 * 60 * 24 * 365);
    }

}