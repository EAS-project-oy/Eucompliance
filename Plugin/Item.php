<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Plugin;

use Easproject\Eucompliance\Service\CartItem;
use Magento\Quote\Api\Data\CartItemExtensionFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\Repository;

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
     * Before save plugin
     *
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
     * Plugin After Get List
     *
     * @param Repository $subject
     * @param array $result
     * @param int $cartId
     * @return array
     */
    public function afterGetList(Repository $subject, array $result, int $cartId): array
    {
        foreach ($result as $item) {
            $this->cartItemService->handleAttributes($item, CartItem::SET);
        }

        return $result;
    }
}
