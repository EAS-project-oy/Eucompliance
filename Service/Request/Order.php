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

namespace Easproject\Eucompliance\Service\Request;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Service\Calculate;
use Easproject\Eucompliance\Service\Order\Collection;
use Easproject\Eucompliance\Setup\Patch\Data\AddGiftCardProductAttribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order\Item;
use Zend_Http_Client;

class Order
{
    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var ZendClientFactory
     */
    private ZendClientFactory $clientFactory;

    /**
     * @var Calculate
     */
    private Calculate $calculate;

    /**
     * @var Collection
     */
    private Collection $serviceCollection;

    /**
     * @var Product
     */
    private Product $productResourceModel;

    /**
     * @var SourceRepositoryInterface
     */
    private SourceRepositoryInterface $sourceRepository;

    /**
     * @var SourceSelectionServiceInterface
     */
    private SourceSelectionServiceInterface $sourceSelectionService;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private StockByWebsiteIdResolverInterface $stockByWebsiteId;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private ItemRequestInterfaceFactory $itemRequestInterfaceFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private InventoryRequestInterfaceFactory $inventoryRequestInterfaceFactory;

    /**
     * @var AddressInterfaceFactory
     */
    private AddressInterfaceFactory $addressInterfaceFactory;

    /**
     * @var InventoryRequestExtensionInterfaceFactory
     */
    private InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var File
     */
    private File $file;

    /**
     * @var DriverInterface
     */
    private DriverInterface $fileSystemDriver;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private \Psr\Log\LoggerInterface $logger;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private \Magento\Sales\Model\Order $orderModel;

    /**
     * @var array
     */
    private array $massOrdersData = [];

    /**
     * @param Configuration $configuration
     * @param ZendClientFactory $clientFactory
     * @param Calculate $calculate
     * @param Collection $serviceCollection
     * @param Product $productResourceModel
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteId
     * @param ItemRequestInterfaceFactory $itemRequestInterfaceFactory
     * @param InventoryRequestInterfaceFactory $inventoryRequestInterfaceFactory
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
     * @param Filesystem $filesystem
     * @param File $file
     * @param DriverInterface $fileSystemDriver
     * @param CartRepositoryInterface $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Order $orderModel
     */
    public function __construct(
        Configuration                             $configuration,
        ZendClientFactory                         $clientFactory,
        Calculate                                 $calculate,
        Collection                                $serviceCollection,
        Product                                   $productResourceModel,
        SourceRepositoryInterface                 $sourceRepository,
        SourceSelectionServiceInterface           $sourceSelectionService,
        StockByWebsiteIdResolverInterface         $stockByWebsiteId,
        ItemRequestInterfaceFactory               $itemRequestInterfaceFactory,
        InventoryRequestInterfaceFactory          $inventoryRequestInterfaceFactory,
        AddressInterfaceFactory                   $addressInterfaceFactory,
        InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory,
        Filesystem                                $filesystem,
        File                                      $file,
        DriverInterface                           $fileSystemDriver,
        CartRepositoryInterface                   $quoteRepository,
        \Psr\Log\LoggerInterface                  $logger,
        \Magento\Sales\Model\Order                $orderModel
    ) {
        $this->configuration = $configuration;
        $this->clientFactory = $clientFactory;
        $this->calculate = $calculate;
        $this->serviceCollection = $serviceCollection;
        $this->productResourceModel = $productResourceModel;
        $this->sourceRepository = $sourceRepository;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->stockByWebsiteId = $stockByWebsiteId;
        $this->itemRequestInterfaceFactory = $itemRequestInterfaceFactory;
        $this->inventoryRequestInterfaceFactory = $inventoryRequestInterfaceFactory;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->inventoryRequestExtensionInterfaceFactory = $inventoryRequestExtensionInterfaceFactory;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->fileSystemDriver = $fileSystemDriver;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->orderModel = $orderModel;
    }

    /**
     * Process Orders in eas
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Http_Client_Exception
     */
    public function processOrders()
    {
        $orders = $this->serviceCollection->getCustomOrderCollection();

        foreach ($orders as $order) {
            if ($this->checkVat($order)) {
                $response = $this->createOrder($order);
                if (!isset($response->errors)) {
                    $this->saveCheckoutToken($response, $order);
                }
            } else {
                $this->prepareOrderData($order);
            }
        }

        $this->massSaleOrders([]);
    }

    /**
     * Create Order in eas
     *
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Http_Client_Exception
     */
    public function createOrder(\Magento\Sales\Model\Order $order)
    {
        $apiUrl = 'http://internal1.easproject.com/api/createpostsaleorder';
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $client->setHeaders([
            'authorization' => 'Bearer ' . $this->calculate->getAuthorizeToken(),
            'Content-Type' => 'application/json',
            'accept' => 'text/*'
        ]);
        $requestData = $this->getOrderData($order);
        $client->setRawData(json_encode($requestData), 'application/json');
        $response = $client->request(Zend_Http_Client::POST)->getBody();
        $response = json_decode($response);
        $this->confirmOrder($response);

        return $response;
    }

    /**
     * Get Order Data
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderData(\Magento\Sales\Model\Order $order): array
    {
        $requestData = [];

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

        /** @var \Magento\Sales\Model\Order $order */
        $data = [
            "external_order_id" => $order->getData('increment_id'),
            "delivery_method" => $deliveryMethod,
            "delivery_cost" => (float)number_format((float)$order->getData('shipping_amount'), 2),
            "payment_currency" => $order->getData('order_currency_code'),
            "is_delivery_to_person" => true,
            "recipient_first_name" => $order->getData('customer_firstname'),
            "recipient_last_name" => $order->getData('customer_lastname'),
            "recipient_company_vat" => $order->getData("vat_id"),
            "delivery_city" => $order->getData("city"),
            "delivery_postal_code" => $order->getData("postcode"),
            "delivery_country" => $order->getData("country_id"),
            "delivery_phone" => $order->getData("telephone"),
            "delivery_email" => $order->getData("email"),
            'delivery_state_province' => $order->getData("region") ? $order->getData("region") : ''
        ];
        if ($order->getData("company")) {
            $data['recipient_company_name'] = $order->getData("company");
            $data['is_delivery_to_person'] = false;
        }

        $prefix = $order->getData("customer_prefix") ?: $order->getData("prefix");
        if ($prefix) {
            $data['recipient_title'] = $prefix;
        }

        $streets = $order->getData("street");
        switch (is_array($streets) && count($streets)) {
            case 1:
                $data['delivery_address_line_1'] = $streets[0];
                break;
            case 2:
                $data['delivery_address_line_1'] = $streets[0];
                $data['delivery_address_line_2'] = $streets[1];
                break;
            case 3:
                $data['delivery_address_line_1'] = $streets[0];
                $data['delivery_address_line_2'] = $streets[1] . PHP_EOL . $streets[2];
                break;
        }
        if (!is_array($streets)) {
            $data['delivery_address_line_1'] = $streets;
        }
        $items = [];

        $incrId = $order->getIncrementId();
        $order = $this->orderModel->loadByIncrementId($incrId);

        foreach ($order->getItems() as $item) {
            $storeId = $item->getStoreId();
            /** @var ProductInterface $product */
            $product = $item->getProduct();
            $items[] = [
                "short_description" => $product->getSku(),
                "long_description" => $product->getName(),
                "id_provided_by_em" => $product->getId(),
                "quantity" => (int)$item->getData("qty_ordered"),
                "cost_provided_by_em" => (float)number_format(
                    (($item->getOriginalPrice() * $item->getData("qty_ordered")) -
                        $item->getOriginalDiscountAmount()) / (int)$item->getData("qty_ordered"),
                    2
                ),
                "weight" => (float)number_format((float)$item->getWeight(), 2),
                "type_of_goods" => $this->getTypeOfGoods($product),
                Configuration::ACT_AS_DISCLOSED_AGENT => (bool)$this->productResourceModel->getAttributeRawValue(
                    $product->getId(),
                    $this->configuration->getActAsDisclosedAgentAttributeName(),
                    $storeId
                ),
                Configuration::LOCATION_WAREHOUSE_COUNTRY => $this->getLocationWarehouse($item, $product, $order),
            ];

            if ($this->getTypeOfGoods($product) === Configuration::GIFTCARD) {
                $data['delivery_cost'] = 0.0;
            }

            $originatingCountry = $this->productResourceModel->getAttributeRawValue(
                $product->getId(),
                Configuration::COUNTRY_OF_MANUFACTURE,
                $storeId
            );
            if ($originatingCountry) {
                $items[array_key_last($items)][Configuration::ORIGINATING_COUNTRY] = $originatingCountry;
            } else {
                $items[array_key_last($items)][Configuration::ORIGINATING_COUNTRY] =
                    $this->configuration->getStoreDefaultCountryCode();
            }

            $hs6p = $this->productResourceModel->getAttributeRawValue(
                $product->getId(),
                $this->configuration->getHscodeAttributeName(),
                $storeId
            );
            if ($hs6p) {
                $items[array_key_last($items)]['hs6p_received'] = $hs6p;
            }
            $sellerRegistrationCountry = $this->productResourceModel->
            getAttributeRawValue($product->getId(), $this->configuration->getSellerRegistrationName(), $storeId);
            if ($sellerRegistrationCountry) {
                $items[array_key_last($items)][Configuration::SELLER_REGISTRATION_COUNTRY] = $sellerRegistrationCountry;
            } else {
                $items[array_key_last($items)][Configuration::SELLER_REGISTRATION_COUNTRY] =
                    $this->configuration->getStoreDefaultCountryCode();
            }
            $reducedTbeVatGroup = (bool)$this->productResourceModel->getAttributeRawValue(
                $product->getId(),
                $this->configuration->getReducedVatAttributeName(),
                $storeId
            );
            if ($reducedTbeVatGroup) {
                $items[array_key_last($items)][Configuration::REDUCED_TBE_VAT_GROUP] = true;
            }
        }

        $data['order_breakdown'] = $items;
        $requestData['order'] = $data;
        $requestData['sale_date'] = $order->getData('created_at');
        return $requestData;
    }

    /**
     * Get Type Of Goods
     *
     * @param ProductInterface $product
     * @return string
     */
    private function getTypeOfGoods(ProductInterface $product): string
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
     * Get Location Warehouse
     *
     * @param Item $item
     * @param ProductInterface $product
     * @param \Magento\Sales\Model\Order $order
     * @return array|bool|string|null
     * @throws NoSuchEntityException
     */
    private function getLocationWarehouse(Item $item, ProductInterface $product, \Magento\Sales\Model\Order $order)
    {
        if ($this->configuration->getMSIWarehouseLocation()) {
            $sourceCode = $this->getWarehouseCode($item, $product, $order);
            return $this->sourceRepository->get($sourceCode)->getCountryId();
        }

        return $this->productResourceModel->getAttributeRawValue(
            $product->getId(),
            $this->configuration->getWarehouseAttributeName(),
            $item->getStoreId()
        ) ?: $this->configuration->getStoreDefaultCountryCode();
    }

    /**
     * Get Warehouse Code
     *
     * @param Item $item
     * @param ProductInterface $product
     * @param \Magento\Sales\Model\Order $order
     * @return array|bool|string|null
     */
    private function getWarehouseCode(Item $item, ProductInterface $product, \Magento\Sales\Model\Order $order)
    {
        if ($this->configuration->getMSIWarehouseLocation()) {
            $request = $this->getInventoryRequestFromQuote($item, $product, $order);
            $sourceSelectionItems = $this->sourceSelectionService->execute(
                $request,
                $this->configuration->getMSIWarehouseLocation()
            )->getSourceSelectionItems();
            return $sourceSelectionItems[array_key_first($sourceSelectionItems)]->getSourceCode();
        }
        return $this->productResourceModel->getAttributeRawValue(
            $product->getId(),
            $this->configuration->getWarehouseAttributeName(),
            $item->getStoreId()
        ) ?: $this->configuration->getStoreDefaultCountryCode();
    }

    /**
     * Get Inventory Request From Quote
     *
     * @param Item $item
     * @param ProductInterface $product
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    private function getInventoryRequestFromQuote(
        Item $item,
        ProductInterface $product,
        \Magento\Sales\Model\Order $order
    ) {
        $store = $item->getStore();
        $stock = $this->stockByWebsiteId->execute((int)$store->getWebsiteId());
        $requestItems = [];

        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getSku() == $product->getSku()) {
                $requestItems[] = $this->itemRequestInterfaceFactory->create([
                    'sku' => $item->getSku(),
                    'qty' => $item->getData('qty_ordered')
                ]);
            }
        }
        $inventoryRequest = $this->inventoryRequestInterfaceFactory->create(
            [
                'stockId' => $stock->getStockId(),
                'items' => $requestItems
            ]
        );

        $address = $this->getAddressFromQuote($order);
        if ($address !== null) {
            $extensionAttributes = $this->inventoryRequestExtensionInterfaceFactory->create();
            $extensionAttributes->setDestinationAddress($address);
            $inventoryRequest->setExtensionAttributes($extensionAttributes);
        }

        return $inventoryRequest;
    }

    /**
     * Get Address From Quote
     *
     * @param \Magento\Sales\Model\Order $order
     * @return AddressInterface|null
     */
    private function getAddressFromQuote(\Magento\Sales\Model\Order $order): ?AddressInterface
    {
        /** @var AddressInterface $address */
        $address = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();
        if ($address === null) {
            return null;
        }

        return $this->addressInterfaceFactory->create(
            [
                'country' => $address->getCountryId(),
                'postcode' => $address->getPostcode() ?? '',
                'street' => implode("\n", $address->getStreet()),
                'region' => $address->getRegion() ?? $address->getRegionCode() ?? '',
                'city' => $address->getCity() ?? ''
            ]
        );
    }

    /**
     * Confirm Order
     *
     * @param string $token
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Http_Client_Exception
     */
    public function confirmOrder(string $token)
    {
        $apiUrl = 'http://internal1.easproject.com/api/confirmpostsaleorder';
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $client->setHeaders([
            'authorization' => 'Bearer ' . $this->calculate->getAuthorizeToken(),
            'Content-Type' => 'application/json',
            'accept' => 'text/*'
        ]);
        $requestData = [];
        $requestData['order_token'] = $token;
        $client->setRawData(json_encode($requestData), 'application/json');

        $config = [
            Configuration::VERIFYPEER => false
        ];
        $client->setConfig($config);
        $client->request(Zend_Http_Client::POST)->getBody();
    }

    /**
     * Prepare Order Data
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    private function prepareOrderData($order)
    {
        try {
            $orderData = $this->getOrderData($order);
            $orderData['order']['total_order_amount'] = $order->getData('total_due');
            foreach ($orderData['order']['order_breakdown'] as $key => $orderProduct) {
                $orderProductId = $orderProduct['id_provided_by_em'];
                $orderProductPrice = $order->getItems()[$orderProductId]->getData('original_price');
                $orderData['order']['order_breakdown'][$key]['unit_cost'] = $orderProductPrice;
                $orderData['order']['order_breakdown'][$key]['item_delivery_charge'] = $orderProductPrice;
                $orderData['order']['order_breakdown'][$key]['item_delivery_charge_vat'] = $orderProductPrice;
                $orderData['order']['order_breakdown'][$key]['customs_duty_rate'] = $orderProductPrice;
                $orderData['order']['order_breakdown'][$key]['item_customs_duties'] = $orderProductPrice;
                $orderData['order']['order_breakdown'][$key]['vat_rate'] = $orderProductPrice;
                $orderData['order']['order_breakdown'][$key]['item_vat'] = $orderProductPrice;
            }

            $this->massOrdersData[] = $orderData;
        } catch (NoSuchEntityException $e) {
            $this->logger->critical('Error with order: ' . $e->getMessage());
        }
    }

    /**
     * Write Content
     *
     * @param string $content
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function writeContent(string $content)
    {
        $dirname = $this->filesystem->getDirectoryWrite(
            DirectoryList::LOG
        )->getAbsolutePath('eas/orders');
        if (!$this->fileSystemDriver->isDirectory($dirname)) {
            $this->fileSystemDriver->createDirectory($dirname, 0775);
        }

        $this->file->write($dirname . '/' . 'orders.json', $content);
    }

    /**
     * Mass Sale
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Http_Client_Exception
     */
    public function massSale()
    {
        $dirname = $this->filesystem->getDirectoryWrite(
            DirectoryList::LOG
        )->getAbsolutePath('eas/orders');

        $apiUrl = 'https://internal1.easproject.com/api/mass-sale/create_post_sale_without_lc_orders';
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $client->setHeaders([
            'authorization' => 'Bearer ' . $this->calculate->getAuthorizeToken(),
            'accept' => '*/*',
            'Content-Type' => 'multipart/form-data; boundary=file',
        ]);
        $client->setFileUpload($dirname . '/' . 'orders.json', 'file', null, 'multipart/form-data; boundary=file');
        $config = [
            Configuration::VERIFYPEER => false
        ];
        $client->setConfig($config);
        $response = $client->request(Zend_Http_Client::POST)->getBody();
        $response = json_decode($response, true);
        if (isset($response['job_id'])) {
            $this->massSaleJobStatus((string) $response['job_id']);
        }
    }

    /**
     * Mass Sale Job Status
     *
     * @param string $jobId
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Http_Client_Exception
     */
    public function massSaleJobStatus(string $jobId)
    {
        $apiUrl = 'http://internal1.easproject.com/api/mass-sale/get_post_sale_without_lc_job_status/' . $jobId;
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $client->setHeaders([
            'authorization' => 'Bearer ' . $this->calculate->getAuthorizeToken(),
            'Content-Type' => 'application/json',
            'accept' => 'text/*'
        ]);
        $response = $client->request(Zend_Http_Client::GET)->getBody();
        $response = json_decode($response, true);
        if (isset($response['status'])) {
            if ($response['status'] != 'completed') {
                $this->massSaleJobStatus($jobId);
            } else {
                $this->massSaleOrderStatus($jobId);
            }
        }
    }

    /**
     * Mass Sale Order Status
     *
     * @param string $jobId
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Http_Client_Exception
     */
    public function massSaleOrderStatus(string $jobId)
    {
        $apiUrl = 'http://internal1.easproject.com/api/mass-sale/get_post_sale_without_lc_order_status/' . $jobId;
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $client->setHeaders([
            'authorization' => 'Bearer ' . $this->calculate->getAuthorizeToken(),
            'Content-Type' => 'application/json',
            'accept' => 'text/*'
        ]);
        $response = $client->request(Zend_Http_Client::GET)->getBody();
        $response = json_decode($response, true);
        if (isset($response['order_response_list'])) {
            foreach ($response['order_response_list'] as $orderData) {
                $this->saveCheckoutToken($response['checkout_token'], false, (int) $orderData['external_order_id']);
            }
        }
    }

    /**
     * Save Checkout Token
     *
     * @param string $checkoutToken
     * @param bool $order
     * @param int $incId
     * @return void
     * @throws NoSuchEntityException
     */
    public function saveCheckoutToken(string $checkoutToken, bool $order = false, int $incId = 0)
    {
        if ($incId) {
            $cartId = $this->serviceCollection->getQuoteIdByOrderIncId($incId);
            if ($cartId) {
                $this->saveToken($cartId, $checkoutToken);
            }
        } else {
            $this->saveToken($order->getQuoteId(), $checkoutToken);
        }
    }

    /**
     * Save Token
     *
     * @param int $cartId
     * @param string $checkoutToken
     * @return void
     * @throws NoSuchEntityException
     */
    public function saveToken(int $cartId, string $checkoutToken)
    {
        $quote = $this->quoteRepository->get($cartId);
        $quote->setData(Configuration::EAS_TOKEN, $checkoutToken);
        $this->quoteRepository->save($quote);
    }

    /**
     * Check Vat
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function checkVat(\Magento\Sales\Model\Order $order): bool
    {
        return !+$order->getData('base_tax_amount') == true;
    }

    /**
     * Mass Sale Orders
     *
     * @param array $massOrderUpdate
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Http_Client_Exception
     */
    public function massSaleOrders(array $massOrderUpdate): void
    {
        foreach ($this->massOrdersData as $key => $massOrder) {
            if (count($massOrderUpdate) < 50) {
                $massOrderUpdate['order_list'][] = $massOrder;
                unset($this->massOrdersData[$key]);
            }
        }
        $this->writeContent(json_encode($massOrderUpdate));
        $this->massSale();
        if (count($this->massOrdersData)) {
            $this->massSaleOrders([]);
        }
    }
}
