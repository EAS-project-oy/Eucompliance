<?php

declare(strict_types=1);

namespace Easproject\Eucompliance\Plugin;

use Easproject\Eucompliance\Service\CartItem;
use Magento\Quote\Api\Data\CartItemExtensionFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\Repository;

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
     * @param  Repository        $subject
     * @param  CartItemInterface $cartItem
     * @return array
     */
    public function beforeSave(Repository $subject, CartItemInterface $cartItem): array
    {
        $this->cartItemService->handleAttributes($cartItem);
        return [$cartItem];
    }

    /**
     * @param  Repository $subject
     * @param  array      $result
     * @param  $cartId
     * @return array
     */
    public function afterGetList(Repository $subject, array $result, $cartId): array
    {
        foreach ($result as $item) {
            $this->cartItemService->handleAttributes($item, CartItem::SET);
        }

        return $result;
    }
}
