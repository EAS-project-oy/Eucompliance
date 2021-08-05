<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface as QuoteTotalsInterface;
use Magento\Quote\Model\Cart\CartTotalRepository;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class CartTotal
{

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * CartTotal constructor.
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param CartTotalRepository $subject
     * @param QuoteTotalsInterface $result
     * @param $cartId
     * @return QuoteTotalsInterface
     * @throws NoSuchEntityException
     */
    public function afterGet(CartTotalRepository $subject, QuoteTotalsInterface $result, $cartId): QuoteTotalsInterface
    {
        if ($result->getTotalSegments()['grand_total']->getValue()) {
            $result->getTotalSegments()['grand_total']->setValue($this->cartRepository->get($cartId)->getGrandTotal());
        }
        return $result;
    }
}
