<?php

namespace matfish\EntryMeta;

use Closure;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\db\ActiveQuery;
use craft\elements\db\ElementQuery;
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

    public string $schemaVersion = '1.0.1';

    public bool $hasCpSettings = true;

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
            $option['checked'] = in_array($option['value'], $enabled, true);
        }

        return $options;
    }

    protected function getEnabled(): array
    {
        return $this->eMcache('emEnabled', function () {
            return $this->settings->enabledFor;
        });
    }

    public function getAllEnabled()
    {
        return $this->eMcache('emAllEnabled', function () {
            $res = [];

            foreach ($this->getEnabled() as $key) {
                $res[] = ClassesMap::LOOK_UP[$key];
            }

            foreach ($this->settings->enabledForCustom as $val) {
                $res[] = array_reverse($val);
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


    public function getEnabledActiveRecords(): array
    {
        return $this->eMcache('emEnabledActiveRecords', function () {
            $res = [];

            foreach ($this->getAllEnabled() as $row) {
                $res[] = $row[0];
            }

            return $res;
        });
    }


    private function getEnabledElements(): array
    {
        return $this->eMcache('emEnabledElements', function () {
            $res = [];

            foreach ($this->getAllEnabled() as $row) {
                  $res[] = $row[1];
            }

            return $res;
        });
    }

    public function afterSaveSettings(): void
    {
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

        $enabled = $this->getEnabledElements();

        foreach ($enabled as $el) {

            Event::on(
                $el,
                Element::EVENT_DEFINE_SIDEBAR_HTML,
                function (DefineHtmlEvent $event) {
                    $entry = $event->sender;
                    $meta = $entry->getElementMetadata();
                    $template = Craft::$app->view->renderTemplate('entry-meta/_meta', [
                        'data' => $meta
                    ]);

                    $event->html .= $template;
                }
            );
        }

    }

    private function eMcache($handle, Closure $callback)
    {
        return \Craft::$app->cache->getOrSet($handle, function () use ($handle, $callback) {
            return $callback($handle);
        }, 60 * 60 * 24 * 365);
    }

}