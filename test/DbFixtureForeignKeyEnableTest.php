<?php declare(strict_types = 1);

namespace NettePhpunit\Test;

class DbFixtureForeignKeyEnableTest extends AbstractIntegrationTestCase
{
	/** @var bool */
	protected $disableFixturesForeignKeyChecks = FALSE;


	/**
	 * Override begin transaction to catch fixtures errors
	 */
	protected function beginTransactions(): void
	{
		try {
			parent::beginTransactions();
		} catch (\Exception $e) {
			$this->assertContains('Cannot add or update a child row:', $e->getMessage());
		}
	}


	/**
	 * @return mixed[]|array
	 */
	public function getFixtures(): array
	{
		return [
			'default' => [
				'product_order' => [
					// there's no such ids in both customer and product table
					['customer_id' => 20, 'product_id' => 100],
				],
			],
		];
	}


	/**
	 * Dummy test for activating fixtures loading process
	 */
	public function testShouldLoadFixturesWithoutForeignKeyChecks(): void
	{
	}

}
