<?php

namespace NettePhpunit\Test;

class DbFixtureForeignKeyEnableTest extends AbstractIntegrationTestCase
{
	protected $disableSchemaForeignKeyChecks = true;

	/**
	 * Check fixtures foreign keys
	 */
	protected $disableFixturesForeignKeyChecks = false;


	/**
	 * Override begin transaction to catch fixtures errors
	 */
	protected function beginTransactions()
	{
		try {
			parent::beginTransactions();
		} catch (\PHPUnit_Extensions_Database_Operation_Exception $e) {
			$this->assertContains('Cannot add or update a child row:', $e->getError());
		}
	}

	public function getFixtures()
	{
		return [
			'default' => [
				'product_order' => [
					// there's no such ids in both customer and product table
					['customer_id' => 20, 'product_id' => 100],
				]
			]
		];
	}

	/**
	 * Dummy test for activating fixtures loading process
	 */
	public function testShouldLoadFixturesWithoutForeignKeyChecks()
	{
	}
}