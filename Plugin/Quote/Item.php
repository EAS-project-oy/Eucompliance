<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

namespace Easproject\Eucompliance\Plugin\Quote;

use Easproject\Eucompliance\Service\CartItem;
use Magento\Framework\Model\AbstractModel;

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
     * Before Save Plugin
     *
     * @param  \Magento\Quote\Model\ResourceModel\Quote\Item $subject
     * @param  AbstractModel                                 $cartItem
     * @return array
     */
    public function beforeSave(\Magento\Quote\Model\ResourceModel\Quote\Item $subject, AbstractModel $cartItem): array
    {
        $this->cartItemService->handleAttributes($cartItem);
        return [$cartItem];
    }
}
