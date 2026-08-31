<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Empêche tout appel réseau réel vers l'API Twilio pendant les tests
        // (SmsService y fait un vrai appel HTTP dès que les identifiants sont configurés).
        Http::preventStrayRequests();
        Http::fake();
    }
}
