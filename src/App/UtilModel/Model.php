<?php

declare(strict_types=1);

namespace SuperFrameworkEngine\App\UtilModel;

use ArrayAccess;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use SuperFrameworkEngine\App\UtilORM\ORM;

abstract class Model implements ArrayAccess
{
    protected ?string $table = null;
    protected string $primaryKey = "id";
    protected array $attributes = [];

    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset): mixed
    {
        return $this->{$offset} ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->{$offset});
    }

    public function __construct(?array $row = null)
    {
        if ($row !== null) {
            $this->fill($row);
        }
    }

    public function fill(array $row): self
    {
        foreach ($row as $column => $value) {
            $this->{$column} = $value;
        }
        return $this;
    }

    public static function tableName(): string
    {
        return (new static())->table ?? strtolower(basename(str_replace('\\', '/', static::class)));
    }

    public static function primaryKeyName(): string
    {
        return (new static())->primaryKey;
    }

    public static function beginTransaction(): void
    {
        ORM::beginTransaction();
    }

    public static function commit(): void
    {
        ORM::commit();
    }

    public static function rollback(): void
    {
        ORM::rollback();
    }

    public static function count(): int
    {
        return db(static::tableName())->count();
    }

    public static function query(): ORM
    {
        return db(static::tableName());
    }

    public static function with(string ...$relations): ORM
    {
        return static::query()->with(...$relations);
    }

    private static function isSoftDelete(): bool
    {
        try {
            $class = new ReflectionClass(static::class);
            return $class->hasProperty("deleted_at");
        } catch (ReflectionException) {
            return false;
        }
    }

    private static function columns(): array
    {
        try {
            $class = new ReflectionClass(static::class);
            $result = [];
            foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                $result[] = $property->getName();
            }
            return $result;
        } catch (ReflectionException) {
            return [];
        }
    }

    public static function findById(mixed $id): ?static
    {
        if (!$id) {
            return null;
        }

        $cacheKey = static::class . '_findById_' . $id;
        if ($cached = get_singleton($cacheKey)) {
            return $cached;
        }

        $row = db(static::tableName())->find($id);
        if (!$row) {
            return null;
        }

        $instance = new static($row);
        put_singleton($cacheKey, $instance);
        return $instance;
    }

    public static function findBy(string $column, mixed $value): ?static
    {
        $cacheKey = static::class . '_findBy_' . $column . '_' . $value;
        if ($cached = get_singleton($cacheKey)) {
            return $cached;
        }

        $row = db(static::tableName())->where($column . " = ?", [$value])->find();
        if (!$row) {
            return null;
        }

        $instance = new static($row);
        put_singleton($cacheKey, $instance);
        return $instance;
    }

    public static function all(?int $limit = null, int $offset = 0): array
    {
        $query = db(static::tableName());
        if (static::isSoftDelete()) {
            $query->whereNull("deleted_at");
        }

        $results = $query->all($limit, $offset);
        return array_map(fn($row) => new static($row), $results);
    }

    public static function paginate(int $limit = 10, string $orderBy = "id", string $orderDir = "desc"): array
    {
        $query = db(static::tableName());
        if (static::isSoftDelete()) {
            $query->whereNull("deleted_at");
        }

        $data = $query->orderBy($orderBy . " " . $orderDir)->paginate($limit);
        $data['data'] = array_map(fn($row) => new static($row), $data['data']);
        return $data;
    }

    public function save(): self
    {
        $columns = static::columns();
        $dataArray = [];
        foreach ($columns as $column) {
            if (isset($this->{$column})) {
                $dataArray[$column] = $this->{$column};
            }
        }

        $primaryKey = static::primaryKeyName();
        $id = $this->{$primaryKey} ?? null;

        if ($id) {
            if (property_exists($this, "updated_at")) {
                $dataArray['updated_at'] = date('Y-m-d H:i:s');
                $this->updated_at = $dataArray['updated_at'];
            }
            db(static::tableName())->where($primaryKey . " = ?", [$id])->update($dataArray);
        } else {
            if (property_exists($this, "created_at")) {
                $dataArray['created_at'] = date('Y-m-d H:i:s');
                $this->created_at = $dataArray['created_at'];
            }
            $newId = db(static::tableName())->insert($dataArray);
            $this->{$primaryKey} = $newId;
        }

        return $this;
    }

    public static function delete(mixed $id): void
    {
        if (static::isSoftDelete()) {
            db(static::tableName())
                ->where(static::primaryKeyName() . " = ?", [$id])
                ->update(["deleted_at" => date("Y-m-d H:i:s")]);
        } else {
            db(static::tableName())->delete($id);
        }
    }

    public static function hardDelete(mixed $id): void
    {
        db(static::tableName())->delete($id);
    }
}
