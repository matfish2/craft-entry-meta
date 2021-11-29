<?php

namespace matfish\EntryMeta\behaviors;

use Craft;
use craft\db\Query;
use yii\base\Behavior;

class EntryElementBehavior extends Behavior
{
    public function setEntryMetadata(array $data)
    {
        $this->_saveMetadata($data);
    }

    public function addEntryMetadata(array $data)
    {
        $current = $this->getEntryMetadata();

        $this->_saveMetadata(array_merge($current, $data));
    }

    public function getEntryMetadata($key = null)
    {
        $meta =  $this->_getEntryMetadata();

        return $key ? ($meta[$key] ?? null) : $meta;
    }

    private function _getEntryMetadata()
    {
        $current = (new Query)->select(['metadata'])->from('{{%entries}}')->where(['id' => $this->owner->id])->all();
        $res = $current[0]['metadata'];

        return $res ? json_decode($res, true) : [];
    }

    private function _saveMetadata(array $data)
    {
        $meta = json_encode($data);
        Craft::$app->db->createCommand("UPDATE {{%entries}} set metadata='{$meta}' WHERE [[id]]={$this->owner->id}")->execute();
    }
}