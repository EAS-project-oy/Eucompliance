<?php

namespace Easproject\Eucompliance\Ui\Component\Listing\Column;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class EasToken extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    private CollectionFactory $quoteCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory
     * @param array                                                        $components
     * @param array                                                        $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory  $quoteCollectionFactory,
        array              $components = [],
        array              $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->quoteCollectionFactory = $quoteCollectionFactory;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $incId = $item['increment_id'];
                $quoteCollection = $this->quoteCollectionFactory->create();
                $quoteCollection->addFieldToFilter('reserved_order_id', ['eq' => $incId]);
                $quoteCollection->addFieldToSelect('eas_token');
                $data = $quoteCollection->getFirstItem();
                $columnContext = $data->getData('eas_token') ? 'YES' : 'NO';
                $item[$this->getData('name')] = $columnContext;
            }
        }
        return $dataSource;
    }
}
