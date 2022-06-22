<?php

namespace Easproject\Eucompliance\Service\Order;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Collection
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private CollectionFactory $orderCollectionFactory;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(CollectionFactory $orderCollectionFactory)
    {
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getCustomOrderCollection()
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->getSelect()->join(
            ['quote'],
            'main_table.quote_id = quote.entity_id',
            []
        )->columns(["quote.eas_token", "sales_order_address.*"])
            ->join(
                ['sales_order_address'],
                'main_table.entity_id = sales_order_address.parent_id',
                []
            )->where("quote.eas_token IS NULL AND main_table.total_paid IS NULL AND sales_order_address.address_type = 'shipping'");
        return $collection->getItems();
    }

    /**
     * @param $incrementId
     * @return false|mixed
     */
    public function getQuoteIdByOrderIncId($incrementId)
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('increment_id', $incrementId);
        $collection->getSelect()->join(
            ['quote'],
            'main_table.quote_id = quote.entity_id',
            []
        );
        return count($collection->getData()) ? $collection->getData()[0]['quote_id'] : false;
    }
}
