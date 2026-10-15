<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $config = $app['config'];
        $memory = $config->get('database.default') === 'sqlite'
            && $config->get('database.connections.sqlite.database') === ':memory:';
        $ciDatabase = getenv('PORTFOLIO_MYSQL_TESTS') === '1'
            && $config->get('database.default') === 'mysql'
            && $config->get('database.connections.mysql.database') === 'portfolio_ci_test'
            && $config->get('database.connections.mysql.username') === 'portfolio_test'
            && $config->get('database.connections.mysql.host') === '127.0.0.1';

        if (! $app->environment('testing') || (! $memory && ! $ciDatabase)) {
            throw new \RuntimeException('Os testes exigem SQLite em memória ou o MySQL isolado portfolio_ci_test, com usuário portfolio_test e opt-in explícito.');
        }

        return $app;
    }
}
