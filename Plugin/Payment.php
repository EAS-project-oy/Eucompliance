<?php

namespace Easproject\Eucompliance\Plugin;

use Magento\Checkout\Model\Session;

class Payment
{

    private Session $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Payment $subject
     * @param $result
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
