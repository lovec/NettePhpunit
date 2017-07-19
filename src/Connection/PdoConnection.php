<?php

namespace HQ\Test\Connection;

class PdoConnection extends AbstractConnection
{
	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * Connection constructor.
	 *
	 * @param $name
	 * @param $schemaFile
	 * @param \PDO $pdo
	 */
	public function __construct(
		$name,
		$schemaFile,
		\PDO $pdo
	)
	{
		$this->name = $name;
		$this->schemaFile = $schemaFile;
		$this->pdo = $pdo;
	}

	public function getPdo()
	{
		return $this->pdo;
	}

    public function disconnect()
    {
        $this->pdo = null;
    }
}