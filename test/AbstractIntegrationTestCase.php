<?php

namespace NettePhpunit\Test;

use PDO;
use Nette\DI\Container;
use HQ\Test\AbstractDbTestCase;
use HQ\Test\Connection\AbstractConnection;
use HQ\Test\Connection\PdoConnection;

class AbstractIntegrationTestCase extends AbstractDbTestCase
{
	/**
	 * @return AbstractConnection[]
	 */
	protected function getConnections()
	{
		// prepare pdo
		$database = $this->container->parameters['database'];
		$pdo = new PDO($database['dsn'], $database['user'], $database['pass'], [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);

		return [
			new PdoConnection('default', __DIR__ . '/default-schema.sql', $pdo)
		];
	}

	public function getBaseFixtureDir()
	{
		return __DIR__;
	}

	/**
	 * @return \Nette\Di\Container
	 */
	protected function getContainer()
	{
		return new Container(require __DIR__ . '/config.php');
	}
}