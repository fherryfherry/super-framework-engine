<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\Helpers\Collection;

class HelperTest extends TestCase
{
    public function testSimpleCollect(): void
    {
        $data = ['a', 'b', 'c'];
        $collection = simple_collect($data);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals($data, $collection->get());
    }

    public function testArrayUniqueMulti(): void
    {
        $array = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            ['id' => 1, 'name' => 'C'],
        ];
        $result = array_unique_multi($array, 'id');
        $this->assertCount(2, $result);
        $this->assertEquals('A', $result[0]['name']);
        $this->assertEquals('B', $result[1]['name']);
    }

    public function testSingletonHelpers(): void
    {
        put_singleton('test_key', 'test_value');
        $this->assertEquals('test_value', get_singleton('test_key'));
    }

    public function testBasePath(): void
    {
        $this->assertStringContainsString('super-framework-engine', base_path());
        $this->assertStringContainsString('super-framework-engine' . DIRECTORY_SEPARATOR . 'src', base_path('src'));
    }
}
