<?php

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;

abstract class UnitTestCase extends TestCase
{
    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = false;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Unit tests shouldn't touch the database by default
        // but we keep RefreshDatabase for factories if needed
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
