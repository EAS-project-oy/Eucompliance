<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 * PHP version 8
 *
 * @category Module
 * @package  Easproject_Eucompliance
 * @author   EAS Project <magento@easproject.org>
 * @license  https://github.com/EAS-project-oy/eascompliance/ General License
 * @link     https://github.com/EAS-project-oy/eascompliance
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Plugin;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Service\CartItem;
use Magento\Quote\Api\Data\CartItemExtensionFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\Repository;

class Item
{

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var CartItem
     */
    private CartItem $cartItemService;

    /**
     * @param CartItem $cartItemService
     * @param Configuration $configuration
     */
    public function __construct(
        CartItem $cartItemService,
        Configuration $configuration
    ) {
        $this->cartItemService = $cartItemService;
        $this->configuration = $configuration;
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
        if ($this->configuration->isEnabled() && !$this->configuration->isStandardSolution()) {
            $this->cartItemService->handleAttributes($cartItem);
        }
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
        if ($this->configuration->isEnabled() && !$this->configuration->isStandardSolution()) {
            foreach ($result as $item) {
                $this->cartItemService->handleAttributes($item, CartItem::SET);
            }
        }

        return $result;
    }
}
