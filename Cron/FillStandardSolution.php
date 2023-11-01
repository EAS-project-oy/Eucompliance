<?php
namespace Easproject\Eucompliance\Cron;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Service\StandardSolution;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class FillStandardSolution
{

    /** @var StandardSolution  */
    protected StandardSolution $standardSolution;

    /** @var Configuration  */
    protected Configuration $configuration;

    /**
     * @param StandardSolution $standardSolution
     * @param Configuration $configuration
     */
    public function __construct(
        StandardSolution $standardSolution,
        Configuration $configuration
    )
    {
        $this->standardSolution = $standardSolution;
        $this->configuration = $configuration;
    }

    /**
     * @return $this
     * @throws GuzzleException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if ($this->configuration->isStandardSolution()) {
            $this->standardSolution->export();
            $this->standardSolution->validate();
        }
        return $this;

    }
}
