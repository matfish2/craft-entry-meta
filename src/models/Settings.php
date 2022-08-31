<?php

namespace matfish\EntryMeta\models;

use craft\base\Element;
use craft\base\Model;
use craft\db\ActiveRecord;

class Settings extends Model
{
    public bool $displayMetadataInCp = true;
    public $enabledFor = [];
    public $enabledForCustom = [];

    public function rules() : array
    {
        return [
            [['displayMetadataInCp'], 'boolean'],
            ['enabledForCustom', function($attr, $params, $validator, $current) {
                foreach ($current as $val) {
                    if (!class_exists($val[0])) {
                        $this->addError($attr,"Element Class {$val[0]} does not exist");
                    } elseif (!is_subclass_of($val[0],Element::class)) {
                        $this->addError($attr,"Class {$val[0]} is not an Element Class");
                    }

                    if (!class_exists($val[1])) {
                        $this->addError($attr,"Active Record Class {$val[0]} does not exist");
                    } elseif (!is_subclass_of($val[1],ActiveRecord::class)) {
                        $this->addError($attr,"Class {$val[0]} is not an Active Record Class");
                    }
                }
            }]
        ];
    }
}