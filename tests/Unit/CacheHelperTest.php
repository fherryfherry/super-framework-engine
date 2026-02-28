<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CacheHelperTest extends TestCase
{
    protected $cacheDir;

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
        // Positive cases
        cache('user_1', 'John Doe');
        $this->assertEquals('John Doe', cache('user_1'));

        cache(['user_2' => 'Jane Doe', 'user_3' => 'Bob'], null, 'batch_tag');
        $this->assertEquals('Jane Doe', cache('user_2', null, 'batch_tag'));
        $this->assertEquals('Bob', cache('user_3', null, 'batch_tag'));

        // Negative/Edge cases
        $this->assertNull(cache('non_existent'));
        $this->assertNull(cache('user_1', null, 'wrong_tag'));
    }

    public function testCacheExpiration(): void
    {
        // Negative case: expired cache
        // We manually create an expired cache file
        $tag = md5('general');
        $key = md5('expired_key');
        $file_path = $this->cacheDir . '/' . $tag . '.' . $key;
        
        file_put_contents($file_path, json_encode([
            'expired' => time() - 3600, // 1 hour ago
            'content' => 'Old Data'
        ]));
        
        $this->assertNull(cache('expired_key'));
        $this->assertFileDoesNotExist($file_path);
    }

    public function testCacheForget(): void
    {
        // Positive case
        cache('user_1', 'John Doe');
        cache_forget('user_1');
        $this->assertNull(cache('user_1'));

        // Negative case: forget non-existent
        cache_forget('non_existent'); // Should not throw error
        $this->assertTrue(true);
    }

    public function testCacheTagForget(): void
    {
        // Positive case
        cache('user_1', 'John Doe', 'user_tag');
        cache('user_2', 'Jane Doe', 'user_tag');
        cache('other', 'Other', 'other_tag');
        
        cache_tag_forget('user_tag');
        
        $this->assertNull(cache('user_1', null, 'user_tag'));
        $this->assertNull(cache('user_2', null, 'user_tag'));
        $this->assertEquals('Other', cache('other', null, 'other_tag'));

        // Negative case: forget non-existent tag
        cache_tag_forget('non_existent_tag'); // Should not throw error
        $this->assertTrue(true);
    }
}
