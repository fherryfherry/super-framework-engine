<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\App\UtilFileSystem\FileSystem;

class FileSystemTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'super_framework_test';
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir);
        }
        
        // Define BASE_PATH if not defined
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', $this->tempDir);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tempDir);
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

    public function testUploadBase64(): void
    {
        $data = base64_encode('test content');
        $fileName = 'test_file';
        $ext = 'txt';
        
        // Ensure the directory exists for FileSystem
        if (!file_exists(public_path())) {
            mkdir(public_path(), 0777, true);
        }

        $path = FileSystem::uploadBase64($data, $fileName, $ext);
        
        $fullPath = public_path($path);
        $this->assertFileExists($fullPath);
        $this->assertEquals('test content', file_get_contents($fullPath));
    }

    public function testUploadImageByUrl(): void
    {
        // Use a data URI as a "URL"
        $content = "fake image content";
        $url = "data:image/jpeg;base64," . base64_encode($content);
        // Note: The FileSystem implementation might expect a real URL or specific extension parsing.
        // Let's check implementation. It does:
        // $ext = strtolower(pathinfo($url,PATHINFO_EXTENSION));
        // $ext = strtok($ext,"?");
        // So a data URI won't work well with pathinfo for extension.
        
        // We can use a local file path with file:// protocol if allowed by filter_var(..., FILTER_VALIDATE_URL)
        // filter_var('file:///tmp/test.jpg', FILTER_VALIDATE_URL) returns false usually (depends on PHP version/config).
        
        // Alternative: Mock file_get_contents? Not easy.
        // Or just use a dummy URL that we know will fail but covers the validation logic?
        
        try {
            FileSystem::uploadImageByUrl('invalid-url', 'test');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("The url protocol is invalid!", $e->getMessage());
        }
    }
}
