<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\App\UtilSecurity\Hash;

class HashTest extends TestCase
{
    public function testHashMakeAndCheck(): void
    {
        $password = 'secret';
        $hash = Hash::make($password);
        
        $this->assertTrue(Hash::check($password, $hash));
        $this->assertFalse(Hash::check('wrong', $hash));
    }
}
