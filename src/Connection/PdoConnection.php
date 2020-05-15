<?php declare(strict_types = 1);

namespace HQ\Test\Connection;

class PdoConnection extends AbstractConnection
{
	/** @var \PDO|NULL */
	private $pdo;


	public function __construct(
		string $name,
		string $schemaFile,
		\PDO $pdo
	)
	{
		parent::__construct($name, $schemaFile);

		$this->pdo = $pdo;
	}


	public function getPdo(): \PDO
	{
		return $this->pdo;
	}


	public function disconnect(): void
	{
		$this->pdo = NULL;
	}

}
