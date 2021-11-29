<?php

namespace matfish\EntryMeta\services;

use craft\records\Entry as EntryRecord;
use matfish\EntryMeta\EntryMeta;

class EntryMetaQueryService
{
    protected $dbDriver;

    const MYSQL = 'mysql';
    const POSTGRES = 'pgsql';

    public function __construct()
    {
        $this->dbDriver = \Craft::$app->db->driverName;
    }

    public function findEntriesByMetadata(array $params)
    {
        $q = EntryRecord::find();

        $first = true;

        foreach ($params as $key => $value) {
            $method = $first ? 'where' : 'andWhere';
            $q = $q->{$method}($this->_getCondition($key, $value));
            $first = false;
        }

        return $q;
    }

    private function _getCondition($key, $value)
    {
        $column = EntryMeta::COLUMN_NAME;

        $bool = is_bool($value);

        $Key = $this->_transformKey($key);
        $Value = $this->_transformValue($value);

        if ($this->dbDriver === self::MYSQL) {
            return "JSON_EXTRACT({$column},'$.{$Key}')={$Value}";
        } else if ($this->dbDriver === self::POSTGRES) {
            if ($bool) {
                return "({$column}{$Key})::boolean is {$Value}";
            }

            return "metadata{$Key}={$Value}";
        }

        throw new \Exception("Unsupported database driver {$this->dbDriver}");
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