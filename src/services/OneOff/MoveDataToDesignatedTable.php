<?php

namespace matfish\EntryMeta\services\OneOff;

use Craft;
use matfish\EntryMeta\EntryMeta;
use matfish\EntryMeta\records\ElementMeta;

class MoveDataToDesignatedTable
{
    public function run()
    {
        $enabled = EntryMeta::getInstance()->getAllEnabled();

        foreach ($enabled as $type) {
            [$activeRecordClass, $elementClass] = $type;
            $tableName = $activeRecordClass::getTableSchema()->name;

            if (Craft::$app->db->columnExists($tableName, EntryMeta::COLUMN_NAME)) {
                $records = $activeRecordClass::find()->where(EntryMeta::COLUMN_NAME . ' IS NOT NULL')->all();

                foreach ($records as $record) {
                    if (!$this->exists($record->id, $elementClass)) {
                        $r = new ElementMeta();
                        $r->elementId = $record->id;
                        $r->elementType = $elementClass;
                        $r->data = $record->emMetadata;
                        $r->save();
                    }
                }
            }
        }
    }

    public function exists($id, $class): bool
    {
        return ElementMeta::find()->where([
            'elementId' => $id
        ])->andWhere([
            'elementType' => $class
        ])
            ->count() > 0;
    }
}