<?php

namespace HQ\Test;

use PHPUnit_Extensions_Database_TestCase_Trait;
use HQ\Test\Connection\AbstractConnection;
use HQ\Test\FixtureLoader;

abstract class AbstractDbTestCase extends AbstractTestCase
{
	use PHPUnit_Extensions_Database_TestCase_Trait;

	const ENV_CREATE_SCHEMA = 'UNITTEST_CREATE_SCHEMA';

	protected $initialized;
	protected static $started;

	/**
	 * @var FixtureLoader
	 */
	protected $fixtureLoader;

	/**
	 * @var AbstractConnection[]
	 */
	protected $connections;

	/**
	 * @var \PHPUnit_Extensions_Database_ITester[]
	 */
	private $databaseTesters = [];

	/**
	 * This will improve test speed when we execute it later
	 *
	 * @var bool
	 */
	protected $forceCreateSchema = true;

	/**
	 * @return AbstractConnection[]
	 */
	abstract protected function getConnections();
	abstract public function getBaseFixtureDir();

	/**
	 * Return fixtures array
	 *
	 * @return array
	 */
	public function getFixtures()
	{
		return [];
	}

	public function setUp()
	{
		$this->init();

		$this->beforeSetup();
		$this->beginTransactions();
		$this->afterSetup();
	}

	public function tearDown()
	{
		$this->beforeTearDown();
		$this->closeTransactions();
		$this->afterTearDown();

		$this->databaseTesters = null;
	}

	protected function init()
	{
		if ($this->initialized) {
			return;
		}

		$this->initContainer();
		$this->initialized = true;

		// do it only once, for db setup
		if (!self::$started) {
			$this->initDatabases();
			self::$started = true;
		}
	}

	protected function initDatabases()
	{
		if (!$this->shouldCreateSchema()) {
			return;
		}

		foreach($this->getInitializedConnections() as $connection) {

			// build schema
			$schemaContent = $this->getSchemaContent($connection->getSchemaFile());
			$connection->createDatabaseSchema($schemaContent);
		}
	}

	protected function getSchemaContent($path)
	{
		if (!is_file($path)) {
			throw new \Exception("No sql schema file found {$path}");
		}

		return file_get_contents($path);
	}

	protected function beginTransactions()
	{
		$fixtureLoader = $this->getFixtureLoader();
		foreach($this->getInitializedConnections() as $connection) {
			$connection->beginTransaction();

			$fixtureSet = $fixtureLoader->load($connection, $this);

			// create database tester & setup tasks
			$databaseTester = $this->createDatabaseTester($connection);
			$databaseTester->setSetUpOperation($this->getSetUpOperation());
			$databaseTester->setDataSet($fixtureSet);
			$databaseTester->onSetUp();
		}
	}

	protected function closeTransactions()
	{
		foreach ($this->getInitializedConnections() as $connection) {
			$connection->rollBack();
		}
	}

	private function createDatabaseTester(AbstractConnection $connection)
	{
		return new \PHPUnit_Extensions_Database_DefaultTester(
			$this->createDefaultDBConnection(
				$connection->getPdo()
			)
		);
	}

	/**
	 * Required by phpunit db testcase
	 */
	final public function getDataSet()
	{
		// TODO: Implement getDataSet() method.
	}

	/**
	 * Required by PHPUnit DB TestCase
	 *
	 * @param null $connectionName
	 * @return AbstractConnection
	 * @throws \Exception
	 */
	final public function getConnection($connectionName = null)
	{
		if (!$connectionName) {
			return current($this->getInitializedConnections());
		}

		$connections = $this->getInitializedConnections();
		if (empty($connections[$connectionName])) {
			throw new \Exception("Invalid connection name {$connectionName}");
		}

		return $connections[$connectionName];
	}

	protected function getFixtureLoader()
	{
		if ($this->fixtureLoader) {
			$this->fixtureLoader;
		}

		return $this->fixtureLoader = new FixtureLoader();
	}

	protected function getInitializedConnections()
	{
		if ($this->connections) {
			return $this->connections;
		}

		return $this->connections = $this->getConnections();
	}

	private function shouldCreateSchema()
	{
		return filter_var(getenv(self::ENV_CREATE_SCHEMA) ?: $this->forceCreateSchema, FILTER_VALIDATE_BOOLEAN) === true;
	}
}