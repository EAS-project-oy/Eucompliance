<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Plugin;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class SaveGuestCartData
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * SaveGuestCartData constructor.
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeSaveAddressInformation(ShippingInformationManagement $subject, int $cartId, ShippingInformationInterface $addressInformation): array
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $shipping = $addressInformation->getShippingAddress();
        $quote->setCustomerFirstname($shipping->getFirstname());
        $quote->setCustomerLastname($shipping->getLastname());
        $quote->setCustomerPrefix($shipping->getPrefix());
        return [$cartId, $addressInformation];
    }
}
