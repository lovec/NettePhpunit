<?php

namespace HQ\Test\Connection;

abstract class AbstractConnection
{
	protected $name;
	protected $schemaFile;

	/**
	 * Connection constructor.
	 *
	 * @param $name
	 * @param $schemaFile
	 */
	public function __construct(
		$name,
		$schemaFile
	)
	{
		$this->name = $name;
		$this->schemaFile = $schemaFile;
	}

	/**
	 * @return \PDO
	 */
	abstract public function getPdo();

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	public function createDatabaseSchema($schemaContent)
	{
		$this->execute('SET FOREIGN_KEY_CHECKS=0');
		$this->execute($schemaContent);
		$this->execute('SET FOREIGN_KEY_CHECKS=1');
	}

	/**
	 * @return mixed
	 */
	public function getSchemaFile()
	{
		return $this->schemaFile;
	}

	public function beginTransaction()
	{
		$this->getPdo()->beginTransaction();
	}

	public function rollback()
	{
		$this->getPdo()->rollBack();
	}

	public function commit()
	{
		$this->getPdo()->commit();
	}

	public function execute($sql, array $params = [])
	{
		$stmt = $this->getPdo()->prepare($sql);
		$stmt->execute($params);

		return $stmt;
	}
}