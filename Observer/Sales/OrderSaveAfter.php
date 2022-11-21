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

namespace Easproject\Eucompliance\Observer\Sales;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        $this->checkoutSession->getData('original_shipping_price', true);
        $this->checkoutSession->getData('custom_price_price', true);
        $this->checkoutSession->getData('custom_shipping_price', true);
        $this->checkoutSession->getData('custom_discount_price', true);
    }
}
