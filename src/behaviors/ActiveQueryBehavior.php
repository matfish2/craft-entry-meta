<?php


namespace matfish\EntryMeta\behaviors;


use matfish\EntryMeta\EntryMeta;
use matfish\EntryMeta\behaviors\QueryBehavior;
use yii\base\Exception;

class ActiveQueryBehavior extends QueryBehavior
{
    protected $dbDriver;

    const MYSQL = 'mysql';
    const POSTGRES = 'pgsql';

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->dbDriver = \Craft::$app->db->driverName;
    }

    public function joinMetadata()
    {
        $table = $this->owner->getAlias();
        $activeRecordClass = $this->owner->modelClass;
        $lookup = EntryMeta::getInstance()->getAllEnabled();
        $current = array_values(array_filter($lookup, static function ($row) use ($activeRecordClass) {
            return $row[0] === $activeRecordClass;
        }));

        if (count($current) === 0) {
            throw new Exception("Could not find element class for active record class " . $activeRecordClass);
        }


        $elementClass = str_replace('\\','\\\\',$current[0][1]);

        return $this->owner->join('LEFT JOIN', '{{%elementmeta}}', "{{%elementmeta}}.[[elementId]]=$table.[[id]] AND {{%elementmeta}}.[[elementType]]='$elementClass'");
    }
}