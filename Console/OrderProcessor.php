<?php

namespace Easproject\Eucompliance\Console;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrderProcessor extends Command
{
    /**
     * @var \Easproject\Eucompliance\Service\Request\Order
     */
    private \Easproject\Eucompliance\Service\Request\Order $order;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private \Psr\Log\LoggerInterface $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    private \Magento\Framework\App\State $state;

    /**
     * @param \Easproject\Eucompliance\Service\Request\Order $order
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\State $state
     * @param string|null $name
     */
    public function __construct(
        \Easproject\Eucompliance\Service\Request\Order $order,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state,
        string $name = null
    ) {
        parent::__construct($name);
        $this->order = $order;
        $this->logger = $logger;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln("Completed order processing");
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            $this->order->processOrders();
        } catch (FileSystemException|InputException|NoSuchEntityException|\Zend_Http_Client_Exception $e) {
            $this->logger->critical('error with process orders: ' . $e->getMessage());
        }

        $output->writeln("Order processing completed successfully");
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("easproject:order-processor");
        $this->setDescription("Order processing started");
        parent::configure();
    }
}
