<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Easproject\Eucompliance\Model;

use Easproject\Eucompliance\Api\Data\MessageInterface;
use Magento\Framework\Model\AbstractModel;

class Message extends AbstractModel implements MessageInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Message::class);
    }

    /**
     * @inheritDoc
     */
    public function getMessageId(): ?string
    {
        return $this->getData(self::MESSAGE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setMessageId(string $messageId): MessageInterface
    {
        return $this->setData(self::MESSAGE_ID, $messageId);
    }

    /**
     * @inheritDoc
     */
    public function getErrorType(): ?string
    {
        return $this->getData(self::ERROR_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setErrorType(string $errorType): MessageInterface
    {
        return $this->setData(self::ERROR_TYPE, $errorType);
    }

    /**
     * @inheritDoc
     */
    public function getResponse(): ?string
    {
        return $this->getData(self::RESPONSE);
    }

    /**
     * @inheritDoc
     */
    public function setResponse(string $response): MessageInterface
    {
        return $this->setData(self::RESPONSE, $response);
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): ?string
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage(string $message): MessageInterface
    {
        return $this->setData(self::MESSAGE, $message);
    }
}
