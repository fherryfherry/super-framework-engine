<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\Helpers\Collection;

class CollectionTest extends TestCase
{
    private array $data = [
        ['id' => 1, 'name' => 'John', 'age' => 25],
        ['id' => 2, 'name' => 'Jane', 'age' => 30],
        ['id' => 3, 'name' => 'Doe', 'age' => 35],
    ];

    public function testCountAndExist(): void
    {
        $collection = new Collection($this->data);
        $this->assertEquals(3, $collection->count());
        $this->assertTrue($collection->exist());

        $empty = new Collection([]);
        $this->assertFalse($empty->exist());
    }

    public function testFirst(): void
    {
        $collection = new Collection($this->data);
        $first = $collection->first();
        $this->assertEquals($this->data[0], $first[0]);
    }

    public function testSumAndAverage(): void
    {
        $collection = new Collection($this->data);
        $this->assertEquals(90, $collection->sum('age'));
        $this->assertEquals(30, $collection->average('age'));
    }

    public function testWhereFilters(): void
    {
        $collection = new Collection($this->data);
        
        $res = $collection->whereEqual('name', 'John')->get();
        $this->assertCount(1, $res);
        $this->assertEquals('John', $res[0]['name']);

        $collection = new Collection($this->data);
        $res = $collection->whereNotEqual('name', 'John')->get();
        $this->assertCount(2, $res);

        $collection = new Collection($this->data);
        $res = $collection->whereIn('id', [1, 3])->get();
        $this->assertCount(2, $res);

        $collection = new Collection($this->data);
        $res = $collection->whereNotIn('id', [1, 3])->get();
        $this->assertCount(1, $res);
        $this->assertEquals(2, $res[0]['id']);

        $collection = new Collection($this->data);
        $res = $collection->whereGreaterThan('age', 30)->get();
        $this->assertCount(1, $res);
        $this->assertEquals(35, $res[0]['age']);

        $collection = new Collection($this->data);
        $res = $collection->whereLessThan('age', 30)->get();
        $this->assertCount(1, $res);
        $this->assertEquals(25, $res[0]['age']);

        $collection = new Collection($this->data);
        $res = $collection->whereGreaterThanEq('age', 30)->get();
        $this->assertCount(2, $res);

        $collection = new Collection($this->data);
        $res = $collection->whereLessThanEq('age', 30)->get();
        $this->assertCount(2, $res);

        $collection = new Collection($this->data);
        $res = $collection->whereLike('name', 'jo')->get();
        $this->assertCount(1, $res);
        $this->assertEquals('John', $res[0]['name']);

        $collection = new Collection($this->data);
        $res = $collection->whereNotLike('name', 'jo')->get();
        $this->assertCount(2, $res);
    }
}
