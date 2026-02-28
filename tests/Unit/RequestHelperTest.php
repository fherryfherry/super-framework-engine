<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class RequestHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $_REQUEST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
    }

    public function testRequestMethodIs(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(request_method_is('POST'));
        $this->assertTrue(request_method_is_post());
        $this->assertFalse(request_method_is_get());
    }

    public function testRequestHelper(): void
    {
        $_REQUEST['name'] = 'John';
        $this->assertEquals('John', request('name'));
        $this->assertEquals('Default', request('age', 'Default'));
    }

    public function testRequestInt(): void
    {
        $_REQUEST['age'] = '25';
        $this->assertEquals(25, request_int('age'));

        $_REQUEST['age'] = 'not-an-int';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid integer value of `age`');
        request_int('age');
    }

    public function testRequestEmail(): void
    {
        $_REQUEST['email'] = 'john@example.com';
        $this->assertEquals('john@example.com', request_email('email'));

        $_REQUEST['email'] = 'invalid-email';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid email value of `email`');
        request_email('email');
    }

    public function testRequestString(): void
    {
        $_REQUEST['name'] = '<b>John</b>';
        $this->assertEquals('<b>John</b>', request_string('name'));
        $this->assertEquals('&lt;b&gt;John&lt;/b&gt;', request_string('name', null, true));
    }
}
