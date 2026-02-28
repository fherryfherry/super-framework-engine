<?php

declare(strict_types=1);

namespace Tests\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\App\UtilDataTable\DataTable;
use SuperFrameworkEngine\App\UtilORM\ORM;

class DataTableTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)");
        $this->pdo->exec("INSERT INTO users (name, email) VALUES ('John', 'john@example.com'), ('Jane', 'jane@example.com')");
        
        $reflection = new \ReflectionClass(ORM::class);
        $property = $reflection->getProperty('dbConn');
        $property->setAccessible(true);
        $property->setValue(null, $this->pdo);

        $_REQUEST = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => '']
        ];
    }

    public function testDataTableGet(): void
    {
        $dt = new DataTable('users');
        $result = $dt->get();

        $this->assertEquals(1, $result['draw']);
        $this->assertEquals(2, $result['recordsTotal']);
        $this->assertCount(2, $result['data']);
        // Default order is ID desc
        $this->assertEquals('Jane', $result['data'][0]['name']);
    }

    public function testDataTableSearch(): void
    {
        $_REQUEST['search']['value'] = 'John';
        $dt = new DataTable('users');
        $dt->searchable(['name']);
        $result = $dt->get();

        $this->assertEquals(1, $result['recordsFiltered']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('John', $result['data'][0]['name']);
    }
}
