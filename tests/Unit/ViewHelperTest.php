<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ViewHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $this->viewDir = base_path('tests/views');
        if (!file_exists($this->viewDir)) {
            mkdir($this->viewDir, 0777, true);
        }
        
        $this->cacheDir = base_path('bootstrap/views');
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        
        file_put_contents($this->viewDir . '/test.blade.php', 'Hello {{ $name }}');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->viewDir . '/test.blade.php')) {
            unlink($this->viewDir . '/test.blade.php');
        }
        if (file_exists($this->viewDir)) {
            rmdir($this->viewDir);
        }
        
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testViewCustomHelper(): void
    {
        $output = view_custom('tests/views', 'test', ['name' => 'Ferry']);
        $this->assertEquals('Hello Ferry', $output);
    }
}
