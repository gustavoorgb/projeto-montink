<?php

namespace App\Models;

use App\Config\Database\Connection;
use App\Config\Database\Query\Query;
use App\Helpers\Strings;
use PDO;

class ActiveRecord {

    protected array $data = [];
    protected string $table;
    protected string $primaryKey = 'id';
    private Query $query;
    private ?PDO $Db;

    public function __construct() {
        $this->Db = Connection::getConnection();
        $this->query = new Query();
    }

    public function __set($name, $value) {
        $setName = Strings::toCamelCase("set_$name");
        if (method_exists($this, $setName)) {
            return $this->$setName($value);
        } else {
            return $this->data[$name] = $value;
        }
    }

    public function __get($name) {
        $getName = Strings::toCamelCase("get_$name");
        if (method_exists($this, $getName)) {
            return $this->$getName();
        } else {
            return $this->data[$name] ?? null;
        }
    }

    public function find(int $id): ?static {
        $query = $this->query->setTable($this->table)
            ->setWhere("{$this->primaryKey} = :id", [':id' => $id]);

        $sql = $query->getSentence();
        $stmt = $this->Db->prepare($sql);
        $stmt->execute($this->query->getData()['binds']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        foreach ($row as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public function save() {
        $columns = array_keys($this->data);
        $placeHolders = array_map(fn($col) => ":$col", $columns);
        $params = [];
        foreach ($columns as $col) {
            $params[":$col"] = $this->data[$col];
        }
        if (isset($this->data[$this->primaryKey])) {
            $set = implode(', ', array_map(fn($col) => "$col = :$col", $columns));
            $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :{$this->primaryKey}";
            $params[":{$this->primaryKey}"] = $this->data[$this->primaryKey];
        } else {
            $colStr = implode(',', $columns);
            $placeholderStr = implode(',', $placeHolders);
            $sql = "INSERT INTO {$this->table} ({$colStr}) VALUES ({$placeholderStr})";
        }
        $stmt = $this->Db->prepare($sql);
        $ok = $stmt->execute($params);
        if (!isset($this->data[$this->primaryKey])) {
            $this->data[$this->primaryKey] = $this->Db->lastInsertId();
        }
        return $ok;
    }
}
