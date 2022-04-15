<?php

namespace Eas\Eucompliance\Ui\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdSkuAndQty;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class SourceSelectionDataProvider extends \Magento\InventoryShippingAdminUi\Ui\DataProvider\SourceSelectionDataProvider
{

    /**
     * @var GetSourcesByOrderIdSkuAndQty|null
     */
    private GetSourcesByOrderIdSkuAndQty $getSourcesByOrderIdSkuAndQty;

    /**
     * @var array
     */
    private array $sources = [];

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver;

    /**
     * @var GetSkuFromOrderItemInterface
     */
    private GetSkuFromOrderItemInterface $getSkuFromOrderItem;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private GetStockItemConfigurationInterface $getStockItemConfiguration;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param $getSourcesByStockIdSkuAndQty
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param GetSourcesByOrderIdSkuAndQty|null $getSourcesByOrderIdSkuAndQty
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string                             $name,
        string                             $primaryFieldName,
        string                             $requestFieldName,
        RequestInterface                   $request,
        OrderRepositoryInterface           $orderRepository,
        StockByWebsiteIdResolverInterface  $stockByWebsiteIdResolver,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
                                           $getSourcesByStockIdSkuAndQty,
        GetSkuFromOrderItemInterface       $getSkuFromOrderItem,
        GetSourcesByOrderIdSkuAndQty       $getSourcesByOrderIdSkuAndQty,
        array                              $meta = [],
        array                              $data = []
    ) {
        $this->request = $request;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getSourcesByOrderIdSkuAndQty = $getSourcesByOrderIdSkuAndQty;
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->orderRepository = $orderRepository;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $request,
            $orderRepository,
            $stockByWebsiteIdResolver,
            $getStockItemConfiguration,
            $getSourcesByStockIdSkuAndQty,
            $getSkuFromOrderItem,
            $getSourcesByOrderIdSkuAndQty,
            $meta,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $data = [];
        $orderId = (int)$this->request->getParam('order_id');
        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();

        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getIsVirtual()
                || $orderItem->getLockedDoShip()
                || $orderItem->getHasChildren()) {
                continue;
            }

            $item = $orderItem->isDummy(true) ? $orderItem->getParentItem() : $orderItem;
            $sku = $this->getSkuFromOrderItem->execute($item);
            if ($item->getQtyToShip() > 0) {
                $sourceCode = $orderItem->getEasWarehouseCode();
            }
            $qty = $item->getSimpleQtyToShip();
            $qty = $this->castQty($item, $qty);
            $data[$orderId]['items'][] = [
                'orderItemId' => $item->getId(),
                'sku' => $sku,
                'product' => $this->getProductName($orderItem),
                'qtyToShip' => $qty,
                'sources' => $this->getSources($orderId, $sku, $qty),
                'isManageStock' => $this->isManageStock($sku, $stockId)
            ];
        }
        $data[$orderId]['websiteId'] = $websiteId;
        $data[$orderId]['order_id'] = $orderId;
        foreach ($this->sources as $code => $name) {
            if (isset($sourceCode)) {
                if ($code == $sourceCode) {
                    $data[$orderId]['sourceCodes'][] = [
                        'value' => $code,
                        'label' => $name
                    ];
                }
            } else {
                $data[$orderId]['sourceCodes'][] = [
                    'value' => $code,
                    'label' => $name
                ];
            }
        }

        return $data;
    }

    /**
     * Get sources
     *
     * @param int $orderId
     * @param string $sku
     * @param float $qty
     * @return array
     * @throws NoSuchEntityException
     */
    private function getSources(int $orderId, string $sku, float $qty): array
    {
        $sources = $this->getSourcesByOrderIdSkuAndQty->execute($orderId, $sku, $qty);
        foreach ($sources as $source) {
            $this->sources[$source['sourceCode']] = $source['sourceName'];
        }
        return $sources;
    }

    /**
     * @param Item $item
     * @param string|int|float $qty
     * @return float|int
     */
    private function castQty(Item $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }

    /**
     * Generate display product name
     * @param Item $item
     * @return null|string
     */
    private function getProductName(Item $item)
    {
        $name = $item->getName();
        if ($parentItem = $item->getParentItem()) {
            $name = $parentItem->getName();
            $options = [];
            if ($productOptions = $parentItem->getProductOptions()) {
                if (isset($productOptions['options'])) {
                    $options = array_merge($options, $productOptions['options']);
                }
                if (isset($productOptions['additional_options'])) {
                    $options = array_merge($options, $productOptions['additional_options']);
                }
                if (isset($productOptions['attributes_info'])) {
                    $options = array_merge($options, $productOptions['attributes_info']);
                }
                if (count($options)) {
                    foreach ($options as $option) {
                        $name .= '<dd>' . $option['label'] . ': ' . $option['value'] . '</dd>';
                    }
                } else {
                    $name .= '<dd>' . $item->getName() . '</dd>';
                }
            }
        }

        return $name;
    }

    /**
     * @param $itemSku
     * @param $stockId
     * @return bool
     * @throws LocalizedException
     */
    private function isManageStock($itemSku, $stockId)
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($itemSku, $stockId);

        return $stockItemConfiguration->isManageStock();
    }
}
