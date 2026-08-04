<?php

namespace zap\db;

use ArrayAccess;
use JsonSerializable;
use zap\util\Arr;

abstract class Model implements ArrayAccess, JsonSerializable
{
    /**
     * The table associated with the model.
     */
    protected $table;

    /**
     * Primary key column name.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should auto-manage timestamps.
     */
    protected $timestamps = false;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'updated_at';

    /**
     * The model's attributes.
     */
    protected $attributes = [];

    /**
     * The model attribute's original state.
     */
    protected $original = [];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [];

    /**
     * The attributes that are mass assignable.
     * Empty means all non-guarded attributes are fillable.
     */
    protected $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['*'];

    /**
     * Indicates if the model exists in the database.
     */
    public $exists = false;

    /**
     * Whether the model is currently being saved.
     */
    protected $saving = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    // ─── Static Access ─────────────────────────────────────────

    /**
     * Get a model instance.
     */
    public static function getInstance()
    {
        return new static();
    }

    /**
     * Get the database table name.
     */
    public static function getTableName(): string
    {
        $obj = new static();
        return $obj->table ?? $obj->getDefaultTable();
    }

    /**
     * Get database connection.
     */
    public static function getDB(): ?ZPDO
    {
        if (class_exists('zap\DB') && method_exists('zap\DB', 'connection')) {
            return \zap\DB::connection();
        }
        return app()->db ?? null;
    }

    /**
     * Create a new query builder instance.
     */
    protected static function createQuery(array $conditions = []): Query
    {
        return Query::query(static::getDB(), static::getTableName());
    }

    // ─── Find / Get ────────────────────────────────────────────

    /**
     * Find a model by its primary key.
     */
    public static function findById($id, $idName = null)
    {
        $primaryKey = $idName ?? (new static())->primaryKey;
        $query = static::createQuery()
            ->where($primaryKey, '=', $id)
            ->limit(1);

        $row = $query->get();
        if (empty($row)) {
            return null;
        }

        return new static((array) $row[0]);
    }

    /**
     * Find a model by its primary key or throw an exception.
     */
    public static function findOrFail($id, $idName = null)
    {
        $model = static::findById($id, $idName);
        if ($model === null) {
            throw new \RuntimeException('Model not found: ' . static::class . ' with id=' . $id);
        }
        return $model;
    }

    /**
     * Find a model by ID or return a new instance.
     */
    public static function findOrNew($id, $idName = null)
    {
        $model = static::findById($id, $idName);
        if ($model !== null) {
            return $model;
        }
        $instance = new static();
        $primaryKey = $idName ?? $instance->primaryKey;
        $instance->setAttribute($primaryKey, $id);
        return $instance;
    }

    /**
     * Find all records.
     */
    public static function findAll(): array
    {
        $query  = static::createQuery();
        $result = $query->get();

        $models = [];
        foreach ($result as $row) {
            $models[] = new static((array) $row);
        }
        return $models;
    }

    /**
     * Get all records.
     */
    public static function all(): array
    {
        return static::findAll();
    }

    /**
     * Get the first record.
     */
    public static function first()
    {
        $query = static::createQuery()->limit(1);
        $row   = $query->get();
        if (empty($row)) {
            return null;
        }
        return new static((array) $row[0]);
    }

    /**
     * Count records by conditions.
     */
    public static function countBy(array $conditions = []): int
    {
        $query = static::createQuery();
        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }
        return $query->count();
    }

    /**
     * Check if any records exist matching conditions.
     */
    public static function exists(array $conditions = []): bool
    {
        return static::countBy($conditions) > 0;
    }

    /**
     * Delete records by primary key.
     */
    public static function destroy($id): bool
    {
        $model = new static();
        $query = static::createQuery()
            ->where($model->primaryKey, '=', $id);

        return $query->delete() > 0;
    }

    /**
     * Legacy spelling - keep for backward compatibility.
     * @deprecated Use destroy() instead.
     */
    public static function destory($id): bool
    {
        return static::destroy($id);
    }

    // ─── Attribute Access ──────────────────────────────────────

    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    public function __isset($name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function getAttribute(string $name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->castGet($name, $this->attributes[$name]);
        }
        return null;
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function setAttribute(string $name, $value): void
    {
        $this->attributes[$name] = $this->castSet($name, $value);
    }

    public function getAttributes(): array
    {
        $attrs = [];
        foreach ($this->attributes as $key => $value) {
            $attrs[$key] = $this->castGet($key, $value);
        }
        return $attrs;
    }

    public function setAttributes(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getId()
    {
        return $this->getAttribute($this->primaryKey);
    }

    // ─── ArrayAccess / JsonSerializable ────────────────────────

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return $this->getAttributes();
    }

    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    // ─── Mass Assignment ───────────────────────────────────────

    /**
     * Fill the model attributes, respecting fillable/guarded rules.
     */
    public function fill(array $attributes)
    {
        $filtered = $this->filterFillable($attributes);
        foreach ($filtered as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Filter attributes based on fillable/guarded.
     */
    protected function filterFillable(array $attributes): array
    {
        // If we're saving (not initial fill), skip mass assignment protection
        if ($this->saving) {
            return $attributes;
        }

        // If fillable is specified, only allow those
        if (!empty($this->fillable)) {
            return array_intersect_key($attributes, array_flip($this->fillable));
        }

        // If guarded is specified, exclude those
        if ($this->guarded === ['*']) {
            return [];
        }

        if (!empty($this->guarded)) {
            return array_diff_key($attributes, array_flip($this->guarded));
        }

        return $attributes;
    }

    // ─── Casts ─────────────────────────────────────────────────

    protected function castGet(string $key, $value)
    {
        if (!isset($this->casts[$key])) {
            return $value;
        }

        switch ($this->casts[$key]) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
            case 'real':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return is_string($value) ? json_decode($value, true) : (array) $value;
            case 'object':
                return is_string($value) ? json_decode($value) : (object) $value;
            default:
                return $value;
        }
    }

    protected function castSet(string $key, $value)
    {
        if (!isset($this->casts[$key])) {
            return $value;
        }

        switch ($this->casts[$key]) {
            case 'array':
            case 'json':
                return is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
            case 'object':
                return is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
            default:
                return $value;
        }
    }

    // ─── Save / Refresh ────────────────────────────────────────

    /**
     * Save the model to the database.
     */
    public function save(): bool
    {
        $this->saving = true;

        $db = static::getDB();
        $id = $this->getId();

        // Timestamps
        $now = date('Y-m-d H:i:s');
        if ($this->timestamps) {
            if (!$this->exists || !$id) {
                $this->setAttribute(static::CREATED_AT, $now);
            }
            $this->setAttribute(static::UPDATED_AT, $now);
        }

        if ($id && $this->exists) {
            // Update
            if (!empty($this->attributes)) {
                $result = $db->update(
                    static::getTableName(),
                    $this->attributes,
                    "{$this->primaryKey}=:{$this->primaryKey}",
                    [$this->primaryKey => $id]
                );
                $this->saving = false;
                return $result > 0;
            }
        } else {
            // Insert
            $data = $this->attributes;
            if ($id && !$this->exists) {
                $data[$this->primaryKey] = $id;
            }

            $newId = $db->insert(static::getTableName(), $data);
            if ($newId) {
                $this->setAttribute($this->primaryKey, $newId);
                $this->exists = true;
                $this->saving = false;
                return true;
            }
        }

        $this->saving = false;
        return false;
    }

    /**
     * Save the model and return it.
     */
    public function saveAndReturn()
    {
        $this->save();
        return $this;
    }

    /**
     * Reload a fresh model instance from the database.
     */
    public function fresh()
    {
        $id = $this->getId();
        if (!$id) {
            return null;
        }
        return static::findById($id, $this->primaryKey);
    }

    /**
     * Reload the current model attributes from the database.
     */
    public function refresh()
    {
        $fresh = $this->fresh();
        if ($fresh) {
            $this->attributes = $fresh->attributes;
            $this->original   = $fresh->original;
            $this->exists     = true;
        }
        return $this;
    }

    /**
     * Delete the model from the database.
     */
    public function delete(): bool
    {
        $id = $this->getId();
        if (!$id) {
            return false;
        }

        $db = static::getDB();
        $result = $db->delete(
            static::getTableName(),
            "{$this->primaryKey}=:{$this->primaryKey}",
            [$this->primaryKey => $id]
        );

        if ($result > 0) {
            $this->exists = false;
            return true;
        }

        return false;
    }

    // ─── Magic Methods ─────────────────────────────────────────

    public function __call($method, $parameters)
    {
        $query = static::createQuery()->where($this->primaryKey, '=', $this->getId());
        return $query->$method(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        $query = static::createQuery();

        // Dynamic where methods: findByXxx / findByXxxAndYyy
        if (str_starts_with(strtolower($method), 'findby')) {
            $colName = str_ireplace('findBy', '', $method);
            // Convert "UserName" → "User_Name" → "_user_name" → "user_name"
            $colName = preg_replace('/([A-Z])/', '_$1', $colName);
            $colName = strtolower(trim($colName, '_'));

            if (str_contains($colName, '_and_')) {
                $cols  = explode('_and_', $colName);
                $count = count($cols);
                foreach ($cols as $i => $c) {
                    if (isset($parameters[$i])) {
                        $query->where($c, '=', $parameters[$i]);
                    }
                }
                $query->limit(1);
                $row = $query->get();
                if (!empty($row)) {
                    return new static((array) $row[0]);
                }
                return null;
            }

            return $query->where($colName, '=', $parameters[0] ?? null)->get();
        }

        // Handle find() with array conditions (e.g. find(['node_id' => 5]))
        // Return Query with where applied, not executed — caller chains
        // select/orderBy/fetchColumn or first/get themselves
        if ($method === 'find' && !empty($parameters) && is_array($parameters[0])) {
            return $query->where($parameters[0]);
        }

        // Handle delete() with array conditions instead of a single id value
        if ($method === 'delete' && !empty($parameters) && is_array($parameters[0])) {
            return $query->where($parameters[0])->delete();
        }

        return $query->$method(...$parameters);
    }

    // ─── Helpers ───────────────────────────────────────────────

    protected function getDefaultTable(): string
    {
        // Convert UserProfile → user_profile
        $class  = $this->getClassName();
        $table  = preg_replace('/([a-z])([A-Z])/', '$1_$2', $class);
        return strtolower($table);
    }

    protected function getClassName(): string
    {
        $parts = explode('\\', static::class);
        return end($parts);
    }
}
