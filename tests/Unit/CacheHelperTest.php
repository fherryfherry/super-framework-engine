<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CacheHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $this->cacheDir = base_path('bootstrap/cache');
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testCacheHelper(): void
    {
        cache('user_1', 'John Doe');
        $this->assertEquals('John Doe', cache('user_1'));
    }

    public function testCacheForget(): void
    {
        cache('user_1', 'John Doe');
        cache_forget('user_1');
        $this->assertNull(cache('user_1'));
    }

    public function testCacheTagForget(): void
    {
        cache('user_1', 'John Doe', 'user_tag');
        cache('user_2', 'Jane Doe', 'user_tag');
        cache('other', 'Other', 'other_tag');
        
        cache_tag_forget('user_tag');
        
        $this->assertNull(cache('user_1', null, 'user_tag'));
        $this->assertNull(cache('user_2', null, 'user_tag'));
        $this->assertEquals('Other', cache('other', null, 'other_tag'));
    }
}
