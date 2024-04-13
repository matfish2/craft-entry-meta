<?php

namespace matfish\EntryMeta\behaviors;

use craft\db\Query;
use matfish\EntryMeta\EntryMeta;
use matfish\EntryMeta\records\ElementMeta;
use yii\base\Behavior;
use yii\db\Exception;

class ElementBehavior extends Behavior
{
    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function setElementMetadata(array $data): void
    {
        $this->_saveMetadata($data);
    }

    public function deleteElementMetadata(): void
    {
        $record = $this->_getNewOrExistingRecord();
        $record->delete();
    }

    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function addElementMetadata(array $data): void
    {
        $current = $this->getElementMetadata();

        $this->_saveMetadata(array_merge($current, $data));
    }

    /**
     * @throws \JsonException
     */
    public function getElementMetadata($key = null)
    {
        $meta = $this->_getElementMetadata();
        // allow retrieval of nested data point by using a . separator, e.g foo.bar will return 'x' if ['foo'=> 'bar' => 'x']
        if ($key) {
            $keys = explode('.', $key);
            $value = $meta;
            foreach ($keys as $k) {
                $value = $value[$k] ?? null;
            }
            return $value;
        }

        // return all metadata if no key is provided
        return $meta;
    }

    /**
     * @throws \JsonException
     */
    private function _getElementMetadata(): array
    {
        $current = ElementMeta::find()->where([
            'elementId' => $this->owner->id
        ])->andWhere([
            'elementType' => get_class($this->owner)
        ])
            ->all();

        $res = $current ? $current[0]->data : null;

        return $res ? json_decode($res, true, 512, JSON_THROW_ON_ERROR) : [];
    }

    private function _getNewOrExistingRecord(): ElementMeta
    {
        $record = ElementMeta::find()->where([
            'elementId' => $this->owner->id
        ])->andWhere([
            'elementType' => get_class($this->owner)
        ])
            ->all();

        return $record ? $record[0] : new ElementMeta();
    }

    /**
     * @throws Exception
     * @throws \JsonException
     */
    private function _saveMetadata(array $data): void
    {
        $r = $this->_getNewOrExistingRecord();
        $r->elementId = $this->owner->id;
        $r->elementType = get_class($this->owner);
        $r->data = json_encode($data, JSON_THROW_ON_ERROR);;
        $r->save();
    }
}