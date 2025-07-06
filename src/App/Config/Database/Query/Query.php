<?php

namespace App\Config\Database\Query;

class Query {

    protected array $data;

    public function getData(): array {
        return $this->data;
    }

    public function setTable(string $table): static {
        $this->data['table'] = $table;

        return $this;
    }

    public function setSelect(array|string $cols): static {
        $this->data['select'] = $cols;

        return $this;
    }

    public function setWhere(string $cond, array $binds): static {
        $this->data['where'][] = $cond;
        $this->data['binds'] = array_merge($this->data['binds'] ?? [], $binds);

        return $this;
    }

    public function setLimit(string $limit = '10', string $offSet = ''): static {
        if ($offSet)
            $this->data['limit'] = "{$limit}, {$offSet} ";
        else
            $this->data['limit'] = "{$limit} ";

        return $this;
    }

    public function setJoin(string $foreignTable, string $localKey, string $foreignKey, string $type = 'LEFT'): static {
        $mainTable = $this->data['table'];

        $foreignField = "{$foreignTable}.{$foreignKey}";
        $localField = "{$mainTable}.{$localKey}";

        $this->data['join'][] = strtoupper($type) . " JOIN {$foreignTable} ON {$foreignField} = {$localField}";

        return $this;
    }

    public function getParam(string|array $param): string|array {

        if (is_array($param)) {
            $dataParam = [];
            foreach ($param as $p) {
                $dataParam[$p] = $this->format($p);
            }
            return $dataParam;
        }

        return $this->format($param) ?: $this->data[$param];
    }

    public function getSentence() {
        $sql = [];

        $sql[] = $this->format('select');

        $sql[] = "FROM {$this->data['table']}";

        $join = $this->format('join');
        if ($join) $sql[] = $join;

        $where = $this->format('where');
        if ($where) $sql[] = $where;

        $limit = $this->format('limit');
        if ($limit) $sql[] = $limit;

        return implode(' ', $sql);
    }

    private function format(string $field): string {

        switch ($field) {
            case 'select':
                if (!isset($this->data[$field])) {
                    return "SELECT *";
                }

                if (is_array($this->data[$field])) {
                    return "SELECT " . implode(',', $this->data[$field]);
                }

                return "SELECT " . $this->data[$field];

            case 'where':
                if (!isset($this->data[$field])) return '';

                if (is_array($this->data[$field]))
                    return $this->data[$field] = " WHERE " . implode(' ', $this->data[$field]);

                return $this->data[$field];

                break;

            case 'join':
                if (!isset($this->data[$field])) return '';

                if (is_array($this->data[$field]))
                    return $this->data[$field] = implode(' ', $this->data[$field]);

                return $this->data[$field];

                break;

            case 'limit':
                if (!isset($this->data[$field])) return '';

                return "LIMIT " . $this->data[$field];

                break;

            default:
                return '';
        }
    }
}
