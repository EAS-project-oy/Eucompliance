<?php

namespace Easproject\Eucompliance\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Session;

class QuoteCollect
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
     * @param Quote $subject
     * @param $result
     * @return mixed
     * @throws \Exception
     */
    public function afterCollectTotals(Quote $subject, $result)
    {
        if ($this->session->getData('custom_price_price')) {
            $subject->setData('subtotal', $this->session->getData('custom_price_price'));
            $subject->setData('base_subtotal', $this->session->getData('custom_price_price'));
            $subject->setData('subtotal_with_discount', $this->session->getData('custom_price_price'));
            $subject->setData('base_subtotal_with_discount', $this->session->getData('custom_price_price'));

            $shipping = $subject->getShippingAddress();
            $shipping->setData('subtotal', $this->session->getData('custom_price_price'));
            $shipping->setData('base_subtotal', $this->session->getData('custom_price_price'));
            $shipping->setData('subtotal_with_discount', $this->session->getData('custom_price_price'));
            $shipping->setData('base_subtotal_with_discount', $this->session->getData('custom_price_price'));
            $shipping->setData('subtotal_incl_tax', $this->session->getData('custom_price_price'));
            $shipping->setData('base_subtotal_total_incl_tax', $this->session->getData('custom_price_price'));
            $customDiscountPrice = $this->session->getData('custom_discount_price');
            $shipping->setData('discount_amount', '-' . $customDiscountPrice);
            $shipping->setData('base_discount_amount', '-' . $customDiscountPrice);
            $subject->setShippingAddress($shipping);
            $subject->save();
        }
        return $result;
    }
}
