<?php

namespace Barabash\Checkout\Plugin\Block;

use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Ui\Component\Form\AttributeMapper;
use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Model\Session as CheckoutSession;

class LayoutProcessor
{
	/**
	 * @var AttributeMetadataDataProvider
	 */
	public $attributeMetadataDataProvider;
	/**
	 * @var AttributeMapper
	 */
	public $attributeMapper;
	/**
	 * @var AttributeMerger
	 */
	public $merger;
	/**
	 * @var CheckoutSession
	 */
	public $checkoutSession;
	/**
	 * @var null
	 */
	public $quote = null;
	/**
	 * LayoutProcessor constructor.
	 *
	 * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
	 * @param AttributeMapper $attributeMapper
	 * @param AttributeMerger $merger
	 * @param CheckoutSession $checkoutSession
	 */
	public function __construct(
		AttributeMetadataDataProvider $attributeMetadataDataProvider,
		AttributeMapper $attributeMapper,
		AttributeMerger $merger,
		CheckoutSession $checkoutSession
	) {
		$this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
		$this->attributeMapper = $attributeMapper;
		$this->merger = $merger;
		$this->checkoutSession = $checkoutSession;
	}

	public function process(array &$jsLayout)
	{
		$customAttributeCode = 'customer_comment';
		$customField = [
			'component' => 'Magento_Ui/js/form/element/abstract',
			'config' => [
				// customScope is used to group elements within a single form (e.g. they can be validated separately)
				'customScope' => 'shippingAddress.custom_attributes',
				'customEntry' => null,
				'template' => 'ui/form/field',
				'elementTmpl' => 'Barabash_Checkout/form/element/custom-textarea',
			],
			'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
			'label' => 'Comment Area',
			'provider' => 'checkoutProvider',
			'sortOrder' => 1000,
			/*'validation' => [
				'required-entry' => true
			],*/
			'options' => [],
			'filterBy' => null,
			'customEntry' => null,
			'visible' => true,
		];

		$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $customField;

		$customAttributeCode = 'comment_checkbox';
		$checkboxField = [
			'component' => 'Magento_Ui/js/form/element/abstract',
			'config' => [
				// customScope is used to group elements within a single form (e.g. they can be validated separately)
				'customScope' => 'shippingAddress.custom_attributes',
				'customEntry' => null,
				'template' => 'ui/form/field',
				'elementTmpl' => 'Barabash_Checkout/form/element/custom-checkbox',
			],
			'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
			'label' => 'Customer\'s Comment',
			'provider' => 'checkoutProvider',
			'sortOrder' => 999,
			'options' => [],
			'filterBy' => null,
			'customEntry' => null,
			'visible' => true,
		];
		$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $checkboxField;

	}
	/**
	 * Get Quote
	 *
	 * @return \Magento\Quote\Model\Quote|null
	 */
	public function getQuote()
	{
		if (null === $this->quote) {
			$this->quote = $this->checkoutSession->getQuote();
		}
		return $this->quote;
	}
	/**
	 * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
	 * @param array $jsLayout
	 * @return array
	 */
	public function aroundProcess(
		\Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
		\Closure $proceed,
		array $jsLayout
	) {
		$jsLayoutResult = $proceed($jsLayout);
		if($this->getQuote()->isVirtual()) {
			return $jsLayoutResult;
		}

		if(isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
			['shippingAddress']['children']['shipping-address-fieldset'])) {
			$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
			['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][0]['placeholder'] = __('Street Address');
			$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
			['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][1]['placeholder'] = __('Street line 2');
			$elements = $this->getAddressAttributes();
			$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
			['children']['shippingAddress']['children']['billing-address'] = $this->getCustomBillingAddressComponent($elements);
			$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
			['children']['shippingAddress']['children']['billing-address']['children']['form-fields']['children']['street']['children'][0]['placeholder'] = __('Street Address');
			$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
			['children']['shippingAddress']['children']['billing-address']['children']['form-fields']['children']['street']['children'][1]['placeholder'] = __('Street line 2');
		}

		$this->process($jsLayoutResult);

		return $jsLayoutResult;
	}

	public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, array $jsLayout)
	{
		unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
			['children']['payment']['children']['payments-list']['children']);

		return $jsLayout;
	}

		/**
	 * Get all visible address attribute
	 *
	 * @return array
	 */
	private function getAddressAttributes()
	{
		/** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
		$attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
			'customer_address',
			'customer_register_address'
		);
		$elements = [];
		foreach ($attributes as $attribute) {
			$code = $attribute->getAttributeCode();
			if ($attribute->getIsUserDefined()) {
				continue;
			}
			$elements[$code] = $this->attributeMapper->map($attribute);
			if (isset($elements[$code]['label'])) {
				$label = $elements[$code]['label'];
				$elements[$code]['label'] = __($label);
			}
		}
		return $elements;
	}
	/**
	 * Prepare billing address field for shipping step for physical product
	 *
	 * @param $elements
	 * @return array
	 */
	public function getCustomBillingAddressComponent($elements)
	{
		return [
			'component' => 'Barabash_Checkout/js/view/billing-address',
			'displayArea' => 'billing-address',
			'provider' => 'checkoutProvider',
			'deps' => ['checkoutProvider'],
			'dataScopePrefix' => 'billingAddress',
			'children' => [
				'form-fields' => [
					'component' => 'uiComponent',
					'displayArea' => 'additional-fieldsets',
					'children' => $this->merger->merge(
						$elements,
						'checkoutProvider',
						'billingAddress',
						[
							'country_id' => [
								'sortOrder' => 115,
							],
							'region' => [
								'visible' => false,
							],
							'region_id' => [
								'component' => 'Magento_Ui/js/form/element/region',
								'config' => [
									'template' => 'ui/form/field',
									'elementTmpl' => 'ui/form/element/select',
									'customEntry' => 'billingAddress.region',
								],
								'validation' => [
									'required-entry' => true,
								],
								'filterBy' => [
									'target' => '${ $.provider }:${ $.parentScope }.country_id',
									'field' => 'country_id',
								],
							],
							'postcode' => [
								'component' => 'Magento_Ui/js/form/element/post-code',
								'validation' => [
									'required-entry' => true,
								],
							],
							'company' => [
								'validation' => [
									'min_text_length' => 0,
								],
							],
							'fax' => [
								'validation' => [
									'min_text_length' => 0,
								],
							],
							'telephone' => [
								'config' => [
									'tooltip' => [
										'description' => __('For delivery questions11111.'),
									],
								],
							],
						]
					),
				],
			],
		];
	}
}