<?php

namespace Eas\Eucompliance\Plugin\Quote;

use Eas\Eucompliance\Service\CartItem;
use Magento\Framework\Model\AbstractModel;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Item
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
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item $subject
     * @param AbstractModel $cartItem
     * @return array
     */
    public function beforeSave(\Magento\Quote\Model\ResourceModel\Quote\Item $subject, AbstractModel $cartItem): array
    {
        $this->cartItemService->handleAttributes($cartItem);
        return [$cartItem];
    }
}
