<?php declare(strict_types = 1);

namespace HQ\Test;

use HQ\Test\Connection\AbstractConnection;

abstract class AbstractDbTestCase extends AbstractTestCase
{
	use \PHPUnit\DbUnit\TestCaseTrait;

	private const ENV_CREATE_SCHEMA = 'UNITTEST_CREATE_SCHEMA';

	/** @var bool */
	protected $initialized = FALSE;

	/** @var bool */
	protected static $started = FALSE;

	/** @var bool */
	protected $isInTransactionMode = TRUE;

	/** @var FixtureLoader|NULL */
	protected $fixtureLoader;

	/** @var AbstractConnection[]|NULL */
	protected $connections;

	/** @var bool */
	protected $forceCreateSchema = TRUE;

	/** @var bool */
	protected $disableSchemaForeignKeyChecks = TRUE;

	/** @var bool */
	protected $disableFixturesForeignKeyChecks = TRUE;


	/**
	 * @return AbstractConnection[]
	 */
	abstract protected function getConnections(): array;


	abstract public function getBaseFixtureDir(): string;


	/**
	 * @return mixed[]|array
	 */
	public function getFixtures(): array
	{
		return [];
	}


	public function setUp(): void
	{
		$this->init();

		$this->beforeSetup();
		$this->beginTransactions();
		$this->afterSetup();
	}


	public function tearDown(): void
	{
		$this->beforeTearDown();
		$this->closeTransactions();
		$this->afterTearDown();

		foreach ($this->connections as $connection) {
			$connection->disconnect();
		}
	}


	protected function init(): void
	{
		if ($this->initialized) {
			return;
		}

		$this->initContainer();
		$this->initialized = TRUE;

		// Do it only once, for db setup or repeat if not in transaction mode
		if (!self::$started) {
			$this->initDatabases();
			self::$started = TRUE;
		}
	}


	protected function initDatabases(): void
	{
		if (!$this->shouldCreateSchema()) {
			return;
		}

		foreach ($this->getInitializedConnections() as $connection) {
			$this->executeQueryWithForeignKeySetting(function() use ($connection): void {
				$schemaContent = $this->getSchemaContent($connection->getSchemaFile());
				$connection->createDatabaseSchema($schemaContent);
			}, $connection, $this->disableSchemaForeignKeyChecks);
		}
	}


	protected function getSchemaContent(string $path): string
	{
		if (!is_file($path)) {
			throw new \Exception("No sql schema file found {$path}");
		}

		return \Nette\Utils\FileSystem::read($path);
	}


	protected function beginTransactions(): void
	{
		$fixtureLoader = $this->getFixtureLoader();

		foreach ($this->getInitializedConnections() as $connection) {
			if ($this->isInTransactionMode) {
				$connection->beginTransaction();
			}

			$this->loadFixtures($fixtureLoader, $connection);
		}
	}


	protected function closeTransactions(): void
	{
		foreach ($this->getInitializedConnections() as $connection) {
			if (!$this->isInTransactionMode) {
				continue;
			}

			$connection->rollBack();
		}
	}


	private function createDatabaseTester(AbstractConnection $connection): \PHPUnit\DbUnit\DefaultTester
	{
		return new \PHPUnit\DbUnit\DefaultTester(
			$this->createDefaultDBConnection(
				$connection->getPdo()
			)
		);
	}


	/**
	 * Required by phpunit db testcase
	 */
	final public function getDataSet(): void
	{
		// TODO: Implement getDataSet() method.
	}


	final public function getConnection(?string $connectionName = NULL): AbstractConnection
	{
		if (!$connectionName) {
			return current($this->getInitializedConnections());
		}

		$connections = $this->getInitializedConnections();

		if (!isset($connections[$connectionName])) {
			throw new \Exception("Invalid connection name {$connectionName}");
		}

		return $connections[$connectionName];
	}


	protected function getFixtureLoader(): FixtureLoader
	{
		if (!$this->fixtureLoader) {
			return $this->fixtureLoader = new FixtureLoader;
		}

		return $this->fixtureLoader;
	}


	/**
	 * @return AbstractConnection[]|array
	 */
	protected function getInitializedConnections(): array
	{
		if (!$this->connections) {
			$this->connections = $this->getConnections();
		}

		return $this->connections;
	}


	private function shouldCreateSchema(): bool
	{
		return filter_var(getenv(self::ENV_CREATE_SCHEMA) ?: $this->forceCreateSchema, FILTER_VALIDATE_BOOLEAN) === TRUE;
	}


	protected function loadFixtures(FixtureLoader $fixtureLoader, AbstractConnection $connection): void
	{
		$this->executeQueryWithForeignKeySetting(function() use ($fixtureLoader, $connection): void {
			$fixtureSet = $fixtureLoader->load($connection, $this);

			// Create database tester & setup tasks
			$databaseTester = $this->createDatabaseTester($connection);
			$databaseTester->setSetUpOperation(\PHPUnit\DbUnit\Operation\Factory::INSERT());
			$databaseTester->setDataSet($fixtureSet);
			$databaseTester->onSetUp();
		}, $connection, $this->disableFixturesForeignKeyChecks);
	}


	protected function executeQueryWithForeignKeySetting(\Closure $closure, AbstractConnection $connection, bool $disableForeignKeyChecks = TRUE): void
	{
		if ($disableForeignKeyChecks) {
			$connection->disableForeignKeyChecks();
		}

		$closure();

		if ($disableForeignKeyChecks) {
			$connection->enableForeignKeyChecks();
		}
	}


	/**
	 * @param string[]|array $tableNames
	 */
	protected function cleanTables(array $tableNames): void
	{
		foreach ($this->getInitializedConnections() as $connection) {
			$connection->disableForeignKeyChecks();

			foreach ($tableNames as $table) {
				$connection->execute(sprintf('DELETE FROM %s WHERE id > 0', $table));
			}

			$connection->enableForeignKeyChecks();
		}
	}

}
