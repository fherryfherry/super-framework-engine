<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class DateTimeHelperTest extends TestCase
{
    public function testCarbonHelper(): void
    {
        $this->assertInstanceOf(Carbon::class, carbon());
    }
}
