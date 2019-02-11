<?php

namespace Barabash\Checkout\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class InstallData implements InstallDataInterface
{
	const COLUMN_CUSTOMER_COMMENT = 'customer_comment';
	/**
	 * Customer setup factory
	 *
	 * @var \Magento\Customer\Setup\CustomerSetup
	 */
	private $customerSetupFactory;
	/**
	 * Init
	 *
	 * @param \Magento\Customer\Setup\CustomerSetup $customerSetupFactory
	 */
	public function __construct(\Magento\Customer\Setup\CustomerSetup $customerSetupFactory)
	{
		$this->customerSetupFactory = $customerSetupFactory;
	}
	/**
	 * Installs DB schema for a module
	 *
	 * @param ModuleDataSetupInterface $setup
	 * @param ModuleContextInterface $context
	 * @return void
	 */
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();

		$customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

		$customerSetup->addAttribute('customer_address', self::COLUMN_CUSTOMER_COMMENT, [
			'label' => 'Customer comment',
			'input' => 'text',
			'type' => 'varchar',
			'source' => '',
			'required' => false,
			'position' => 333,
			'visible' => true,
			'system' => false,
			'is_used_in_grid' => false,
			'is_visible_in_grid' => false,
			'is_filterable_in_grid' => false,
			'is_searchable_in_grid' => false,
			'backend' => ''
		]);


		$attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', self::COLUMN_CUSTOMER_COMMENT)
			->addData(['used_in_forms' => [
				'customer_address_edit',
				'customer_register_address',
				'checkout'
			]]);
		$attribute->save();

		$installer->getConnection()->addColumn(
			$installer->getTable('quote_address'),
			self::COLUMN_CUSTOMER_COMMENT,
			[
				'type' => 'varchar',
				'length' => 255
			]
		);

		$installer->getConnection()->addColumn(
			$installer->getTable('sales_order_address'),
			self::COLUMN_CUSTOMER_COMMENT,
			[
				'type' => 'varchar',
				'length' => 255
			]
		);

		$installer->endSetup();
	}
}