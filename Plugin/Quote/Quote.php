<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

namespace Easproject\Eucompliance\Plugin\Quote;

use Easproject\Eucompliance\Service\CartItem;
use Magento\Quote\Model\Quote\Item;

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
     * Plugin After Get All Visible Items
     *
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
