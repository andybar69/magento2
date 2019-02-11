<?php

namespace Barabash\Checkout\Observer;

use Magento\Customer\Model\Address\AbstractAddress as Address;


class ProcessOrder implements \Magento\Framework\Event\ObserverInterface
{
	protected $orderRepository;

	public function __construct(\Magento\Sales\Model\Order\AddressRepository $orderAddressRepository)
	{
		$this->orderRepository = $orderAddressRepository;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$quote = $observer->getQuote();
		$quoteAddresses = $quote->getAddressesCollection();
		$comment = '';
		foreach ($quoteAddresses as $quoteAddress) {
			if (Address::TYPE_SHIPPING === $quoteAddress->getAddressType()) {
				$comment = $quoteAddress->getCustomerComment();
			}
		}
		if (strlen($comment) > 0) {
			$order = $observer->getOrder();
			$addresses = $order->getAddressesCollection();
			foreach ($addresses as $address) {
				if (Address::TYPE_SHIPPING === $address->getAddressType()) {
					$address->setCustomerComment($comment);
					$this->orderRepository->save($address);
				}
			}
		}

		return $this;
	}
}