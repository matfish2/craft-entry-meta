<?php

namespace matfish\EntryMeta\behaviors;

use Craft;
use craft\db\Query;
use matfish\EntryMeta\EntryMeta;
use yii\base\Behavior;

class ElementBehavior extends Behavior
{
    protected string $table;

    /**
     * ElementBehavior constructor.
     * @param string $table
     */
    public function __construct(string $table)
    {
        parent::__construct();
        $this->table = $table;
    }

    public function setElementMetadata(array $data)
    {
        $this->_saveMetadata($data);
    }

    public function addElementMetadata(array $data)
    {
        $current = $this->getElementMetadata();

        $this->_saveMetadata(array_merge($current, $data));
    }

    public function getElementMetadata($key = null)
    {
        $meta = $this->_getElementMetadata();

        return $key ? ($meta[$key] ?? null) : $meta;
    }

    private function _getElementMetadata()
    {
        $current = (new Query)->select([EntryMeta::COLUMN_NAME])->from($this->table)->where(['id' => $this->owner->id])->all();
        $res = $current[0][EntryMeta::COLUMN_NAME];

        return $res ? json_decode($res, true, 512, JSON_THROW_ON_ERROR) : [];
    }

    private function _saveMetadata(array $data): void
    {
        $meta = json_encode($data);
        Craft::$app->db->createCommand("UPDATE {$this->table} set " . EntryMeta::COLUMN_NAME . "='{$meta}' WHERE [[id]]={$this->owner->id}")->execute();
    }
}