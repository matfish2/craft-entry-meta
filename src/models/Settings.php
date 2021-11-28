<?php

namespace matfish\EntryMeta\models;

use craft\base\Model;

class Settings extends Model
{
    public $displayMetadataInCp = true;

    public function rules()
    {
        return [
            [['displayMetadataInCp'], 'bool']
        ];
    }
}