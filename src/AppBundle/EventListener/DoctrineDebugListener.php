<?php
declare(strict_types=1);

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Purpose: Fast Doctrine debug info for frontend. (totalQueries, totalExecutionTime ...)
 */
class DoctrineDebugListener
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $logger = new \Doctrine\DBAL\Logging\DebugStack();
        $this->em->getConnection()->getConfiguration()->setSQLLogger($logger);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $content = $response->getContent();
        if (!empty($content)) {
            /** @var \Doctrine\DBAL\Logging\DebugStack $logger */
            $logger = $this->em->getConnection()->getConfiguration()->getSQLLogger();
            if (isset($logger->queries)) {
                $contentDecoded = \json_decode($content, true);
                if (JSON_ERROR_NONE !== \json_last_error()) { // Is valide JSON?
                    return;
                }

                $totalExecutionTime = 0;
                foreach ($logger->queries as $query) {
                    $totalExecutionTime += $query['executionMS'];
                }
                $contentDecoded['z_totalQueries'] = $logger->currentQuery;
                $contentDecoded['z_totalExecutionTime'] = \round($totalExecutionTime * 1000, 0) . 'ms';
                //$contentDecoded['z_queries'] = $logger->queries;

                $contentEncoded = \json_encode($contentDecoded);
                $response->setContent($contentEncoded);
            }
        }
    }
}