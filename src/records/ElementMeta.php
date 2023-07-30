<?php

namespace matfish\EntryMeta\records;

use yii\db\ActiveRecord;

class ElementMeta extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%elementmeta}}';
    }
}