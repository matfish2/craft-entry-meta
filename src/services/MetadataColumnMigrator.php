<?php

namespace matfish\EntryMeta\services;

use Craft;
use matfish\EntryMeta\EntryMeta;

class MetadataColumnMigrator
{

    public function add($table): bool
    {
        if (!$this->columnExists($table)) {
            echo "> Adding column " . EntryMeta::COLUMN_NAME . " to $table table..." . PHP_EOL;
            $columnType = Craft::$app->db->driverName === 'pgsql' ? 'jsonb' : 'text';
            Craft::$app->db->createCommand()->addColumn($table, EntryMeta::COLUMN_NAME, $columnType)->execute();
        }

        return true;
    }


    public function drop($table): bool
    {
        if ($this->columnExists($table)) {
            echo "> Removing column " . EntryMeta::COLUMN_NAME . " from $table table..." . PHP_EOL;
            Craft::$app->db->createCommand()->dropColumn($table, EntryMeta::COLUMN_NAME)->execute();
        }

        return true;
    }

    public function columnExists($table): bool
    {
        return Craft::$app->db->columnExists($table, EntryMeta::COLUMN_NAME);
    }
}