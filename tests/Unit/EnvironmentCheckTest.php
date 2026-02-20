<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnvironmentCheckTest extends TestCase
{
    #[Test]
    public function test_environment_is_testing()
    {
        $this->assertEquals('testing', config('app.env'));
        $this->assertEquals('testing', app()->environment());
    }
}
