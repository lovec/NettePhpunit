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
		$this->execute($schemaContent);
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
	
	public function explodeAndExecute($sql)
    	{
		$queryArray = explode(";\n", $sql);

		foreach($queryArray as $queryArrayItem) {
			$queryArrayItem = trim($queryArrayItem);
			if (empty($queryArrayItem)) {
				continue;
			}

			$this->getPdo()->exec($queryArrayItem);
		}
	}

	public function enableForeignKeyChecks()
	{
		$this->setForeignKeyChecks(true);
	}

	public function disableForeignKeyChecks()
	{
		$this->setForeignKeyChecks(false);
	}

	public function setForeignKeyChecks($enable = true)
	{
		$this->execute(
			sprintf('SET FOREIGN_KEY_CHECKS=%d', $enable)
		);
	}
}
