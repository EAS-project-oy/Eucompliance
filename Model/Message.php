<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Eas\Eucompliance\Model;

use Eas\Eucompliance\Api\Data\MessageInterface;
use Magento\Framework\Model\AbstractModel;

class Message extends AbstractModel implements MessageInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Eas\Eucompliance\Model\ResourceModel\Message::class);
    }

    /**
     * @inheritDoc
     */
    public function getMessageId()
    {
        return $this->getData(self::MESSAGE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setMessageId($messageId)
    {
        return $this->setData(self::MESSAGE_ID, $messageId);
    }

    /**
     * @inheritDoc
     */
    public function getErrorType()
    {
        return $this->getData(self::ERROR_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setErrorType($errorType)
    {
        return $this->setData(self::ERROR_TYPE, $errorType);
    }

    /**
     * @inheritDoc
     */
    public function getResponse()
    {
        return $this->getData(self::RESPONSE);
    }

    /**
     * @inheritDoc
     */
    public function setResponse($response)
    {
        return $this->setData(self::RESPONSE, $response);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }
}
