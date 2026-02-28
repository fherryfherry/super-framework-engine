<?php

declare(strict_types=1);

namespace Tests\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\App\UtilORM\ORM;

class ORMTest extends TestCase
{
    private PDO $pdo;
    private ORM $orm;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)");
        
        // Set the static connection for ORM
        $reflection = new \ReflectionClass(ORM::class);
        $property = $reflection->getProperty('dbConn');
        $property->setAccessible(true);
        $property->setValue(null, $this->pdo);

        $this->orm = new ORM($this->pdo);
        $this->orm->db('users');
    }

    public function testInsertAndFind(): void
    {
        $id = $this->orm->insert(['name' => 'John Doe', 'email' => 'john@example.com']);
        $user = $this->orm->find($id);

        $this->assertEquals('John Doe', $user['name']);
        $this->assertEquals('john@example.com', $user['email']);
    }

    public function testUpdate(): void
    {
        $id = $this->orm->insert(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
        $this->orm->where('id = ?', [$id])->update(['name' => 'Jane Smith']);

        $user = $this->orm->find($id);
        $this->assertEquals('Jane Smith', $user['name']);
    }

    public function testDelete(): void
    {
        $id = $this->orm->insert(['name' => 'Delete Me', 'email' => 'delete@example.com']);
        $this->orm->delete($id);

        $user = $this->orm->find($id);
        $this->assertFalse($user);
    }

    public function testAll(): void
    {
        $this->orm->insert(['name' => 'User 1', 'email' => 'u1@example.com']);
        $this->orm->insert(['name' => 'User 2', 'email' => 'u2@example.com']);

        $users = $this->orm->all();
        $this->assertCount(2, $users);
    }

    public function testQueryBuilder(): void
    {
        $this->orm->insert(['name' => 'John', 'email' => 'john@example.com']);
        $this->orm->insert(['name' => 'Jane', 'email' => 'jane@example.com']);

        // Select
        $res = $this->orm->select('name')->where('name = ?', ['John'])->first();
        $this->assertEquals('John', $res['name']);
        $this->assertArrayNotHasKey('email', $res);

        // OrderBy
        $res = $this->orm->orderBy('name desc')->first();
        $this->assertEquals('John', $res['name']);

        // Limit & Offset
        $res = $this->orm->orderBy('name asc')->limit(1)->offset(1)->all();
        $this->assertCount(1, $res);
        $this->assertEquals('John', $res[0]['name']);

        // GroupBy (SQLite specific check)
        $this->orm->insert(['name' => 'John', 'email' => 'john2@example.com']);
        $res = $this->orm->groupBy('name')->select('name, COUNT(*) as count')->orderBy('name desc')->all();
        // John should have 2
        $found = false;
        foreach($res as $r) {
            if($r['name'] == 'John') {
                $this->assertEquals(2, $r['count']);
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    public function testTransactions(): void
    {
        ORM::beginTransaction();
        $this->orm->insert(['name' => 'Trans User', 'email' => 'trans@example.com']);
        ORM::rollback();

        $user = (new ORM($this->pdo))->db('users')->where('name = ?', ['Trans User'])->first();
        $this->assertFalse($user);

        ORM::beginTransaction();
        $this->orm->insert(['name' => 'Trans User 2', 'email' => 'trans2@example.com']);
        ORM::commit();

        $user = (new ORM($this->pdo))->db('users')->where('name = ?', ['Trans User 2'])->first();
        $this->assertIsArray($user);
    }

    public function testUpdateAndDelete(): void
    {
        $id = $this->orm->insert(['name' => 'To Update', 'email' => 'update@example.com']);
        
        // Update
        $this->orm->where('id = ?', [$id])->update(['name' => 'Updated']);
        $user = $this->orm->where('id = ?', [$id])->first();
        $this->assertEquals('Updated', $user['name']);

        // Delete
        $this->orm->where('id = ?', [$id])->delete();
        $user = $this->orm->where('id = ?', [$id])->first();
        $this->assertFalse($user);
    }

    public function testAdvancedWhere(): void
    {
        $this->orm->insert(['name' => 'Null Name', 'email' => 'null@example.com']);
        // Use new instance for update to avoid polluting subsequent queries if we reused $this->orm
        (new ORM($this->pdo))->db('users')->where('name = ?', ['Null Name'])->update(['name' => null]);

        // Use new instance for query
        $res = (new ORM($this->pdo))->db('users')->whereNull('name')->first();
        $this->assertNull($res['name']);

        $res = (new ORM($this->pdo))->db('users')->whereNotNull('email')->first();
        $this->assertEquals('null@example.com', $res['email']);

        $this->orm->insert(['name' => 'Date User', 'email' => 'date@example.com']);
        
        $res = (new ORM($this->pdo))->db('users')->whereLike('email', 'date')->first();
        $this->assertEquals('Date User', $res['name']);
        
        $res = (new ORM($this->pdo))->db('users')->whereIn('email', ['date@example.com', 'null@example.com'])->all();
        $this->assertCount(2, $res);

        $res = (new ORM($this->pdo))->db('users')->whereNotIn('email', ['date@example.com'])->first();
        $this->assertEquals('null@example.com', $res['email']);
    }

    public function testJoins(): void
    {
        $this->pdo->exec("CREATE TABLE posts (id INTEGER PRIMARY KEY, user_id INTEGER, title TEXT)");
        $userId = $this->orm->insert(['name' => 'Author', 'email' => 'author@example.com']);
        (new ORM($this->pdo))->db('posts')->insert(['user_id' => $userId, 'title' => 'My Post']);

        $res = (new ORM($this->pdo))->db('users')
            ->select('users.name', 'posts.title')
            ->join('posts ON posts.user_id = users.id')
            ->first();
            
        $this->assertEquals('Author', $res['name']);
        $this->assertEquals('My Post', $res['title']);
        
        // Test helper methods
        (new ORM($this->pdo))->db('users')->leftJoin('posts ON posts.user_id = users.id');
        (new ORM($this->pdo))->db('users')->rightJoin('posts ON posts.user_id = users.id');
        (new ORM($this->pdo))->db('users')->outerJoin('posts ON posts.user_id = users.id');
        $this->assertTrue(true); // Just checking if methods run without error
    }

    public function testAddSelect(): void
    {
        $this->orm->insert(['name' => 'Select Me', 'email' => 'select@example.com']);
        $res = (new ORM($this->pdo))->db('users')->select('name')->addSelect('email')->first();
        $this->assertEquals('Select Me', $res['name']);
        $this->assertEquals('select@example.com', $res['email']);
    }

    public function testCaching(): void
    {
        $this->orm->insert(['name' => 'Cache Me', 'email' => 'cache@example.com']);
        
        // First call to cache
        $orm1 = new ORM($this->pdo);
        $res = $orm1->db('users')->remember(60)->where('name = ?', ['Cache Me'])->first();
        $this->assertEquals('Cache Me', $res['name']);
        
        // Delete directly to verify cache usage
        (new ORM($this->pdo))->db('users')->where('name = ?', ['Cache Me'])->delete();
        
        // Should still return result from cache
        $orm2 = new ORM($this->pdo);
        $res = $orm2->db('users')->remember(60)->where('name = ?', ['Cache Me'])->first();
        $this->assertEquals('Cache Me', $res['name']);
    }

    public function testEagerLoading(): void
    {
        // Setup relations
        $this->pdo->exec("CREATE TABLE roles (id INTEGER PRIMARY KEY, name TEXT)");
        $this->pdo->exec("CREATE TABLE user_roles (id INTEGER PRIMARY KEY, user_id INTEGER, role_id INTEGER)"); 
        
        // So users table needs roles_id (plural because relation name is roles)
        $this->pdo->exec("ALTER TABLE users ADD COLUMN roles_id INTEGER");
        
        $roleId = (new ORM($this->pdo))->db('roles')->insert(['name' => 'Admin']);
        (new ORM($this->pdo))->db('users')->insert(['name' => 'Admin User', 'email' => 'admin@example.com', 'roles_id' => $roleId]);
        
        $user = (new ORM($this->pdo))->db('users')->with('roles')->where('name = ?', ['Admin User'])->first();
        
        $this->assertIsArray($user);
        $this->assertArrayHasKey('roles', $user);
        $this->assertEquals('Admin', $user['roles']['name']);
    }

    public function testRawQuery(): void
    {
        $this->orm->insert(['name' => 'Raw', 'email' => 'raw@example.com']);
        $res = $this->orm->raw("SELECT * FROM users WHERE name = ?", ['Raw'])->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $res);
        $this->assertEquals('Raw', $res[0]['name']);
    }

    public function testAggregates(): void
    {
        $this->pdo->exec("CREATE TABLE posts (id INTEGER PRIMARY KEY, user_id INTEGER, title TEXT)");
        $this->orm->insert(['name' => 'A', 'email' => 'a@example.com']);
        $this->orm->db('posts')->insert(['user_id' => 1, 'title' => 'Post 1']); // id=1
        $this->orm->db('posts')->insert(['user_id' => 1, 'title' => 'Post 2']); // id=2
        $this->orm->db('posts')->insert(['user_id' => 1, 'title' => 'Post 3']); // id=3

        $count = (new ORM($this->pdo))->db('posts')->count();
        $this->assertEquals(3, $count);

        // SQLite IDs are integers.
        $max = (new ORM($this->pdo))->db('posts')->max('id');
        $this->assertEquals(3, $max);

        $min = (new ORM($this->pdo))->db('posts')->min('id');
        $this->assertEquals(1, $min);

        $sum = (new ORM($this->pdo))->db('posts')->sum('id');
        $this->assertEquals(6, $sum);

        $avg = (new ORM($this->pdo))->db('posts')->avg('id');
        $this->assertEquals(2, $avg);
    }

    public function testSchemaInfo(): void
    {
        $this->assertTrue((new ORM($this->pdo))->hasTable('users'));
        $this->assertFalse((new ORM($this->pdo))->hasTable('non_existent'));

        $this->assertTrue((new ORM($this->pdo))->hasColumn('users', 'email'));
        $this->assertFalse((new ORM($this->pdo))->hasColumn('users', 'wrong_col'));

        $columns = (new ORM($this->pdo))->listColumn('users');
        $this->assertContains('name', $columns);
        $this->assertContains('email', $columns);

        $tables = (new ORM($this->pdo))->listTable();
        $this->assertContains('users', $tables);
        
        $pk = (new ORM($this->pdo))->findPrimaryKey('users');
        $this->assertEquals('id', $pk);
    }
}
