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

namespace Easproject\Eucompliance\Ui\Component\Listing\Column;

use Easproject\Eucompliance\Service\Calculate;
use Firebase\JWT\JWT;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;

class IOSS extends Column
{

    /** @var SearchCriteriaBuilder  */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /** @var OrderRepositoryInterface  */
    protected OrderRepositoryInterface $orderRepository;

    /** @var JWT  */
    protected JWT $jwt;

    /**
     * @var Calculate
     */
    private Calculate $calculate;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param JWT $jwt
     * @param Calculate $calculate
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        JWT $jwt,
        Calculate $calculate,
        array              $components = [],
        array              $data = []
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->jwt = $jwt;
        $this->calculate = $calculate;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $incId = $item['increment_id'];
                $criteria = $this->searchCriteriaBuilder
                    ->addFilter(OrderInterface::INCREMENT_ID, $incId)
                    ->create();
                $orders = $this->orderRepository->getList($criteria)->getItems();
                $order =  !count($orders) ? null : $orders[array_keys($orders)[0]];
                $decoded = null;
                if ($order && $order->getData('eas_token')) {
                    try{
                        $decoded = $this->jwt->decode(
                            $order->getData('eas_token'),
                            $this->calculate->getPublicKey(),
                            ['RS256']
                        );
                    } catch (\Exception $e) {
                    }
                }
                $columnContext = !$order ||
                $order->getIsVirtual() ||
                !$decoded ||
                !$decoded->FID ? 'NO' : 'YES';
                $item[$this->getData('name')] = $columnContext;
            }
        }
        return $dataSource;
    }
}
