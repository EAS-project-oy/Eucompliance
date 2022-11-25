<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 * PHP version 8
 *
 * @category Module
 * @package  Easproject_Eucompliance
 * @author   EAS Project <magento@easproject.org>
 * @license  https://github.com/EAS-project-oy/eascompliance/ General License
 * @link     https://github.com/EAS-project-oy/eascompliance
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Plugin;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;

class SaveGuestCartData
{
    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var QuoteRepository
     */
    private QuoteRepository $quoteRepository;

    /**
     * SaveGuestCartData constructor.
     *
     * @param QuoteRepository $quoteRepository
     * @param Configuration $configuration
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Configuration $configuration
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->configuration = $configuration;
    }

    /**
     * Plugin Before Save Address Information
     *
     * @param  ShippingInformationManagement $subject
     * @param  int                           $cartId
     * @param  ShippingInformationInterface  $addressInformation
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        int                           $cartId,
        ShippingInformationInterface  $addressInformation
    ): array {
        if ($this->configuration->isEnabled()) {
            $quote = $this->quoteRepository->getActive($cartId);
            $shipping = $addressInformation->getShippingAddress();
            $quote->setCustomerFirstname($shipping->getFirstname());
            $quote->setCustomerLastname($shipping->getLastname());
            $quote->setCustomerPrefix($shipping->getPrefix());
        }
        return [$cartId, $addressInformation];
    }
}
