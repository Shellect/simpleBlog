<?php

namespace engine;

use ArrayAccess;

abstract class Model implements ArrayAccess
{
    protected $connection;
    protected string $table;
    private array $dataResult = [];

    public function __construct($select = false)
    {
        $this->connection = App::$container->get(DB::class)->connect();

        $modelName = get_class($this);
        $arrExp = explode('\\', $modelName);
        $tableName = strtolower(end($arrExp));
        $this->table = $tableName;

        $sql = $this->_getSelect($select) ?: '';
        $this->_getResult("SELECT * FROM {$this->table}{$sql};");
    }

    public function validate($value): string
    {
        $value = trim($value);
        $value = stripslashes($value);
        $value = strip_tags($value);
        return htmlspecialchars($value);
    }

    public function getTableName(): string
    {
        return $this->table;
    }

    public function getAllRows(): bool|array
    {
        if (empty($this->dataResult)) {
            return false;
        }

        return $this->dataResult;
    }

    public function getOneRow()
    {
        if (empty($this->dataResult)) {
            return false;
        }

        return $this->dataResult[0];
    }

    public function fetchOne(): bool
    {
        if (empty($this->dataResult)) {
            return false;
        }
        foreach ($this->dataResult[0] as $key => $val) {
            $this->$key = $val;
        }

        return true;
    }

    public function getRowById($id)
    {
        $db = $this->connection;
        return $db->query("SELECT * from {$this->table} WHERE id = {$id}")->fetch();
    }

    public function save()
    {
        $arrayAllFields = array_keys($this->fieldsTable());
        $arraySetFields = array();
        $arrayData = array();
        foreach ($arrayAllFields as $field) {
            if (!empty($this->$field)) {
                $arraySetFields[] = $field;
                $arrayData[] = $this->$field;
            }
        }
        $forQueryFields = implode(', ', $arraySetFields);
        $rangePlace = array_fill(0, count($arraySetFields), '?');
        $forQueryPlace = implode(', ', $rangePlace);

        try {
            $db = $this->connection;
            $stmt = $db->prepare("INSERT INTO $this->table ($forQueryFields) values ($forQueryPlace)");
            $result = $stmt->execute($arrayData);
        } catch (\PDOException $e) {
            echo 'Error : ' . $e->getMessage();
            echo '<br/>Error sql : ' . "'INSERT INTO $this->table ($forQueryFields) values ($forQueryPlace)'";
            exit();
        }

        return $result;
    }

    private function _getSelect($select): string
    {
        $querySql = '';
        if (is_array($select)) {
            $allQuery = array_keys($select);
            array_walk($allQuery, function (&$val) {
                $val = strtoupper($val);
            });
            if (in_array('WHERE', $allQuery, true)) {
                foreach ($select as $key => $val) {
                    if (strtoupper($key) === 'WHERE') {
                        $querySql .= ' WHERE ' . $val;
                    }
                }
            }
            if (in_array('GROUP', $allQuery, true)) {
                foreach ($select as $key => $val) {
                    if (strtoupper($key) === 'GROUP') {
                        $querySql .= ' GROUP BY ' . $val;
                    }
                }
            }
            if (in_array('ORDER', $allQuery, true)) {
                foreach ($select as $key => $val) {
                    if (strtoupper($key) === 'ORDER') {
                        $querySql .= ' ORDER BY ' . $val;
                    }
                }
            }

            if (in_array('LIMIT', $allQuery, true)) {
                foreach ($select as $key => $val) {
                    if (strtoupper($key) === 'LIMIT') {
                        $querySql .= ' LIMIT ' . $val;
                    }
                }
            }
            if (in_array('OFFSET', $allQuery, true)) {
                foreach ($select as $key => $val) {
                    if (strtoupper($key) === 'OFFSET') {
                        $querySql .= ' OFFSET ' . $val;
                    }
                }
            }
        }
        return $querySql;
    }

    private function _getResult(string $sql): array
    {
        try {
            $db = $this->connection;
            $stmt = $db->query($sql);
            $rows = $stmt->fetchAll();
            $this->dataResult = $rows;
        } catch (\PDOException $e) {
            echo 'Error : ' . $e->getMessage();
            echo '<br/>Error sql : ' . $sql;
            exit();
        }

        return $rows;
    }

    public function update($arrayAllFields = []): bool
    {
        $arrayForSet = array();
        foreach ($arrayAllFields as $field) {
            if ($this->$field !== null) {
                if (strtoupper($field) === 'ID') {
                    $whereID = $this->$field;
                } else {
                    $arrayForSet[] = "{$field}='{$this->$field}'";
                }
            }
        }
        if (empty($arrayForSet)) {
            echo "Array data table `$this->table` empty!";
            exit;
        }
        if (empty($whereID)) {
            echo "ID table `$this->table` not found!";
            exit;
        }

        $strForSet = implode(', ', $arrayForSet);

        try {
            $db = $this->connection;
            $stmt = $db->prepare("UPDATE ($this->table) SET {$strForSet} WHERE `id` = :whereID;");
            $stmt->bindParam(':whereID', $whereID);
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            echo 'Error : ' . $e->getMessage();
            echo '<br/>Error sql : ' . "'UPDATE {$this->table} SET {$strForSet} WHERE `id` = {$whereID}'";
            exit();
        }

        return $result;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    abstract public function fieldsTable();
}