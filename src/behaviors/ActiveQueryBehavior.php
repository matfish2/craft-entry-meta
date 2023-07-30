<?php


namespace matfish\EntryMeta\behaviors;


use matfish\EntryMeta\EntryMeta;
use yii\base\Behavior;
use yii\base\Exception;

class ActiveQueryBehavior extends Behavior
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

    public function whereMetadata($key, $value, $operand = '=')
    {
        return $this->_whereMetadata($key, $value, $operand, 'where');
    }

    public function orWhereMetadata($key, $value, $operand = '=')
    {
        return $this->_whereMetadata($key, $value, $operand, 'orWhere');
    }

    public function andWhereMetadata($key, $value, $operand = '=')
    {
        return $this->_whereMetadata($key, $value, $operand, 'andWhere');
    }

    public function orderByMetadata($key, $asc = true, $numeric = false)
    {
        $cast = $numeric ? 'integer' : null;
        $dir = $asc ? SORT_ASC : SORT_DESC;
        $keyExpression = $this->_getKeyExpression($key, $cast);

        return $this->owner->orderBy([$keyExpression => $dir]);
    }

    public function hasMetadata($key = null)
    {
        return $this->_whereMetadata($key, null, ' IS NOT ', 'where');
    }

    public function doesntHaveMetadata($key = null)
    {
        return $this->_whereMetadata($key, null, ' IS ', 'where');
    }

    private function _whereMetadata($key, $value, $operand, $method)
    {
        return $this->owner->{$method}($this->_getCondition($key, $value, $operand));
    }

    private function _getCondition($key, $value, $operand): string
    {
        $bool = is_bool($value);
        $numeric = is_numeric($value);

        $keyExpression = $this->_getKeyExpression($key);
        $Value = $this->_transformValue($value);

        if ($this->dbDriver === self::MYSQL) {
            return "{$keyExpression}{$operand}{$Value}";
        }

        if ($this->dbDriver === self::POSTGRES) {
            if ($bool) {
                return "{$keyExpression}::boolean is {$Value}";
            }

            if ($numeric) {
                $keyExpression = "{$keyExpression}::integer";
            }

            return "{$keyExpression}{$operand}{$Value}";
        }
    }

    private function _getKeyExpression($key, $cast = null): string
    {
        $column = '{{%elementmeta}}.[[data]]';

        if (is_null($key)) {
            return $column;
        }

        $Key = $this->_transformKey($key);

        if ($this->dbDriver === self::MYSQL) {
            $exp = "JSON_EXTRACT({$column},'$.{$Key}')";
            if ($cast) {
                $exp = "CAST({$exp} as {$cast})";
            }
        } else if ($this->dbDriver === self::POSTGRES) {
            $exp = "({$column}{$Key})";
            if ($cast) {
                $exp .= "::{$cast}";
            }
        } else {
            throw new \Exception("Unsupported database driver {$this->dbDriver}");
        }

        return $exp;
    }

    private function _transformValue($value)
    {
        if (is_null($value)) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_string($value) || $this->dbDriver === self::POSTGRES) {
            return "'{$value}'";
        }

        return $value;
    }

    private function _transformKey($key)
    {
        if ($this->dbDriver === self::POSTGRES) {
            $keys = explode('.', $key);
            $keys = array_map(function ($key) {
                return "'{$key}'";
            }, $keys);
            $nested = count($keys) > 1;

            if ($nested) {
                $lastSegment = array_pop($keys);
                $key = implode('->', $keys);
                $key .= '->>' . $lastSegment;
                $key = '->' . $key;
            } else {
                $key = $keys[0];
                $key = '->>' . $key;
            }
        }

        return $key;
    }
}