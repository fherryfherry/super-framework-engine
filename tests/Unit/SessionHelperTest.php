<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SessionHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testSessionHelper(): void
    {
        session(['user' => 'Ferry']);
        $this->assertEquals('Ferry', session('user'));
        $this->assertEquals('Ferry', $_SESSION['user']);
    }

    public function testSessionForget(): void
    {
        session(['user' => 'Ferry']);
        session_forget('user');
        $this->assertNull(session('user'));
    }

    public function testSessionFlash(): void
    {
        session_flash(['message' => 'Success']);
        $flash = session_flash();
        $this->assertEquals('Success', $flash['message']);
        $this->assertNull(session_flash());
    }

    public function testCsrfToken(): void
    {
        $token = csrf_token();
        $this->assertNotEmpty($token);
        $this->assertEquals($token, csrf_token());
    }

    public function testCsrfValidation(): void
    {
        $token = csrf_token();
        $_REQUEST['_token'] = $token;
        $this->assertTrue(csrf_validation());
        $this->assertFalse(csrf_validation()); // Should be false after one validation
    }
}
