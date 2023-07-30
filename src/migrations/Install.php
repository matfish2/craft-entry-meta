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
        $this->createTable('{{%elementmeta}}', [
            'id' => $this->primaryKey()->notNull(),
            'elementType' => $this->string()->notNull(),
            'elementId' => $this->integer()->notNull(),
            'data' => $this->text()->notNull(),
            'dateCreated' => $this->timestamp(),
            'dateUpdated' => $this->timestamp()
        ]);

        $this->createIndex('unique_element', '{{%elementmeta}}', ['elementType', 'elementId'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%elementmeta}}');
    }
}