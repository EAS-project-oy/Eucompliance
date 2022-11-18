<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

namespace Easproject\Eucompliance\Plugin;

use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\ToOrderItem;

class SetOrderItemValue
{
    /**
     * Plugin Around Convert
     *
     * @param ToOrderItem $subject
     * @param callable $proceed
     * @param Item|AddressItem $quoteItem
     * @param array $data
     * @return mixed
     */
    public function aroundConvert(ToOrderItem $subject, callable $proceed, $quoteItem, $data)
    {
        $orderItem = $proceed($quoteItem, $data);
        $orderItem->setEasWarehouseCode($quoteItem->getExtensionAttributes()->getEasWarehouseCode());
        $orderItem->setEasCustomDuties($quoteItem->getExtensionAttributes()->getEasCustomDuties());
        $orderItem->setEasFee($quoteItem->getExtensionAttributes()->getEasFee());
        $orderItem->setVatOnEasFee($quoteItem->getExtensionAttributes()->getVatOnEasFee());
        return $orderItem;
    }
}
