<?php

namespace matfish\EntryMeta\migrations;

use Craft;
use craft\db\Migration;
use matfish\EntryMeta\EntryMeta;
use matfish\EntryMeta\services\MetadataTableDetector;

class Install extends Migration
{
    public function safeUp()
    {
    }

    public function safeDown()
    {
        $activeRecordClasses = EntryMeta::getInstance()->getEnabledActiveRecords();

        foreach ($activeRecordClasses as $ar) {
            $table = (new MetadataTableDetector())->detect($ar);

            if ($this->_columnExists($table)) {
                echo "> Removing column " . EntryMeta::COLUMN_NAME . " from entries table..." . PHP_EOL;
                Craft::$app->db->createCommand()->dropColumn($table, EntryMeta::COLUMN_NAME)->execute();
            }
        }

    }

    private function _columnExists($table): bool
    {
        return Craft::$app->db->columnExists($table, EntryMeta::COLUMN_NAME);
    }
}