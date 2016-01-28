<?php

namespace NettePhpunit\Test;

class DbFixtureForeignKeyDisableTest extends AbstractIntegrationTestCase
{
	/**
	 * These are default values (true)
	 */
	protected $disableSchemaForeignKeyChecks = true;
	protected $disableFixturesForeignKeyChecks = true;

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

	public function testShouldLoadFixturesWithoutForeignKeyChecks()
	{
		$con = $this->getConnection();
		$rows = $con->execute('SELECT customer_id, product_id FROM product_order')->fetchAll(\PDO::FETCH_ASSOC);

		// should return result
		$this->assertSame(1, sizeof($rows));
		$this->assertEquals($this->getFixtures()['default']['product_order'], $rows);
	}
}