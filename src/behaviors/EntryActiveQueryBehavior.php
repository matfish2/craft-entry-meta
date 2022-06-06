<?php


namespace matfish\EntryMeta\behaviors;


use craft\helpers\Db;
use matfish\EntryMeta\EntryMeta;
use yii\base\Behavior;

class EntryActiveQueryBehavior extends Behavior
{
    protected $dbDriver;

    const MYSQL = 'mysql';
    const POSTGRES = 'pgsql';

    public function __construct()
    {
        parent::__construct();

        $this->dbDriver = \Craft::$app->db->driverName;
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

    private function _whereMetadata($key, $value, $operand, $method)
    {
        return $this->owner->{$method}($this->_getCondition($key, $value, $operand));
    }

    private function _getCondition($key, $value, $operand)
    {
        $bool = is_bool($value);
        $numeric = is_numeric($value);

        $keyExpression = $this->_getKeyExpression($key);
        $Value = $this->_transformValue($value);

        if ($this->dbDriver === self::MYSQL) {
            return "{$keyExpression}{$operand}{$Value}";
        } else if ($this->dbDriver === self::POSTGRES) {
            if ($bool) {
                return "{$keyExpression}::boolean is {$Value}";
            }

            if ($numeric) {
                $keyExpression = "{$keyExpression}::integer";
            }

            return "{$keyExpression}{$operand}{$Value}";
        }
    }

    private function _getKeyExpression($key, $cast = null)
    {
        $column = EntryMeta::COLUMN_NAME;
        $Key = $this->_transformKey($key);

        if ($this->dbDriver === self::MYSQL) {
            $exp = "JSON_EXTRACT({$column},'$.{$Key}')";
            if ($cast) {
                $exp = "CAST({$exp} as {$cast})";
            }
        } else if ($this->dbDriver === self::POSTGRES) {
            $exp = "({$column}{$Key})";
            if ($cast) {
                $exp.= "::{$cast}";
            }
        } else {
            throw new \Exception("Unsupported database driver {$this->dbDriver}");
        }

        return $exp;
    }

    private function _transformValue($value)
    {
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