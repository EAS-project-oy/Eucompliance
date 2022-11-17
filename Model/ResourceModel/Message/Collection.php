<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Model\ResourceModel\Message;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'message_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Easproject\Eucompliance\Model\Message::class,
            \Easproject\Eucompliance\Model\ResourceModel\Message::class
        );
    }
}
