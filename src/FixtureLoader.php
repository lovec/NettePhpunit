<?php declare(strict_types = 1);

namespace HQ\Test;

use PHPUnit\DbUnit\DataSet;
use HQ\Test\Connection\AbstractConnection;

class FixtureLoader
{
	/**
	 * For constructing a fixture file name e.g. {connection-name}-fixtures.{ext}
	 *
	 * @var string
	 */
	protected $fixtureSuffix = '-fixtures';

	/** @var string[] */
	protected $fixturesExtensions = [
		'yaml' => 'loadFromYaml',
		'json' => 'loadFromJson',
		'php' => 'loadFromArray',
	];


	/**
	 * Fixture loading strategy
	 *
	 * 1. load base fixtures e.g. from AbstractDbTestCase.getBaseFixtureDir() + *-fixtures.{ext}
	 * 2. load class's fixtures e.g. all fixtures that relative to *Test.php
	 * 3. load instance's fixtures e.g. AbstractDbTestCase.getFixtures
	 */
	public function load(AbstractConnection $connection, AbstractDbTestCase $testCase): DataSet\CompositeDataSet
	{
		$dataSets = new DataSet\CompositeDataSet;

		// 1. load base fixture first
		$baseFixtureDir = $testCase->getBaseFixtureDir();
		$this->loadFixturesByPath($dataSets, $connection->getName(), $baseFixtureDir);

		// 2. load class fixtures
		$classPath = $this->getClassPath($testCase);
		$this->loadFixturesByPath($dataSets, $connection->getName(), $classPath);

		// 3. load instance fixtures
		$this->loadFixturesByClass($dataSets, $connection->getName(), $testCase);

		// Ensure we have at least one dataset
		$dataSets->addDataSet(new DataSet\ArrayDataSet([]));

		return $dataSets;
	}


	private function getClassPath(AbstractDbTestCase $testCase): string
	{
		$reflection = new \ReflectionClass(get_class($testCase));

		return dirname($reflection->getFileName());
	}


	public function loadFixturesByPath(DataSet\CompositeDataSet $dataSets, string $name, string $path): void
	{
		foreach ($this->fixturesExtensions as $extension => $loader) {
			$fixturePath = "{$path}/{$name}{$this->fixtureSuffix}.{$extension}";

			// Ignore if fixture not exists
			if (!is_file($fixturePath)) {
				continue;
			}

			// Add data set
			$dataSets->addDataSet(
				$this->$loader($fixturePath)
			);
		}
	}


	public function loadFixturesByClass(DataSet\CompositeDataSet $dataSets, string $name, AbstractDbTestCase $class): void
	{
		$getFixtureMethod = [$class, 'getFixtures'];
		if (!is_callable($getFixtureMethod)) {
			return;
		}

		$fixtures = $getFixtureMethod();
		if (!isset($fixtures[$name]) || !$fixtures[$name]) {
			return;
		}

		$dataSets->addDataSet(
			new DataSet\ArrayDataSet($fixtures[$name])
		);
	}


	public function loadFromArray(string $path): DataSet\ArrayDataSet
	{
		$fixtures = require $path;

		if (!is_array($fixtures)) {
			throw new \Exception("$path must return php array");
		}

		return new DataSet\ArrayDataSet($fixtures);
	}


	public function loadFromYaml(string $path): DataSet\YamlDataSet
	{
		return new DataSet\YamlDataSet($path);
	}


	public function loadFromJson(string $path): DataSet\ArrayDataSet
	{
		$content = \Nette\Utils\FileSystem::read($path);
		$fixtures = \Nette\Utils\Json::decode($content, \Nette\Utils\Json::FORCE_ARRAY);

		return new DataSet\ArrayDataSet($fixtures);
	}

}
