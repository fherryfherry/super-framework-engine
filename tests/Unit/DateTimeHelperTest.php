<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class DateTimeHelperTest extends TestCase
{
    public function testCarbonHelper(): void
    {
        // Positive case
        $this->assertInstanceOf(Carbon::class, carbon());
        
        // Check if it's currently now
        $now = Carbon::now();
        $helperNow = carbon();
        $this->assertEquals($now->format('Y-m-d H:i'), $helperNow->format('Y-m-d H:i'));
    }
}
