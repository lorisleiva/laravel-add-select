<?php

namespace Lorisleiva\LaravelAddSelect\Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return ['Lorisleiva\LaravelAddSelect\ServiceProvider'];
    }
}
