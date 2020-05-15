<?php declare(strict_types = 1);

namespace NettePhpunit\Test;

class DbFixtureForeignKeyDisableTest extends AbstractIntegrationTestCase
{

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


	public function testShouldLoadFixturesWithoutForeignKeyChecks(): void
	{
		$connection = $this->getConnection();
		$rows = $connection->execute('SELECT customer_id, product_id FROM product_order')->fetchAll(\PDO::FETCH_ASSOC);

		$this->assertCount(1, $rows);
		$this->assertEquals($this->getFixtures()['default']['product_order'], $rows);
	}

}
