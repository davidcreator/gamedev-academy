<?php
// core/Model.php

namespace Core;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $hidden = ['password'];
    
    protected array $attributes = [];
    protected array $original = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    public static function getTable(): string
    {
        return static::$table;
    }

    protected static function db(): Database
    {
        return Database::getInstance();
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (empty(static::$fillable) || in_array($key, static::$fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function toArray(): array
    {
        $data = $this->attributes;
        
        foreach (static::$hidden as $key) {
            unset($data[$key]);
        }
        
        return $data;
    }

    // Query Methods
    public static function find(int $id): ?static
    {
        $data = self::db()->fetch(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id",
            ['id' => $id]
        );
        
        return $data ? new static($data) : null;
    }

    public static function findOrFail(int $id): static
    {
        $model = self::find($id);
        
        if (!$model) {
            throw new \RuntimeException("Registro não encontrado");
        }
        
        return $model;
    }

    public static function all(array $columns = ['*']): array
    {
        $columns = implode(', ', $columns);
        $data = self::db()->fetchAll("SELECT {$columns} FROM " . static::$table);
        
        return array_map(fn($row) => new static($row), $data);
    }

    public static function where(string $column, $value, string $operator = '='): array
    {
        $data = self::db()->fetchAll(
            "SELECT * FROM " . static::$table . " WHERE {$column} {$operator} :value",
            ['value' => $value]
        );
        
        return array_map(fn($row) => new static($row), $data);
    }

    public static function first(string $column, $value): ?static
    {
        $data = self::db()->fetch(
            "SELECT * FROM " . static::$table . " WHERE {$column} = :value LIMIT 1",
            ['value' => $value]
        );
        
        return $data ? new static($data) : null;
    }

    public static function create(array $data): static
    {
        $id = self::db()->insert(static::$table, $data);
        return self::find($id);
    }

    public function save(): bool
    {
        $id = $this->attributes[static::$primaryKey] ?? null;
        
        if ($id) {
            // Update
            $data = array_diff_assoc($this->attributes, $this->original);
            unset($data[static::$primaryKey]);
            
            if (empty($data)) {
                return true;
            }
            
            return self::db()->update(
                static::$table,
                $data,
                static::$primaryKey . " = :id",
                ['id' => $id]
            ) > 0;
        } else {
            // Insert
            $id = self::db()->insert(static::$table, $this->attributes);
            $this->attributes[static::$primaryKey] = $id;
            $this->original = $this->attributes;
            return $id > 0;
        }
    }

    public function delete(): bool
    {
        $id = $this->attributes[static::$primaryKey] ?? null;
        
        if (!$id) {
            return false;
        }
        
        return self::db()->delete(
            static::$table,
            static::$primaryKey . " = :id",
            ['id' => $id]
        ) > 0;
    }

    public static function count(string $column = '*'): int
    {
        return (int) self::db()->fetchColumn(
            "SELECT COUNT({$column}) FROM " . static::$table
        );
    }

    public static function exists(string $column, $value): bool
    {
        return self::db()->fetchColumn(
            "SELECT COUNT(*) FROM " . static::$table . " WHERE {$column} = :value",
            ['value' => $value]
        ) > 0;
    }

    public static function paginate(int $perPage = 15, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        $total = self::count();
        $totalPages = (int) ceil($total / $perPage);
        
        $data = self::db()->fetchAll(
            "SELECT * FROM " . static::$table . " ORDER BY " . static::$primaryKey . " DESC LIMIT :limit OFFSET :offset",
            ['limit' => $perPage, 'offset' => $offset]
        );
        
        return [
            'data' => array_map(fn($row) => new static($row), $data),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $totalPages,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];
    }
}