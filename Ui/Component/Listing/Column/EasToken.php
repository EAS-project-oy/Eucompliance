<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;

class EasToken extends Column
{

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $quoteCollectionFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory $quoteCollectionFactory
     * @param array $components
     * @param array $data
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
