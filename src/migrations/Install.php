<?php

namespace matfish\EntryMeta\migrations;

use Craft;
use craft\db\Migration;
use matfish\EntryMeta\EntryMeta;

class Install extends Migration
{
    public function safeUp()
    {
        if (!$this->_pluginExistsInProjectConfig() && !$this->_columnExists()) {
            echo "> Adding column " . EntryMeta::COLUMN_NAME . " to entries table..." . PHP_EOL;
            $columnType = Craft::$app->db->driverName==='pgsql' ? 'jsonb' : 'text';
            Craft::$app->db->createCommand()->addColumn("{{%entries}}", EntryMeta::COLUMN_NAME, $columnType)->execute();
        }
    }

    public function safeDown()
    {
        if ($this->_pluginExistsInProjectConfig() && $this->_columnExists()) {
            echo "> Removing column " . EntryMeta::COLUMN_NAME . " from entries table..." . PHP_EOL;
            Craft::$app->db->createCommand()->dropColumn("{{%entries}}", EntryMeta::COLUMN_NAME)->execute();
        }
    }

    private function _pluginExistsInProjectConfig(): bool
    {
        return Craft::$app->projectConfig->get('plugins.entry-meta', true) !== null;
    }

    private function _columnExists(): bool
    {
        return Craft::$app->db->columnExists("{{%entries}}", EntryMeta::COLUMN_NAME);
    }
}