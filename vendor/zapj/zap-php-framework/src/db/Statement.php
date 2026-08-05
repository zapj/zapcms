<?php

namespace zap\db;

use PDO;
use PDOStatement;

class Statement extends PDOStatement
{
    /**
     * Bind a parameter to an IN clause placeholder.
     *
     * @param string $placeholder Placeholder name
     * @param array $values Array of values
     * @return int
     */
    public function bindIn(string $placeholder, array $values): int
    {
        $params = [];
        for ($i = 0; $i < count($values); $i++) {
            $params[$i] = ":{$placeholder}{$i}";
            $this->bindValue($params[$i], $values[$i]);
        }

        return 0;
    }

    /**
     * Execute with IN parameter binding.
     *
     * @param string $sql SQL with named placeholders for IN
     * @param array $inputParams Parameters including array values for IN
     */
    public function executeWithIn(string $sql, array $inputParams = []): bool
    {
        $params = [];

        foreach ($inputParams as $name => $value) {
            if (is_array($value)) {
                $inParams = [];
                for ($i = 0; $i < count($value); $i++) {
                    $key = ":{$name}{$i}";
                    $inParams[] = $key;
                    $params[$key] = $value[$i];
                }
                $sql = str_replace(':' . $name, implode(',', $inParams), $sql);
            } else {
                $params[':' . $name] = $value;
            }
        }

        foreach ($params as $key => $val) {
            $this->bindValue($key, $val);
        }

        return true;
    }

    /**
     * Fetch all rows as an associative array.
     */
    public function fetchAllAssoc(): array
    {
        return $this->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all rows as an array of objects.
     */
    public function fetchAllObj(): array
    {
        return $this->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all rows as a numbered array.
     */
    public function fetchAllNum(): array
    {
        return $this->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Fetch all values of a single column.
     */
    public function fetchColumnAll(int $column = 0): array
    {
        return $this->fetchAll(PDO::FETCH_COLUMN, $column);
    }

    /**
     * Fetch key-value pairs.
     */
    public function fetchPairs(): array
    {
        return $this->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Fetch grouped results.
     */
    public function fetchGrouped(int $style = PDO::FETCH_OBJ): array
    {
        return $this->fetchAll(PDO::FETCH_GROUP | $style);
    }

    /**
     * Get column meta information.
     */
    #[\ReturnTypeWillChange]
    public function getColumnMeta($column)
    {
        return parent::getColumnMeta($column);
    }
}
