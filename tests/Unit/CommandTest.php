<?php

declare(strict_types=1);

namespace Tests\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\App\UtilModel\Configs\Command;
use SuperFrameworkEngine\App\UtilORM\ORM;

class CommandTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)");
        
        $reflection = new \ReflectionClass(ORM::class);
        $property = $reflection->getProperty('dbConn');
        $property->setAccessible(true);
        $property->setValue(null, $this->pdo);

        $this->appDir = base_path('app');
        if (!file_exists($this->appDir)) {
            mkdir($this->appDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->appDir);
    }

    private function removeDir($dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                        $this->removeDir($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    public function testMakeModelCommand(): void
    {
        $cmd = new Command();
        // Capture output to prevent it from cluttering the test results
        ob_start();
        $cmd->makeModel('users');
        ob_end_clean();

        $this->assertFileExists(base_path('app/Models/Users.php'));
        $this->assertFileExists(base_path('app/Repositories/UsersRepository.php'));
        $this->assertFileExists(base_path('app/Services/UsersService.php'));
    }
}
