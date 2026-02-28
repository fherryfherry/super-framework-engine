<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\App\UtilValidator\Validator;
use SuperFrameworkEngine\Exceptions\ValidatorException;

class ValidatorTest extends TestCase
{
    public function testRequiredRule(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Column `name` is required');

        Validator::make(['name' => ''], ['name' => 'required']);
    }

    public function testUniqueRule(): void
    {
        // Mock DB
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT)");
        $pdo->exec("INSERT INTO users (email) VALUES ('taken@example.com')");
        
        $reflection = new \ReflectionClass(\SuperFrameworkEngine\App\UtilORM\ORM::class);
        $property = $reflection->getProperty('dbConn');
        $property->setAccessible(true);
        $property->setValue(null, $pdo);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Data email 'taken@example.com' has already exists!");

        Validator::make(['email' => 'taken@example.com'], ['email' => 'unique:users']);
    }

    public function testUniqueRuleSuccess(): void
    {
        // Mock DB
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT)");
        
        $reflection = new \ReflectionClass(\SuperFrameworkEngine\App\UtilORM\ORM::class);
        $property = $reflection->getProperty('dbConn');
        $property->setAccessible(true);
        $property->setValue(null, $pdo);

        Validator::make(['email' => 'new@example.com'], ['email' => 'unique:users']);
        $this->assertTrue(true);
    }

    public function testExistsRule(): void
    {
        // Mock DB
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT)");
        
        $reflection = new \ReflectionClass(\SuperFrameworkEngine\App\UtilORM\ORM::class);
        $property = $reflection->getProperty('dbConn');
        $property->setAccessible(true);
        $property->setValue(null, $pdo);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Data for `user_id` is not exists!");

        Validator::make(['user_id' => 999], ['user_id' => 'exists:users,id']);
    }

    public function testExistsRuleSuccess(): void
    {
        // Mock DB
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT)");
        $pdo->exec("INSERT INTO users (id, email) VALUES (1, 'exist@example.com')");
        
        $reflection = new \ReflectionClass(\SuperFrameworkEngine\App\UtilORM\ORM::class);
        $property = $reflection->getProperty('dbConn');
        $property->setAccessible(true);
        $property->setValue(null, $pdo);

        Validator::make(['user_id' => 1], ['user_id' => 'exists:users,id']);
        $this->assertTrue(true);
    }

    public function testEmailRule(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Column `email` should be email');

        Validator::make(['email' => 'invalid-email'], ['email' => 'email']);
    }

    public function testUrlRule(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Column `website` should be url');

        Validator::make(['website' => 'not-a-url'], ['website' => 'url']);
    }

    public function testIntRule(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Column `age` should be integer');

        Validator::make(['age' => 'twenty'], ['age' => 'int']);
    }

    public function testValidData(): void
    {
        Validator::make(
            ['name' => 'John', 'email' => 'john@example.com', 'age' => 25],
            ['name' => 'required', 'email' => 'email', 'age' => 'int']
        );
        $this->assertTrue(true); // No exception thrown
    }
}
