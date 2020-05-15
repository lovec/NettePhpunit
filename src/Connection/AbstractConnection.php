<?php declare(strict_types = 1);

namespace HQ\Test\Connection;

abstract class AbstractConnection
{
	/** @var string */
	protected $name;

	/** @var string */
	protected $schemaFile;


	public function __construct(
		string $name,
		string $schemaFile
	)
	{
		$this->name = $name;
		$this->schemaFile = $schemaFile;
	}


	abstract public function getPdo(): \PDO;


	public function getName(): string
	{
		return $this->name;
	}


	public function createDatabaseSchema(string $schemaContent): void
	{
		$this->execute($schemaContent);
	}


	public function getSchemaFile(): string
	{
		return $this->schemaFile;
	}


	public function beginTransaction(): void
	{
		$this->getPdo()->beginTransaction();
	}


	public function rollback(): void
	{
		$this->getPdo()->rollBack();
	}


	public function commit(): void
	{
		$this->getPdo()->commit();
	}


	/**
	 * @param mixed[]|array $params
	 * @return bool|\PDOStatement
	 */
	public function execute(string $sql, array $params = [])
	{
		$stmt = $this->getPdo()->prepare($sql);
		$stmt->execute($params);

		return $stmt;
	}


	public function explodeAndExecute(string $sql): void
	{
		$queryArray = explode(";\n", $sql);

		foreach ($queryArray as $queryArrayItem) {
			$queryArrayItem = trim($queryArrayItem);

			if (!$queryArrayItem) {
				continue;
			}

			$this->getPdo()->exec($queryArrayItem);
		}
	}


	public function enableForeignKeyChecks(): void
	{
		$this->setForeignKeyChecks(TRUE);
	}


	public function disableForeignKeyChecks(): void
	{
		$this->setForeignKeyChecks(FALSE);
	}


	public function setForeignKeyChecks(bool $enable = TRUE): void
	{
		$this->execute(
			sprintf('SET FOREIGN_KEY_CHECKS=%d', $enable)
		);
	}


	abstract public function disconnect(): void;

}
