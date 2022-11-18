<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Service;

use Easproject\Eucompliance\Api\Data\MessageInterfaceFactory as MessageFactory;
use Easproject\Eucompliance\Api\MessageRepositoryInterface;
use Easproject\Eucompliance\Setup\Patch\Data\AddGiftCardProductAttribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\Repository;
use Magento\Quote\Model\QuoteRepository;
use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;
use Zend_Http_Client;
use Zend_Http_Client_Exception;
use Magento\Framework\Serialize\SerializerInterface;

class Calculate
{

    /**
     * @var ZendClientFactory
     */
    private ZendClientFactory $clientFactory;

    /**
     * @var string|null
     */
    private ?string $token;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Product
     */
    private Product $productResourceModel;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var QuoteRepository
     */
    private QuoteRepository $quoteRepository;

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var AddressInterfaceFactory
     */
    private AddressInterfaceFactory $addressInterfaceFactory;

    /**
     * @var InventoryRequestExtensionInterfaceFactory
     */
    private InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private StockByWebsiteIdResolverInterface $stockByWebsiteId;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private InventoryRequestInterfaceFactory $inventoryRequestInterfaceFactory;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private ItemRequestInterfaceFactory $itemRequestInterfaceFactory;

    /**
     * @var SourceSelectionServiceInterface
     */
    private SourceSelectionServiceInterface $sourceSelectionService;

    /**
     * @var SourceRepositoryInterface
     */
    private SourceRepositoryInterface $sourceRepository;

    /**
     * @var Repository
     */
    private Repository $quoteItemRepository;

    /**
     * @var MessageRepositoryInterface
     */
    private MessageRepositoryInterface $messageRepository;

    /**
     * @var MessageFactory
     */
    private MessageFactory $messageFactory;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var string[]
     */
    private $keyMapping = [
        'delivery_address_line_1' => 'Street Address',
        'delivery_address_line_2' => 'Street Address',
        'delivery_city' => 'City',
        'delivery_country' => 'Country',
        'delivery_email' => 'Email Address',
        'delivery_phone' => 'Phone Number',
        'delivery_postal_code' => 'Zip/Postal Code',
        'delivery_state_province' => 'State/Province',
        'recipient_first_name' => 'First Name',
        'recipient_last_name' => 'Last Name',
        'recipient_company_name' => 'Company'
    ];

    /**
     * Calculate constructor.
     *
     * @param ZendClientFactory                         $clientFactory
     * @param StoreManagerInterface                     $storeManager
     * @param Product                                   $productResourceModel
     * @param QuoteRepository                           $quoteRepository
     * @param LoggerInterface                           $logger
     * @param UrlInterface                              $url
     * @param AddressInterfaceFactory                   $addressInterfaceFactory
     * @param StockByWebsiteIdResolverInterface         $stockByWebsiteId
     * @param ItemRequestInterfaceFactory               $itemRequestInterfaceFactory
     * @param InventoryRequestInterfaceFactory          $inventoryRequestInterfaceFactory
     * @param InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
     * @param SourceSelectionServiceInterface           $sourceSelectionService
     * @param SourceRepositoryInterface                 $sourceRepository
     * @param Repository                                $quoteItemRepository
     * @param Configuration                             $configuration
     * @param MessageRepositoryInterface                $messageRepository
     * @param MessageFactory                            $messageFactory
     * @param SerializerInterface                       $serializer
     */
    public function __construct(
        ZendClientFactory                         $clientFactory,
        StoreManagerInterface                     $storeManager,
        Product                                   $productResourceModel,
        QuoteRepository                           $quoteRepository,
        LoggerInterface                           $logger,
        UrlInterface                              $url,
        AddressInterfaceFactory                   $addressInterfaceFactory,
        StockByWebsiteIdResolverInterface         $stockByWebsiteId,
        ItemRequestInterfaceFactory               $itemRequestInterfaceFactory,
        InventoryRequestInterfaceFactory          $inventoryRequestInterfaceFactory,
        InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory,
        SourceSelectionServiceInterface           $sourceSelectionService,
        SourceRepositoryInterface                 $sourceRepository,
        Repository                                $quoteItemRepository,
        Configuration                             $configuration,
        MessageRepositoryInterface                $messageRepository,
        MessageFactory                            $messageFactory,
        SerializerInterface                       $serializer
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->itemRequestInterfaceFactory = $itemRequestInterfaceFactory;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->inventoryRequestInterfaceFactory = $inventoryRequestInterfaceFactory;
        $this->stockByWebsiteId = $stockByWebsiteId;
        $this->clientFactory = $clientFactory;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->inventoryRequestExtensionInterfaceFactory = $inventoryRequestExtensionInterfaceFactory;
        $this->storeManager = $storeManager;
        $this->productResourceModel = $productResourceModel;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->url = $url;
        $this->configuration = $configuration;
        $this->token = null;
        $this->messageRepository = $messageRepository;
        $this->messageFactory = $messageFactory;
        $this->serializer = $serializer;
    }

    /**
     * Calculate
     *
     * @param  Quote $quote
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Zend_Http_Client_Exception
     * @throws CouldNotSaveException
     */
    public function calculate(Quote $quote): array
    {
        if (!$this->configuration->isEnabled()) {
            return ['disabled' => true];
        }

        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId();
        }

        list($data, $response) = $this->sendRequest($quote);
        if ($this->configuration->isDebugEnabled()) {
            $this->logger->debug('Eas data send :' . $this->serializer->serialize($data));
            $this->logger->debug('Eas data get :' . $response);
        }
        if (filter_var(str_replace('"', '', $response), FILTER_VALIDATE_URL)) {
            $this->quoteRepository->save($quote);
            return ['redirect' => str_replace('"', '', $response)];
        }
        $this->logger->critical('Eas calculate failed' . $response);
        $errors = $this->serializer->unserialize($response);
        if (array_key_exists('type', $errors)) {
            return $this->getErrorResult($errors);
        }
        $errors = array_key_exists('errors', $errors) ? $errors['errors'] :
            (array_key_exists('message', $errors) ? $errors['message'] : $errors['messages']);
        return $this->getErrorResult($errors);
    }

    /**
     * GetAuthorizeToken
     *
     * @param string $apiKey
     * @param string $secretApiKey
     * @param string $baseApiUrl
     * @return string|null
     * @throws InputException
     */
    public function getAuthorizeToken($apiKey = null, $secretApiKey = null, $baseApiUrl = null): ?string
    {
        if (!$this->token) {
            $client = $this->clientFactory->create();
            if ($baseApiUrl) {
                $baseApiUrl = $baseApiUrl . Configuration::CREDENTIALS_AUTHORIZE_URL;
            } else {
                $baseApiUrl = $this->configuration->getAuthorizeUrl();
            }

            $apiKey = $apiKey ?: $this->configuration->getApiKey();
            $secretApiKey = $secretApiKey ?: $this->configuration->getSecretKey();
            $client->setUri($baseApiUrl);
            $client->setHeaders(
                [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $secretApiKey),
                ]
            );

            $client->setParameterPost('grant_type', 'client_credentials');
            $this->setConfig($client);
            $token = $this->serializer->unserialize($client->request(Zend_Http_Client::POST)->getBody());
            if ($token && array_key_exists(Configuration::ACCESS_TOKEN, $token)) {
                $this->token = $token[Configuration::ACCESS_TOKEN];
            } else {
                throw new InputException(__('Wrong auth keys provided'));
            }

        }
        return $this->token;
    }

    /**
     * Set config client
     *
     * @param array $client
     * @return void
     */
    protected function setConfig($client)
    {
        $config = [
            Configuration::VERIFYPEER => false
        ];
        $client->setConfig($config);
    }

    /**
     * Get Warehouse Code
     *
     * @param Quote $quote
     * @param ProductInterface $product
     * @return array|bool|string|null
     * @throws NoSuchEntityException
     */
    private function getWarehouseCode(Quote $quote, ProductInterface $product)
    {
        if ($this->configuration->getMSIWarehouseLocation()) {
            $request = $this->getInventoryRequestFromQuote($quote, $product);
            $sourceSelectionItems = $this->sourceSelectionService->execute(
                $request,
                $this->configuration->getMSIWarehouseLocation()
            )->getSourceSelectionItems();
            return $sourceSelectionItems[array_key_first($sourceSelectionItems)]->getSourceCode();
        }
        return $this->productResourceModel->getAttributeRawValue(
            $product->getId(),
            $this->configuration->getWarehouseAttributeName(),
            $quote->getStoreId()
        ) ?: $this->configuration->getStoreDefaultCountryCode();
    }

    /**
     * Get Inventory Request From Quote
     *
     * @param Quote $quote
     * @param ProductInterface $product
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function getInventoryRequestFromQuote(Quote $quote, ProductInterface $product)
    {
        $store = $this->storeManager->getStore($quote->getStoreId());
        $stock = $this->stockByWebsiteId->execute((int)$store->getWebsiteId());
        $requestItems = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getSku() == $product->getSku()) {
                $requestItems[] = $this->itemRequestInterfaceFactory->create(
                    [
                        'sku' => $item->getSku(),
                        'qty' => $item->getQty()
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

        $address = $this->getAddressFromQuote($quote);
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
     * @param Quote $quote
     * @return AddressInterface|null
     */
    private function getAddressFromQuote(Quote $quote): ?AddressInterface
    {
        /**
         * @var AddressInterface $address
         */
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
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
     * Get Location Warehouse
     *
     * @param Quote $quote
     * @param ProductInterface $product
     * @return array|bool|string|null
     * @throws NoSuchEntityException
     */
    private function getLocationWarehouse(Quote $quote, ProductInterface $product)
    {
        if ($this->configuration->getMSIWarehouseLocation()) {
            $sourceCode = $this->getWarehouseCode($quote, $product);
            return $this->sourceRepository->get($sourceCode)->getCountryId();
        }

        return $this->productResourceModel->getAttributeRawValue(
            $product->getId(),
            $this->configuration->getWarehouseAttributeName(),
            $quote->getStoreId()
        ) ?: $this->configuration->getStoreDefaultCountryCode();
    }

    /**
     * Get Error Result
     *
     * @param array $error
     * @return bool[]|\Magento\Framework\Phrase[]
     */
    private function getErrorResult($error): array
    {
        $message = $this->getUserMessage($error);
        switch ($error['type']) {
            case 'STANDARD_CHECKOUT':
                return ['disabled' => true];
            case 'STOP_SELLING':
                return ['error' => $message];
            default:
                $this->sendToAdmin($error);
                return ['error' => $message];
        }
    }

    /**
     * Get User Message
     *
     * @param array $error
     * @return \Magento\Framework\Phrase
     */
    private function getUserMessage($error): \Magento\Framework\Phrase
    {
        if ($this->configuration->isDebugEnabled()) {
            $message = $this->getFullMessage($error);
        } else {
            $message = $this->getErrorMessage($error);
        }

        return __($message);
    }

    /**
     * Get Full Message
     *
     * @param array $error
     * @return string
     */
    public function getFullMessage($error): string
    {
        $message = $this->getErrorMessage($error);
        if (isset($error['data']) && isset($error['data']['message'])) {
            $message .= $error['data']['message'];
        }
        return $message;
    }

    /**
     * Get Error Message
     *
     * @param array $error
     * @return string
     */
    public function getErrorMessage($error): string
    {
        $message = '';
        if (isset($error['message'])) {
            $message = $this->getMessage($error);
        }
        if (!isset($error['message']) && !isset($error['data'])) {
            $message = $this->getKeyMessage($error, $message);
        }
        return $message;
    }

    /**
     * Get Message
     *
     * @param array $error
     * @return string
     */
    public function getMessage($error): string
    {
        $message = '';
        if ($error['type'] === 'CONTACT_ADMIN') {
            if (array_key_exists($error['data']['field'], $this->keyMapping)) {
                $message .= $error['message'] . ' ';
            } else {
                return $this->getDefaultMessage();
            }
        } else {
            $message .= $error['message'] . ' ';
        }
        return $message;
    }

    /**
     * Get Default Message
     *
     * @return string
     */
    private function getDefaultMessage(): string
    {
        return 'Please contact our support to fix the issue';
    }

    /**
     * Get Key Message
     *
     * @param array $error
     * @param string $message
     * @return string
     */
    public function getKeyMessage($error, string $message): string
    {
        foreach ($error as $key => $value) {
            if ($key !== 'type' && array_key_exists($key, $this->keyMapping)) {
                $changedMessage = str_replace($key, __($this->keyMapping[$key]), $value);
                $message .= $changedMessage . ' ';
            }
        }
        return $message ?: $this->getDefaultMessage();
    }

    /**
     * Send To Admin
     *
     * @param array $error
     * @return void
     */
    private function sendToAdmin($error)
    {
        $message = $this->getFullMessage($error);

        $messageModel = $this->messageFactory->create();
        $messageModel->setErrorType($error['type']);
        $messageModel->setResponse($this->serializer->serialize($error));
        $messageModel->setMessage($message);

        try {
            $this->messageRepository->save($messageModel);
        } catch (LocalizedException $e) {
            $this->logger->error('Error when saving data to admin: ' . $e->getMessage());
        }
    }

    /**
     * Confirm Order
     *
     * @param OrderInterface $order
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function confirmOrder(OrderInterface $order)
    {
        if ($this->configuration->isEnabled()) {
            $quote = $this->quoteRepository->get((int)$order->getQuoteId());
            if ($quote->getEasToken() && !$quote->getEasConfirmationSent()) {
                $apiUrl = $this->configuration->getPaymentVerifyUrl();
                $client = $this->clientFactory->create();
                $client->setUri($apiUrl);
                $client->setHeaders(
                    [
                        'authorization' => 'Bearer ' . $this->getAuthorizeToken(),
                        'Content-Type' => 'application/json',
                        'accept' => 'text/*'
                    ]
                );

                $data = [
                    'token' => $quote->getEasToken(),
                    'checkout_payment_id' => $order->getIncrementId()
                ];
                $client->setRawData($this->serializer->serialize($data), 'application/json');
                $this->setConfig($client);
                $response = $client->request(Zend_Http_Client::POST)->getBody();
                if (empty($response)) {
                    $quote->setEasConfirmationSent(true);
                    $this->quoteRepository->save($quote);
                } else {
                    $this->logger->debug(
                        'EAS: quote with id ' . $quote->getEntityId() .
                        'failed confirmation. Response body ' . $response
                    );
                }
            }
        }
    }

    /**
     * Get Public Key
     *
     * @return mixed
     * @throws InputException
     */
    public function getPublicKey()
    {
        $apiUrl = $this->configuration->getApiKeysUrl();
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $this->setConfig($client);
        $client->setHeaders(
            [
                'authorization' => 'Bearer ' . $this->getAuthorizeToken(),
                'Content-Type' => 'application/json',
                'accept' => 'text/*'
            ]
        );
        return $client->request(Zend_Http_Client::GET)->getBody();
    }

    /**
     * Get Type Of Goods
     *
     * @param ProductInterface $product
     * @return string
     */
    private function getTypeOfGoods($product): string
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
     * Send Request
     *
     * @param Quote $quote
     * @return array
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function sendRequest(
        Quote $quote
    ): array {

        $apiUrl = $this->configuration->getCalculateUrl();
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $client->setHeaders(
            [
                'authorization' => 'Bearer ' . $this->getAuthorizeToken(),
                'x-redirect-uri' => $this->url->getUrl(Configuration::EAS_CALCULATE),
                'Content-Type' => 'application/json',
                'accept' => 'text/*'
            ]
        );

        $storeId = $this->storeManager->getStore()->getId();
        $address = $quote->getIsVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();

        $deliveryMethod = Configuration::COURIER;

        if ($this->configuration->getPostalMethods()) {
            foreach (explode(',', $this->configuration->getPostalMethods()) as $postalMethod) {
                if ($address->getShippingMethod() == $postalMethod) {
                    $deliveryMethod = Configuration::POSTAL;
                }
            }
        }

        if ($quote->isVirtual()) {
            $deliveryMethod = Configuration::POSTAL;
        }

        $data = [
            "external_order_id" => $quote->getReservedOrderId(),
            "delivery_method" => $deliveryMethod,
            "delivery_cost" => (float)number_format((float)$address->getShippingAmount(), 2),
            "payment_currency" => $quote->getQuoteCurrencyCode(),
            "is_delivery_to_person" => true,
            "recipient_first_name" =>
                $quote->getCustomerFirstname() ?: $quote->getBillingAddress()->getFirstName(),
            "recipient_last_name" =>
                $quote->getCustomerLastname() ?: $quote->getBillingAddress()->getLastName(),
            "recipient_company_vat" => $address->getVatId(),
            "delivery_city" => $address->getCity(),
            "delivery_postal_code" => $address->getPostcode(),
            "delivery_country" => $address->getCountryId(),
            "delivery_phone" => $address->getTelephone(),
            "delivery_email" => $address->getEmail() ?: $quote->getCustomerEmail(),
            'delivery_state_province' => $address->getRegion() ? $address->getRegion() : ''
        ];

        if ($address->getCompany()) {
            $data['recipient_company_name'] = $address->getCompany();
            $data['is_delivery_to_person'] = false;
        }

        $prefix = $quote->getCustomerPrefix() ?: $address->getPrefix();
        if ($prefix) {
            $data['recipient_title'] = $prefix;
        }

        /**
         * @TODO need refactoring in future versions
         */
        $streets = $address->getStreet();
        switch (count($streets)) {
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
        $items = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            /**
             * @var ProductInterface $product
             */
            $product = $item->getProduct();
            // set warehouse code
            $extAttributes = $item->getExtensionAttributes();
            $extAttributes->setEasWarehouseCode($this->getWarehouseCode($quote, $product));
            $item->setExtensionAttributes($extAttributes);
            $this->quoteItemRepository->save($item);
            $items[] = [
                "short_description" => $product->getSku(),
                "long_description" => $product->getName(),
                "id_provided_by_em" => $product->getId(),
                "quantity" => (int)$item->getQty(),
                "cost_provided_by_em" => (float)number_format(
                    ($item->getOriginalPrice() *
                        $item->getQty() - $item->getOriginalDiscountAmount()) / $item->getQty(),
                    2
                ),
                "weight" => (float)number_format((float)$product->getWeight(), 2),
                "type_of_goods" => $this->getTypeOfGoods($product),
                Configuration::ACT_AS_DISCLOSED_AGENT => (bool)$this->productResourceModel->getAttributeRawValue(
                    $product->getId(),
                    $this->configuration->getActAsDisclosedAgentAttributeName(),
                    $storeId
                ),
                Configuration::LOCATION_WAREHOUSE_COUNTRY => $this->getLocationWarehouse($quote, $product),
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
            $sellerRegistrationCountry = $this->productResourceModel
                ->getAttributeRawValue($product->getId(), $this->configuration->getSellerRegistrationName(), $storeId);
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
        $client->setRawData($this->serializer->serialize($data), 'application/json');
        $this->setConfig($client);
        $response = $client->request(Zend_Http_Client::POST)->getBody();
        return [$data, $response];
    }
}
