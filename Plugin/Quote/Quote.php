<?php

namespace Easproject\Eucompliance\Plugin\Quote;

use Easproject\Eucompliance\Service\CartItem;
use Magento\Quote\Model\Quote\Item;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Quote
{
    /**
     * @var CartItem
     */
    private CartItem $cartItemService;

    /**
     * @param CartItem $cartItemService
     */
    public function __construct(CartItem $cartItemService)
    {
        $this->cartItemService = $cartItemService;
    }

    /**
     * @param  \Magento\Quote\Model\Quote $subject
     * @param  Item[]                     $result
     * @return Item[]
     */
    public function afterGetAllVisibleItems(\Magento\Quote\Model\Quote $subject, array $result): array
    {
        foreach ($result as $item) {
            $this->cartItemService->handleAttributes($item, CartItem::SET);
        }
        return $result;
    }
}
