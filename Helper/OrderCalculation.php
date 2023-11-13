<?php

namespace Easproject\Eucompliance\Helper;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Setup\Patch\Data\AddGiftCardProductAttribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterfaceFactory;

class OrderCalculation
{

    /** @var Configuration  */
    protected Configuration $configuration;

    /** @var SourceRepositoryInterface  */
    protected SourceRepositoryInterface $sourceRepository;

    /** @var SourceSelectionServiceInterface  */
    protected SourceSelectionServiceInterface $sourceSelectionService;

    /** @var StoreManagerInterface  */
    protected StoreManagerInterface $storeManager;

    /** @var StockByWebsiteIdResolverInterface  */
    protected StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver;

    /** @var ItemRequestInterfaceFactory  */
    protected ItemRequestInterfaceFactory $itemRequestInterfaceFactory;

    /** @var InventoryRequestInterfaceFactory  */
    protected InventoryRequestInterfaceFactory $inventoryRequestInterfaceFactory;

    /** @var AddressInterfaceFactory  */
    protected AddressInterfaceFactory $addressInterfaceFactory;

    /** @var InventoryRequestExtensionInterfaceFactory  */
    protected InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory;

    public function __construct(
        Configuration $configuration,
        SourceRepositoryInterface $sourceRepository,
        SourceSelectionServiceInterface $sourceSelectionService,
        StoreManagerInterface $storeManager,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        ItemRequestInterfaceFactory $itemRequestInterfaceFactory,
        InventoryRequestInterfaceFactory $inventoryRequestInterfaceFactory,
        AddressInterfaceFactory $addressInterfaceFactory,
        InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
    )
    {
        $this->configuration = $configuration;
        $this->sourceRepository = $sourceRepository;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->storeManager = $storeManager;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->itemRequestInterfaceFactory = $itemRequestInterfaceFactory;
        $this->inventoryRequestInterfaceFactory = $inventoryRequestInterfaceFactory;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->inventoryRequestExtensionInterfaceFactory = $inventoryRequestExtensionInterfaceFactory;
    }

    /**
     * Get Type Of Goods
     *
     * @param ProductInterface $product
     * @return string
     */
    public function getTypeOfGoods(ProductInterface $product): string
    {
        $result = Configuration::GOODS;
        if ($product->getTypeId() == Configuration::VIRTUAL) {
            $result = Configuration::TBE;
        }
        if ($product->getData(AddGiftCardProductAttribute::EAS_GIFT_CARD)) {
            $result = Configuration::GIFTCARD;
        }
        return $result;
    }

    /**
     * Get Address From Quote
     *
     * @param Order $order
     * @return AddressInterface|null
     */
    private function getAddressFromOrder(Order $order): ?AddressInterface
    {
        $address = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();
        if ($address === null) {
            return null;
        }

        return $this->addressInterfaceFactory->create(
            [
                'country' => $address->getCountryId(),
                'postcode' => $address->getPostcode() ?? '',
                'street' => implode(PHP_EOL, $address->getStreet()),
                'region' => $address->getRegion() ?? $address->getRegionCode() ?? '',
                'city' => $address->getCity() ?? ''
            ]
        );
    }

    /**
     * Get Inventory Request From Order
     *
     * @param Order $order
     * @param ProductInterface $product
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function getInventoryRequestFromOrder(Order $order, ProductInterface $product)
    {
        $store = $this->storeManager->getStore($order->getStoreId());
        $stock = $this->stockByWebsiteIdResolver->execute((int)$store->getWebsiteId());
        $requestItems = [];

        /** @var Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getSku() == $product->getSku()) {
                $requestItems[] = $this->itemRequestInterfaceFactory->create(
                    [
                        'sku' => $item->getSku(),
                        'qty' => $item->getQtyOrdered()
                    ]
                );
            }
        }
        $inventoryRequest = $this->inventoryRequestInterfaceFactory->create(
            [
                'stockId' => $stock->getStockId(),
                'items' => $requestItems
            ]
        );

        $address = $this->getAddressFromOrder($order);
        if ($address !== null) {
            $extensionAttributes = $this->inventoryRequestExtensionInterfaceFactory->create();
            $extensionAttributes->setDestinationAddress($address);
            $inventoryRequest->setExtensionAttributes($extensionAttributes);
        }

        return $inventoryRequest;
    }

    /**
     * Get Warehouse Code
     *
     * @param Order $order
     * @param ProductInterface $product
     * @return array|bool|string|null
     * @throws NoSuchEntityException
     */
    private function getWarehouseCode(Order $order, ProductInterface $product)
    {
        if ($this->configuration->getMSIWarehouseLocation()) {
            $request = $this->getInventoryRequestFromOrder($order, $product);
            $sourceSelectionItems = $this->sourceSelectionService->execute(
                $request,
                $this->configuration->getMSIWarehouseLocation()
            )->getSourceSelectionItems();
            return $sourceSelectionItems[array_key_first($sourceSelectionItems)]->getSourceCode();
        }
        return $order->getData($this->configuration->getWarehouseAttributeName()) ?:
            $this->configuration->getStoreDefaultCountryCode();
    }

    /**
     * Get Location Warehouse
     *
     * @param Order $order
     * @param ProductInterface $product
     * @return array|bool|string|null
     * @throws NoSuchEntityException
     */
    public function getLocationWarehouse(Order $order, ProductInterface $product)
    {
        if ($this->configuration->getMSIWarehouseLocation()) {
            $sourceCode = $this->getWarehouseCode($order, $product);
            return $this->sourceRepository->get($sourceCode)->getCountryId();
        }

        return $product->getData($this->configuration->getWarehouseAttributeName()) ?:
            $this->configuration->getStoreDefaultCountryCode();
    }

    /**
     * @param Order $order
     * @return string
     */
    public function getDeliveryMethod(Order $order)
    {
        $address = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();

        $deliveryMethod = Configuration::COURIER;

        if ($this->configuration->getPostalMethods()) {
            foreach (explode(',', $this->configuration->getPostalMethods()) as $postalMethod) {
                if ($address && $address->getShippingMethod() == $postalMethod) {
                    $deliveryMethod = Configuration::POSTAL;
                }
            }
        }

        if ($order->getIsVirtual()) {
            $deliveryMethod = Configuration::POSTAL;
        }
        return $deliveryMethod;
    }
}
