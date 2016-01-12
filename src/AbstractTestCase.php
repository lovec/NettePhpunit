<?php

namespace HQ\Test;

use Nette\DI\Container;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Container
	 */
	protected $container;


	/**
	 * @return \Nette\Di\Container
	 */
	abstract protected function getContainer();

	protected function initContainer()
	{
		$this->container = $this->getContainer();

		if (!$this->container instanceof Container) {
			throw new \Exception('$containerPath must return an instance of ' . Container::class);
		}
	}

	protected function setUp()
	{
		$this->beforeSetup();
		$this->afterSetup();
	}

	protected function beforeSetup()
	{
		$this->initContainer();
	}

	protected function afterSetup()
	{
	}

	protected function tearDown()
	{
		$this->beforeTearDown();
		$this->afterTearDown();
	}

	protected function beforeTearDown()
	{
	}

	protected function afterTearDown()
	{
	}
}