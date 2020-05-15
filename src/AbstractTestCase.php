<?php declare(strict_types = 1);

namespace HQ\Test;

use Nette\DI\Container;

abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
	/** @var Container */
	protected $container;


	abstract protected function getContainer(): \Nette\DI\Container;


	protected function initContainer(): void
	{
		$this->container = $this->getContainer();

		if (!$this->container instanceof Container) {
			throw new \Exception(sprintf('%s::getContainer() must return an instance of %s', static::class, Container::class));
		}
	}


	protected function setUp(): void
	{
		$this->beforeSetup();
		$this->afterSetup();
	}


	protected function beforeSetup(): void
	{
		$this->initContainer();
	}


	protected function afterSetup(): void
	{
	}


	protected function tearDown(): void
	{
		$this->beforeTearDown();
		$this->afterTearDown();
	}


	protected function beforeTearDown(): void
	{
	}


	protected function afterTearDown(): void
	{
	}

}
