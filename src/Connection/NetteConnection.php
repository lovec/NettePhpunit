<?php declare(strict_types = 1);

namespace HQ\Test\Connection;

use Nette\Database\Context;

class NetteConnection extends AbstractConnection
{
	/** @var Context */
	private $context;


	public function __construct(
		string $name,
		string $schemaFile,
		Context $context
	)
	{
		parent::__construct($name, $schemaFile);

		$this->context = $context;
	}


	public function getPdo(): \PDO
	{
		return $this->context->getConnection()->getPdo();
	}


	public function disconnect(): void
	{
		$this->context->getConnection()->disconnect();
	}

}
