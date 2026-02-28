<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    public function testConvertSnakeToCamelCase(): void
    {
        // Positive cases
        $this->assertEquals('helloWorld', convert_snake_to_CamelCase('hello_world'));
        $this->assertEquals('HelloWorld', convert_snake_to_CamelCase('hello_world', true));
        $this->assertEquals('someLongerString', convert_snake_to_CamelCase('some_longer_string'));

        // Negative/Edge cases
        $this->assertEquals('', convert_snake_to_CamelCase(''));
        $this->assertEquals('alreadyCamel', convert_snake_to_CamelCase('alreadyCamel'));
    }

    public function testConvertUpperCamelToSnake(): void
    {
        // Positive cases
        $this->assertEquals('hello-world', convert_UpperCamel_to_snake('HelloWorld'));
        $this->assertEquals('hello_world', convert_UpperCamel_to_snake('HelloWorld', '_'));

        // Negative/Edge cases
        $this->assertEquals('', convert_UpperCamel_to_snake(''));
        $this->assertEquals('already-snake', convert_UpperCamel_to_snake('already-snake'));
    }

    public function testRandomString(): void
    {
        // Positive cases
        $this->assertEquals(10, strlen(random_string()));
        $this->assertEquals(5, strlen(random_string(5)));
        $this->assertEquals(100, strlen(random_string(100)));

        // Negative/Edge cases
        $this->assertEquals(0, strlen(random_string(0)));
    }

    public function testStrSlug(): void
    {
        // Positive cases
        $this->assertEquals('hello-world', str_slug('Hello World!'));
        $this->assertEquals('this-is-a-test', str_slug('This is a test...'));
        $this->assertEquals('complex-slug-with-123', str_slug('Complex slug with 123!!!'));

        // Negative/Edge cases
        $this->assertEquals('n-a', str_slug(''));
        $this->assertEquals('n-a', str_slug('!!!@@@###'));
    }
}
