<?php

namespace matfish\EntryMeta\behaviors;

use craft\elements\db\ElementQuery;
use matfish\EntryMeta\EntryMeta;

class ElementQueryBehavior extends QueryBehavior
{
    public function joinMetadata()
    {
        /** @var ElementQuery $owner */
        $owner = $this->owner;
        $elementType = $owner->elementType;
        $table = EntryMeta::getInstance()->getActiveRecordFromElementClass($elementType)::tableName();
        
        return $owner->join('LEFT JOIN', '{{%elementmeta}}', "{{%elementmeta}}.[[elementId]]=$table.[[id]]")
            ->andWhere(['or', ['elementmeta.elementType' => $elementType], ['elementmeta.elementType' => null]]);
    }
}
