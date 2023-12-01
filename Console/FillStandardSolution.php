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

namespace Easproject\Eucompliance\Console;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Service\Request\Order;
use Easproject\Eucompliance\Service\StandardSolution;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\ObjectManagerInterface;

class FillStandardSolution extends Command
{

    /** @var StandardSolution  */
    protected StandardSolution $standardSolutionService;

    /** @var OrderRepositoryInterface  */
    protected OrderRepositoryInterface $orderRepository;

    /** @var ObjectManagerInterface */
    protected ObjectManagerInterface $objectManager;

    /**
     * @var State
     */
    protected State $state;

    /***
     * @param ObjectManagerInterface $objectManager
     * @param State $state
     * @param string|null $name
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        State $state,
        string $name = null
    )
    {
        $this->objectManager = $objectManager;
        $this->state = $state;
        parent::__construct($name);
    }

    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName("easproject:fill-standard-solution");
        $this->setDescription("Fill order data for standard solution");
        parent::configure();
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
                [$this, "executeFill"],
                [$input, $output]
            );
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
        $output->writeln("Order processing completed successfully");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function executeFill(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->objectManager->create(Configuration::class);
        if (!$configuration->isStandardSolution()) {
            throw new LocalizedException(
                __(
                    "For command to work, standard solution must be enabled"
                )
            );
        }
        $this->standardSolutionService = $this->objectManager->create(StandardSolution::class);
        $newJob = $this->standardSolutionService->export();
        $this->standardSolutionService->validate($newJob);
    }
}
