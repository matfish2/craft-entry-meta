<?php

namespace matfish\EntryMeta\migrations;

use Craft;
use craft\db\Migration;
use matfish\EntryMeta\services\OneOff\MoveDataToDesignatedTable;

/**
 * m230730_064458_create_element_meta_table migration.
 */
class m230730_064458_create_element_meta_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
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

        (new MoveDataToDesignatedTable())->run();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m230730_064458_create_element_meta_table cannot be reverted.\n";
        return false;
    }
}
