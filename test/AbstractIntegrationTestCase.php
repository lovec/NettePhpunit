<?php declare(strict_types = 1);

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
	protected function getConnections(): array
	{
		$database = $this->getContainer()->parameters['database'];

		$pdo = new PDO($database['dsn'], $database['user'], $database['pass'], [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);

		return [
			new PdoConnection('default', __DIR__ . '/default-schema.sql', $pdo),
		];
	}


	public function getBaseFixtureDir(): string
	{
		return __DIR__;
	}


	protected function getContainer(): \Nette\DI\Container
	{
		return new Container(require __DIR__ . '/config.php');
	}

}
