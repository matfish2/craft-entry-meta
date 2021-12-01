<?php

namespace matfish\EntryMeta;

use Craft;
use craft\base\Plugin;
use craft\db\ActiveQuery;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\PopulateElementEvent;
use matfish\EntryMeta\behaviors\EntryActiveQueryBehavior;
use matfish\EntryMeta\behaviors\EntryElementBehavior;
use matfish\EntryMeta\models\Settings;
use matfish\EntryMeta\twig\EntryMetaExtension;
use yii\base\Event;
use craft\records\Entry as EntryRecord;

class EntryMeta extends Plugin
{
    const COLUMN_NAME = 'metadata';
    const CP_HOOK = 'cp.entries.edit.meta';

    public function init()
    {
        parent::init();

        $this->registerElementBehavior();
        $this->registerQueryBehavior();

        if (Craft::$app->request->getIsCpRequest() && $this->settings->displayMetadataInCp) {
            $this->registerCpMetaHook();
        }
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    private function registerElementBehavior()
    {
        Event::on(ActiveQuery::class, ActiveQuery::EVENT_INIT, function ($e) {
            if ($e->sender->modelClass === EntryRecord::class) {
                $e->sender->attachBehaviors([EntryActiveQueryBehavior::class]);
            }
        });
    }

    private function registerQueryBehavior()
    {
        Event::on(ElementQuery::class, ElementQuery::EVENT_AFTER_POPULATE_ELEMENT, function (PopulateElementEvent $event) {
            $element = $event->element;

            if ($element instanceof Entry) {
                $element->attachBehavior('metadata', EntryElementBehavior::class);
            }
        });

    }

    private function registerCpMetaHook()
    {
        Craft::$app->view->registerTwigExtension(new EntryMetaExtension());

        Craft::$app->getView()->hook(self::CP_HOOK, function (array &$context) {
            $entry = $context['entry'];
            $meta = $entry->getEntryMetadata();

            return Craft::$app->view->renderTemplate('entry-meta/_meta', [
                'data' => $meta
            ]);
        });
    }
}