<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Stub Vite so view-rendering tests don't require a built
        // public/build/manifest.json (assets aren't compiled in the test job).
        $this->withoutVite();
    }
}
