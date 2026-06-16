<?php

namespace Tests;

use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Bus;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake([SendWhatsAppMessageJob::class]);
    }
}

