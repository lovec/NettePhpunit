<?php

namespace HQ\Test;

use PHPUnit\DbUnit\DataSet;
use HQ\Test\Connection\AbstractConnection;

class FixtureLoader
{
	/**
	 * For constructing a fixture file name e.g. {connection-name}-fixtures.{ext}
	 * @var string
	 */
	protected $fixtureSuffix = '-fixtures';
	protected $fixturesExtensions = [
		'yaml' => 'loadFromYaml',
		'json' => 'loadFromJson',
		'php'  => 'loadFromArray',
	];

	/**
	 * Fixture loading strategy
	 *
	 * 1. load base fixtures e.g. from AbstractDbTestCase.getBaseFixtureDir() + *-fixtures.{ext}
	 * 2. load class's fixtures e.g. all fixtures that relative to *Test.php
	 * 3. load instance's fixtures e.g. AbstractDbTestCase.getFixtures
	 *
	 * @param AbstractConnection $connection
	 * @param AbstractDbTestCase $testCase
	 * @return DataSet\CompositeDataSet
	 */
	public function load(AbstractConnection $connection, AbstractDbTestCase $testCase)
	{
		$dataSets = new DataSet\CompositeDataSet();

		// 1. load base fixture first
		$baseFixtureDir = $testCase->getBaseFixtureDir();
		$this->loadFixturesByPath($dataSets, $connection->getName(), $baseFixtureDir);

		// 2. load class fixtures
		$classPath = $this->getClassPath($testCase);
		$this->loadFixturesByPath($dataSets, $connection->getName(), $classPath);

		// 3. load instance fixtures
		$this->loadFixturesByClass($dataSets, $connection->getName(), $testCase);

		// ensure we have at least one dataset
		$dataSets->addDataSet(new DataSet\ArrayDataSet([]));

		return $dataSets;
	}

	private function getClassPath(AbstractDbTestCase $testCase)
	{
		$reflection   = new \ReflectionClass(get_class($testCase));
		$fixturesPath = dirname($reflection->getFileName());

		return $fixturesPath;
	}

	public function loadFixturesByPath(DataSet\CompositeDataSet $dataSets, $name, $path)
	{
		foreach($this->fixturesExtensions as $extension => $loader) {
			$fixturePath = "{$path}/{$name}{$this->fixtureSuffix}.{$extension}";

			// ignore if fixture not exists
			if (!is_file($fixturePath)) {
				continue;
			}

			// add data set
			$dataSets->addDataSet(
				call_user_func([$this, $loader], $fixturePath)
			);
		}
	}

	public function loadFixturesByClass(DataSet\CompositeDataSet $dataSets, $name, AbstractDbTestCase $class)
	{
		$getFixtureMethod = [$class, 'getFixtures'];
		if (!is_callable($getFixtureMethod)) {
			return;
		}

		$fixtures = call_user_func($getFixtureMethod);
		if (empty($fixtures[$name])) {
			return;
		}

		$dataSets->addDataSet(
			new DataSet\ArrayDataSet($fixtures[$name])
		);
	}

	public function loadFromArray($path)
	{
		$fixtures = require($path);
		if (!is_array($fixtures)) {
			throw new \Exception("$path must return php array");
		}

		return new DataSet\ArrayDataSet($fixtures);
	}

	public function loadFromYaml($path)
	{
		return new DataSet\YamlDataSet($path);
	}

	public function loadFromJson($path)
	{
		$fixtures = json_decode(file_get_contents($path), true);
		return new DataSet\ArrayDataSet($fixtures);
	}
}
