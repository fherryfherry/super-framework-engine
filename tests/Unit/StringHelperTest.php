<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    public function testConvertSnakeToCamelCase(): void
    {
        $this->assertEquals('helloWorld', convert_snake_to_CamelCase('hello_world'));
        $this->assertEquals('HelloWorld', convert_snake_to_CamelCase('hello_world', true));
    }

    public function testConvertUpperCamelToSnake(): void
    {
        $this->assertEquals('hello-world', convert_UpperCamel_to_snake('HelloWorld'));
        $this->assertEquals('hello_world', convert_UpperCamel_to_snake('HelloWorld', '_'));
    }

    public function testRandomString(): void
    {
        $this->assertEquals(10, strlen(random_string()));
        $this->assertEquals(5, strlen(random_string(5)));
    }

    public function testStrSlug(): void
    {
        $this->assertEquals('hello-world', str_slug('Hello World!'));
        $this->assertEquals('n-a', str_slug(''));
    }
}
