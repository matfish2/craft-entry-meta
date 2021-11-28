<?php

namespace matfish\EntryMeta;

use Craft;
use craft\base\Plugin;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\PopulateElementEvent;
use matfish\EntryMeta\behaviors\EntryMetaBehavior;
use matfish\EntryMeta\models\Settings;
use matfish\EntryMeta\services\EntryMetaQueryService;
use yii\base\Event;

class EntryMeta extends Plugin
{
    const COLUMN_NAME = 'metadata';
    const CP_HOOK = 'cp.entries.edit.meta';

    public function init()
    {
        parent::init();

        $this->setComponents([
            'query' => EntryMetaQueryService::class
        ]);

        /**
         * Attach a behavior after an entry has been loaded from the database (populated).
         */
        Event::on(ElementQuery::class, ElementQuery::EVENT_AFTER_POPULATE_ELEMENT, function (PopulateElementEvent $event) {
            $element = $event->element;

            if ($element instanceof Entry) {
                $element->attachBehavior('metadata', EntryMetaBehavior::class);
            }
        });

        if ($this->settings->displayMetadataInCp) {
            Craft::$app->getView()->hook(self::CP_HOOK, function (array &$context) {

                $entry = $context['entry'];
                $meta = $entry->getEntryMetadata();

                return Craft::$app->view->renderTemplate('entry-meta/_meta', [
                    'data' => $meta
                ]);
            });
        }
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

}