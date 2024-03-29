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

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;

class EasToken extends Column
{

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $quoteCollectionFactory;

    /** @var SearchCriteriaBuilder  */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /** @var OrderRepositoryInterface  */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory $quoteCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory  $quoteCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        array              $components = [],
        array              $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
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
                $quoteCollection = $this->quoteCollectionFactory->create();
                $quoteCollection->addFieldToFilter('reserved_order_id', ['eq' => $incId]);
                $quoteCollection->addFieldToSelect('eas_token');
                $data = $quoteCollection->getFirstItem();
                $columnContext = $data->getData('eas_token') ||
                (
                    count($orders) &&
                    $orders[array_keys($orders)[0]]->getData('eas_token')
                ) ? 'YES' : 'NO';
                $item[$this->getData('name')] = $columnContext;
            }
        }
        return $dataSource;
    }
}
