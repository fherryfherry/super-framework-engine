<?php

declare(strict_types=1);

namespace Tests\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\App\UtilModel\Model;
use SuperFrameworkEngine\App\UtilORM\ORM;

class ModelTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT, created_at TEXT, updated_at TEXT)");
        
        // Mock ORM to use our PDO instance
        $orm = new ORM($this->pdo);
        // We need to make sure ORM::createConnection returns our mocked instance
        // But for now, let's just use the global db() which we can override if needed.
        // Since we defined db() in our bootstrap, we can make it return our ORM.
    }

    public function testModelSave(): void
    {
        $user = new User();
        $user->name = 'John Doe';
        $user->email = 'john@example.com';
        
        // We need to inject the PDO into the ORM used by the Model
        // For testing, we'll manually set the static dbConn in ORM
        $reflection = new \ReflectionClass(ORM::class);
        $property = $reflection->getProperty('dbConn');
        $property->setAccessible(true);
        $property->setValue(null, $this->pdo);

        $user->save();

        $this->assertNotNull($user->id);
        
        $found = User::findById($user->id);
        $this->assertEquals('John Doe', $found->name);
    }
    public function testSoftDelete(): void
    {
        $this->pdo->exec("CREATE TABLE soft_users (id INTEGER PRIMARY KEY, name TEXT, deleted_at TEXT)");
        
        $reflection = new \ReflectionClass(ORM::class);
        $property = $reflection->getProperty('dbConn');
        $property->setAccessible(true);
        $property->setValue(null, $this->pdo);

        $user = new SoftUser();
        $user->name = 'Soft Delete Me';
        $user->save();

        $id = $user->id;
        SoftUser::delete($id);

        $found = SoftUser::findById($id);
        $this->assertNotNull($found->deleted_at);

        $all = SoftUser::all();
        $this->assertCount(0, $all);
    }

    public function testPaginate(): void
    {
        $this->pdo->exec("DELETE FROM users");
        for ($i = 1; $i <= 25; $i++) {
            $user = new User();
            $user->name = "User $i";
            $user->email = "user$i@example.com";
            $user->save();
        }

        $_REQUEST['page'] = 1;
        $result = User::paginate(10);
        $this->assertCount(10, $result['data']);
        $this->assertEquals(25, $result['total']);
        $this->assertEquals(3, $result['last_page']);

        $_REQUEST['page'] = 3;
        $result = User::paginate(10);
        $this->assertCount(5, $result['data']);

        // Test findAllByPaginate
        $_REQUEST['page'] = 1;
        $result = User::findAllByPaginate(10);
        $this->assertCount(10, $result['data']);
        $this->assertEquals(25, $result['total']);
    }

    public function testFindBy(): void
    {
        $this->pdo->exec("DELETE FROM users");
        $user = new User();
        $user->name = "Find Me";
        $user->email = "find@example.com";
        $user->save();

        $found = User::findBy('email', 'find@example.com');
        $this->assertNotNull($found);
        $this->assertEquals('Find Me', $found->name);

        $notFound = User::findBy('email', 'notfound@example.com');
        $this->assertNull($notFound);
    }

    public function testLoadArray(): void
    {
        $data = ['name' => 'Array User', 'email' => 'array@example.com'];
        $user = User::loadArray($data);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Array User', $user->name);
        $this->assertEquals('array@example.com', $user->email);
    }
}

class User extends Model
{
    protected ?string $table = 'users';
    public $id;
    public $name;
    public $email;
    public $created_at;
    public $updated_at;
}

class SoftUser extends Model
{
    protected ?string $table = 'soft_users';
    public $id;
    public $name;
    public $deleted_at;
}
