<?php

namespace HQ\Test\Connection;

use Nette\Database\Context;

class NetteConnection extends AbstractConnection
{
	/**
	 * @var Context
	 */
	private $context;

	/**
	 * Connection constructor.
	 *
	 * @param $name
	 * @param $schemaFile
	 * @param Context $context
	 */
	public function __construct(
		$name,
		$schemaFile,
		Context $context
	)
	{
		$this->name = $name;
		$this->schemaFile = $schemaFile;
		$this->context = $context;
	}

	public function getPdo()
	{
		return $this->context->getConnection()->getPdo();
	}

    public function disconnect()
    {
        $this->context->getConnection()->disconnect();
    }
}