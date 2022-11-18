<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

namespace Easproject\Eucompliance\Plugin;

use Magento\Checkout\Model\Session;

class Payment
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Plugin After Import Data
     *
     * @param \Magento\Quote\Model\Quote\Payment $subject
     * @param \Magento\Quote\Model\Quote\Payment $result
     * @param array $data
     * @return mixed
     */
    public function afterImportData(\Magento\Quote\Model\Quote\Payment $subject, $result, array $data)
    {
        //$this->session->getData('custom_shipping_price', true);
        //$this->session->getData('custom_price_price', true);
        //$this->session->getData('custom_discount_price', true);
        return $result;
    }
}
