<?php

namespace matfish\EntryMeta;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\controllers\ElementsController;
use craft\db\ActiveQuery;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\DefineElementEditorHtmlEvent;
use craft\events\DefineHtmlEvent;
use craft\events\PopulateElementEvent;
use matfish\EntryMeta\behaviors\EntryActiveQueryBehavior;
use matfish\EntryMeta\behaviors\EntryElementBehavior;
use matfish\EntryMeta\models\Settings;
use matfish\EntryMeta\twig\EntryMetaExtension;
use yii\base\Event;
use craft\records\Entry as EntryRecord;
use craft\base\Model;

class EntryMeta extends Plugin
{
    const COLUMN_NAME = 'metadata';

    public function init()
    {
        parent::init();

        $this->registerElementBehavior();
        $this->registerQueryBehavior();

        if ($this->settings->displayMetadataInCp && Craft::$app->request->getIsCpRequest()) {
            $this->registerCpMetaHook();
        }
    }

    protected function createSettingsModel(): ?Model
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

    private function registerCpMetaHook(): void
    {
        Craft::$app->view->registerTwigExtension(new EntryMetaExtension());

        Event::on(
            Entry::class,
            Element::EVENT_DEFINE_SIDEBAR_HTML,
            function (DefineHtmlEvent $event) {
                $entry = $event->sender;
                $meta = $entry->getEntryMetadata();
                $template = Craft::$app->view->renderTemplate('entry-meta/_meta', [
                    'data' => $meta
                ]);
                $event->html .= $template;
            }
        );
    }
}