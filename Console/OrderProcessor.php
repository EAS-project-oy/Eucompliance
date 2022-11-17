<?php

namespace Easproject\Eucompliance\Console;

use Easproject\Eucompliance\Service\Request\Order;
use Magento\Framework\App\State;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\ObjectManagerInterface;

class OrderProcessor extends Command
{
    /**
     * @var Order
     */
    private $requestOrder;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var State
     */
    private State $state;

    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    /**
     * Constructor for order processing
     *
     * @param State $state
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        State $state,
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
        $this->state = $state;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
    }

    /**
     * Run order processor
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Completed order processing");
        try {
            $this->state->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_ADMINHTML,
                [$this, "executeProcessOrders"],
                [$input, $output]
            );
        } catch (\Exception $e) {
            $this->logger->critical('error with run process orders: ' . $e->getMessage());
        }
        $output->writeln("Order processing completed successfully");
    }

    /**
     * Run processOrders with area emulate
     *
     * @return void
     */
    public function executeProcessOrders()
    {
        try {
            $this->requestOrder = $this->objectManager->create(Order::class);
            $this->requestOrder->processOrders();
        } catch (FileSystemException|InputException|NoSuchEntityException|\Zend_Http_Client_Exception $e) {
            $this->logger->critical('error with process orders: ' . $e->getMessage());
        }
    }

    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName("easproject:order-processor");
        $this->setDescription("Order processing started");
        parent::configure();
    }
}
