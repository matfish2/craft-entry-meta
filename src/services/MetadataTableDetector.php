<?php

namespace matfish\EntryMeta\services;

use yii\base\UnknownClassException;

class MetadataTableDetector
{

    public function detect($key): string
    {
        if (array_key_exists($key, ClassesMap::LOOK_UP)) {
            $activeRecordClass = ClassesMap::LOOK_UP[$key][0];
        } else {
            $activeRecordClass = $key;
        }

        if (!class_exists($activeRecordClass)) {
            throw new UnknownClassException("Class {$activeRecordClass} does not exist");
        }

        return $activeRecordClass::getTableSchema()->name;
    }

}