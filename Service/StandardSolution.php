<?php
namespace Easproject\Eucompliance\Service;

use Easproject\Eucompliance\Api\Data\JobInterface;
use Easproject\Eucompliance\Api\Data\JobInterfaceFactory;
use Easproject\Eucompliance\Api\JobRepositoryInterface;
use Easproject\Eucompliance\Helper\OrderCalculation;
use Easproject\Eucompliance\Model\Config\Configuration;
use Firebase\JWT\JWT;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Easproject\Eucompliance\Model\ResourceModel\Job\CollectionFactory as JobCollectionFactory;
use Psr\Log\LoggerInterface;

class StandardSolution
{
    private const ORDER_EAS_EXPORTED_ATTRIBUTE = 'eas_exported';
    private const ORDER_EAS_TOKEN_ATTRIBUTE = 'eas_token';
    private const ORDER_EAS_ERROR = 'eas_error';

    private const AVAILABLE_COUNTRIES_URL = '/visualization/fetch_supported_country_list_for_em';
    private const ORDER_VALIDATE_URL = '/visualization/em_order_list?external_order_id=';
    private const EXPORT_URL = '/mass-sale/create_post_sale_without_lc_orders';
    private const JOB_STATUS_URL = '/mass-sale/get_post_sale_without_lc_order_status/';

    private array $availableCountries = [];

    /** @var CollectionFactory  */
    protected CollectionFactory $orderCollectionFactory;

    /** @var ClientFactory  */
    protected ClientFactory $httpClientFactory;

    /** @var SerializerInterface  */
    protected SerializerInterface $serializer;

    /** @var Calculate  */
    protected Calculate $calculateService;

    /** @var Configuration  */
    protected Configuration $configuration;

    /** @var OrderCalculation  */
    protected OrderCalculation $orderCalculationHelper;

    /** @var JobInterfaceFactory  */
    protected JobInterfaceFactory $jobFactory;

    /** @var JobCollectionFactory  */
    protected JobCollectionFactory $jobCollectionFactory;

    /** @var JobRepositoryInterface  */
    protected JobRepositoryInterface $jobRepository;

    /** @var OrderRepositoryInterface  */
    protected OrderRepositoryInterface $orderRepository;

    /** @var Filesystem  */
    protected Filesystem $filesystem;

    /** @var \Easproject\Eucompliance\Service\Request\Order  */
    protected \Easproject\Eucompliance\Service\Request\Order $orderService;

    /** @var SearchCriteriaBuilder  */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /** @var JWT  */
    protected JWT $jwt;

    /** @var LoggerInterface  */
    protected LoggerInterface $logger;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        ClientFactory $httpClientFactory,
        SerializerInterface $serializer,
        Calculate $calculateService,
        Configuration $configuration,
        OrderCalculation $orderCalculationHelper,
        JobInterfaceFactory $jobFactory,
        JobCollectionFactory $jobCollectionFactory,
        JobRepositoryInterface $jobRepository,
        OrderRepositoryInterface $orderRepository,
        Filesystem $filesystem,
        \Easproject\Eucompliance\Service\Request\Order $orderService,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        JWT $jwt,
        LoggerInterface $logger
    )
    {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->httpClientFactory = $httpClientFactory;
        $this->serializer = $serializer;
        $this->calculateService = $calculateService;
        $this->configuration = $configuration;
        $this->orderCalculationHelper = $orderCalculationHelper;
        $this->jobFactory = $jobFactory;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->jobRepository = $jobRepository;
        $this->orderRepository = $orderRepository;
        $this->filesystem = $filesystem;
        $this->orderService = $orderService;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->jwt = $jwt;
        $this->logger = $logger;
    }

    /**
     * Return orders that are not sent
     *
     * @return Collection
     */
    public function getNonExportedOrdersCollection(): Collection
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addAttributeToFilter(
            self::ORDER_EAS_EXPORTED_ATTRIBUTE,
            '0'
        )->addAttributeToFilter(
            OrderInterface::STATUS,
            'Processing'
        );
        $collection->load();
        return $collection;
    }

    /**
     * Return jobs that are not yet completed
     *
     * @return \Easproject\Eucompliance\Model\ResourceModel\Job\Collection
     */
    public function getNonCompleteJobs(): \Easproject\Eucompliance\Model\ResourceModel\Job\Collection
    {
        $collection = $this->jobCollectionFactory->create();
        $collection->addFieldToFilter(
            JobInterface::SYNCED,
            0,
        );
        $collection->load();
        return $collection;
    }

    /**
     * General process logic
     *
     * @param $url
     * @param $method
     * @param null $body
     * @param bool $file
     * @return array|bool|float|int|string|null
     * @throws GuzzleException
     * @throws InputException
     * @throws FileSystemException
     */
    public function processRequest($url, $method, $body = null, bool $file = false)
    {
        $client = $this->httpClientFactory->create();
        try {
            $options = [
                RequestOptions::VERIFY => false,
                RequestOptions::HEADERS => [
                    'authorization' => 'Bearer ' . $this->calculateService->getAuthorizeToken()
                ],
            ];
            if (
                $method === Request::HTTP_METHOD_POST ||
                $method === Request::HTTP_METHOD_PUT
            ) {
                if (!$file) {
                    $options[RequestOptions::HEADERS]['Content-Type'] = 'application/json';
                }
                $options[RequestOptions::HEADERS]['accept'] = '*/*';
                if ($body) {
                    if ($file) {
                        $this->orderService->writeContent(
                            $this->serializer->serialize($body),
                            'order_standard_solution.json'
                        );
                        $dirname = $this->filesystem->getDirectoryWrite(
                            DirectoryList::LOG
                        )->getAbsolutePath('eas/orders');
                        $options[RequestOptions::MULTIPART] = [
                            [
                                'name' => 'orders.json',
                                'contents' => Utils::tryFopen($dirname . '/order_standard_solution.json', 'r'),
                                'filename' => $dirname . '/order_standard_solution.json',
                            ]
                        ];
                    } else {
                        $options[RequestOptions::JSON] = $body;
                    }
                }
            }
            return $this->serializer->unserialize($client->request(
                $method,
                $this->configuration->getBaseUrl() . $url,
                $options
            )->getBody()->getContents());
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseData = $this->serializer->unserialize((string)$response->getBody()->getContents());
            $this->logger->debug(
                'Failed to process url ' . $url . '. Exception message: ' . $responseData['message'] .
                ' File path: ' . ($dirname ?? "") . '/' . 'order_standard_solution.json' .
                ' code: ' . $e->getCode()
            );
            throw $e;
        } catch (\Exception $e) {
            $this->logger->debug(
                'Failed to process url ' . $url . '. Exception message: ' . $e->getMessage() .
                ' code: ' . $e->getCode()
            );
            throw $e;
        }
    }

    /**
     * Check if order country is available. Returns true if available, false - otherwise
     *
     * @param Order $order
     * @return bool
     * @throws FileSystemException
     * @throws GuzzleException
     * @throws InputException
     */
    public function validateCountry(Order $order): bool
    {
        $countryCode = $order->getShippingAddress()->getCountryId();
        $availableCountries = empty($this->availableCountries) ? $this->processRequest(
            self::AVAILABLE_COUNTRIES_URL,
            Request::HTTP_METHOD_GET
        ) : $this->availableCountries;
        foreach ($availableCountries as $availableCountry) {
            if ($availableCountry['country_code'] === $countryCode) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Order $order
     * @return bool
     * @throws FileSystemException
     * @throws GuzzleException
     * @throws InputException
     */
    public function checkOrderExists(Order $order): bool
    {
        try{
            $easOrder = $this->processRequest(
                self::ORDER_VALIDATE_URL . $order->getIncrementId(),
                Request::HTTP_METHOD_GET
            );
        } catch (ClientException $e) {
            /** Just in case of logic change. If it will be single search with http error result */
            if ($e->getCode() === 404) {
                return false;
            }
            throw $e;
        }
        if (!isset($easOrder['rowCount']) || $easOrder['rowCount'] < 1) {
            return false;
        }
        return true;
    }

    /**
     * @param float $num
     * @return float
     */
    private function roundTo2(float $num): float
    {
        return (float)(number_format($num, 2));
    }

    /**
     * Create order items data to export
     *
     * @param array $items
     * @param Order $order
     * @return array
     * @throws NoSuchEntityException
     */
    private function prepareItemExportData(array $items, Order $order): array
    {
        $res = [];
        /** @var Order\Item $item */
        foreach ($items as $item) {
            $product = $item->getProduct();
            $originatingCountry = $product->getData(Configuration::COUNTRY_OF_MANUFACTURE);
            $hs6p = $product->getData($this->configuration->getHscodeAttributeName());
            $sellerRegistrationCountry = $product->getData($this->configuration->getSellerRegistrationName());
            $reducedTbeVatGroup = $product->getData($this->configuration->getReducedVatAttributeName());
            $res[] = [
                "short_description" => $item->getSku(),
                "long_description" => $item->getName(),
                "id_provided_by_em" => $product->getId(),
                "quantity" => (int)$item->getQtyOrdered(),
                "cost_provided_by_em" => (float)number_format(
                    ($item->getOriginalPrice() *
                        $item->getQtyOrdered() - $item->getDiscountAmount()) / $item->getQtyOrdered(),
                    2
                ),
                "weight" => (float)number_format((float)$product->getWeight(), 2),
                "type_of_goods" => $this->orderCalculationHelper->getTypeOfGoods($product),
                Configuration::ACT_AS_DISCLOSED_AGENT => (bool)$product->getData(
                    $this->configuration->getActAsDisclosedAgentAttributeName()
                ),
                Configuration::LOCATION_WAREHOUSE_COUNTRY =>
                    $this->orderCalculationHelper->getLocationWarehouse($order, $product),
                Configuration::ORIGINATING_COUNTRY => $originatingCountry ?:
                    $this->configuration->getStoreDefaultCountryCode(),
                'hs6p_received' => $hs6p ?? null,
                Configuration::SELLER_REGISTRATION_COUNTRY => $sellerRegistrationCountry ?:
                    $this->configuration->getStoreDefaultCountryCode(),
                Configuration::REDUCED_TBE_VAT_GROUP => (bool)$reducedTbeVatGroup,
                'unit_cost' => $this->roundTo2((float)$item->getOriginalPrice()),
                'vat_rate' => $this->roundTo2((float)$item->getTaxPercent() / 100),
                'item_vat' => $this->roundTo2((float)$item->getTaxAmount()),
            ];
        }
        return $res;
    }



    /**
     * Prepare export data for single order
     *
     * @param Order $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareExportData(Order $order): array
    {
        $address = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();
        $customerEmail = $address->getEmail() ?: $order->getCustomerEmail();
        $streets = $address->getStreet();
        $data = [
            "external_order_id" => $order->getIncrementId(),
            "delivery_cost" => (float)number_format((float)$order->getShippingAmount(), 2),
            "payment_currency" => $order->getOrderCurrencyCode(),
            "is_delivery_to_person" => true,
            "recipient_first_name" =>
                $order->getCustomerFirstname() ?: $order->getBillingAddress()->getFirstName(),
            "recipient_last_name" =>
                $order->getCustomerLastname() ?: $order->getBillingAddress()->getLastName(),
            "recipient_company_vat" => $address->getVatId(),
            "delivery_city" => $address->getCity(),
            "delivery_postal_code" => $address->getPostcode(),
            "delivery_country" => $address->getCountryId(),
            "delivery_phone" => $address->getTelephone() ?: 'Not provided',
            "delivery_email" => $customerEmail ?: $this->configuration->getDefaultEmail(),
            'delivery_state_province' => $address->getRegion() ? $address->getRegion() : '',
            'delivery_address_line_1' => $streets[0] ?? null,
            'delivery_address_line_2' => implode(PHP_EOL, array_slice($streets, 1)), // all except 1st part of streets
            'recipient_title' => ($order->getCustomerPrefix() ?: $address->getPrefix()) ?? null,
            'order_breakdown' => $this->prepareItemExportData($order->getAllVisibleItems(), $order),
            'total_order_amount' => $order->getData('total_due'),
            'delivery_method' => $this->orderCalculationHelper->getDeliveryMethod($order)
        ];

        /** TODO: Here we should search for gift cards in items or pass pointer to data to prepareItemExportData to set 0 to delivery amount */

        /** Not sure if this is needed */
        if ($address->getCompany()) {
            $data['recipient_company_name'] = $address->getCompany();
            $data['is_delivery_to_person'] = false;
        }

        return $data;
    }

    /**
     * @param int $jobId
     * @param JobInterface $job
     * @return void
     * @throws GuzzleException
     * @throws InputException
     * @throws LocalizedException
     */
    private function fillEasTokens(int $jobId, JobInterface $job)
    {
        $jobStatuses = $this->processRequest(self::JOB_STATUS_URL . $jobId, Request::HTTP_METHOD_GET);
        if (!isset($jobStatuses['order_response_list'])) {
            $this->logger->error("order_response_list is not set for job " . $jobId);
            return;
        }
        $completed = true;
        $totalJobError = '';

        foreach ($jobStatuses['order_response_list'] as $jobStatus) {
            if (!isset($jobStatus['external_order_id'])) {
                $job = $job->setStatus($jobStatus['status']);
                $job = $job->setError('Order id is not set');
                $job = $job->setSynced(1);
                $this->jobRepository->save($job);
                return;
            }
            $criteria = $this->searchCriteriaBuilder
                ->addFilter(OrderInterface::INCREMENT_ID, $jobStatus['external_order_id'])
                ->create();
            $orders = $this->orderRepository->getList($criteria)->getItems();
            if (!count($orders)) {
                $job = $job->setStatus($jobStatus['status']);
                $job = $job->setError('EAS Order ' . $jobStatus['external_order_id'] . ' not found');
                $job = $job->setSynced(1);
                $this->jobRepository->save($job);
                return;
            }
            $order = $orders[array_keys($orders)[0]];
            if (isset($jobStatus['error'])) {
                $totalJobError .= $jobStatus['external_order_id'] . PHP_EOL;
                $totalMsg = '';
                if (!isset($jobStatus['error']['message'])) {
                    foreach ($jobStatus['error']['data'] as $error) {
                        $totalMsg .= $error['message'] . PHP_EOL;
                    }
                } else {
                    $totalMsg .= $jobStatus['error']['message'];
                }

                $order->setData(self::ORDER_EAS_ERROR, $totalMsg);
                if (isset($jobStatus['checkout_token'])) {
                    $order->setData(self::ORDER_EAS_TOKEN_ATTRIBUTE, $jobStatus['checkout_token']);
                }
                $order->save();
                // $this->orderRepository->save($order);
                $totalJobError .= $totalMsg;
                $completed = false;
                continue;
            }
            if (
                !isset($jobStatus['checkout_token'])
            ) {
                $completed = false;
                $totalJobError .= 'checkout_token not set' . PHP_EOL;
                continue;
            }

            $order->setData(self::ORDER_EAS_TOKEN_ATTRIBUTE, $jobStatus['checkout_token']);
            $order->setData(self::ORDER_EAS_EXPORTED_ATTRIBUTE, 1);
            $this->orderRepository->save($order);
        }
        if (empty($totalJobError) && $completed) {
            $job = $job->setStatus('completed');
        } else {
            $job = $job->setStatus('rejected');
            $job = $job->setError($totalJobError);
        }
        $job = $job->setSynced(1);
        $this->jobRepository->save($job);
    }

    /**
     * @param $easResponse
     * @return mixed|void
     * @throws LocalizedException
     */
    public function createJob($easResponse)
    {
        // It may be that job already exists
        $jobCollection = $this->jobCollectionFactory->create();

        $jobExists = (bool)$jobCollection
            ->addFieldToFilter(JobInterface::JOB_ID, $easResponse['job_id'])
            ->load()
            ->count();
        if ($jobExists) {
            return $easResponse;
        }
        $newJob = $this->jobFactory->create();
        $newJob = $newJob->setJobId((int)$easResponse['job_id']);
        /** TODO: change regarding real structure of data */
        if (isset($easResponse['code'])) {
            $totalError = $easResponse['message'];
            $newJob = $newJob->setError($totalError);
        }
        $this->jobRepository->save($newJob);
    }

    /**
     * Export non exported orders
     *
     * @return mixed
     * @throws GuzzleException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function export()
    {
        $toExport = ['order_list' => []];
        /** @var Order $order */
        foreach ($this->getNonExportedOrdersCollection() as $order) {
            try{
                if (!$this->validateCountry($order) || $this->checkOrderExists($order)) {
                    continue;
                }
                $toExport['order_list'][] = [
                    'order' => $this->prepareExportData($order),
                    'sale_date' => $order->getData('created_at')
                ];
            } catch (\Exception $e) {
                $order->setData(self::ORDER_EAS_ERROR, $e->getMessage());
                $this->orderRepository->save($order);
                continue;
            }
        }
        $response = $this->processRequest(self::EXPORT_URL,
            Request::HTTP_METHOD_POST,
            $toExport,
            true
        );
        if (!isset($response['job_id'])) {
            throw new LocalizedException(__("Something went wrong. Cannot export to EAS %1 order", $order->getIncrementId()));
        }

        $this->createJob($response);

        return $response;
    }

    /**
     * Validate and refill eas tokens
     *
     * @return void
     * @throws GuzzleException
     * @throws InputException
     * @throws LocalizedException
     */
    public function validate()
    {
        /** @var JobInterface $job */
        foreach ($this->getNonCompleteJobs() as $job)
        {
            $this->fillEasTokens($job->getJobId(), $job);
        }
    }



}
