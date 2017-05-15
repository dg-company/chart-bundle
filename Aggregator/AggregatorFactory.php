<?php

namespace DGC\ChartBundle\Aggregator;

use Psr\Log\LoggerInterface;

class AggregatorFactory
{

    protected $logger;

    public function __construct(LoggerInterface $logger){
        $this->logger =  $logger;
    }

    protected function initAggregator(AbstractAggregator $aggregator)
    {
        $aggregator->setLogger($this->logger);
    }

    public function createMongoAggregator()
    {
        $aggregator = new MongoAggregator();
        $this->initAggregator($aggregator);
        return $aggregator;
    }

    public function createSqlAggregator()
    {
        $aggregator = new SqlAggregator();
        $this->initAggregator($aggregator);
        return $aggregator;
    }

    public function createManualAggregator()
    {
        $aggregator = new ManualAggregator();
        $this->initAggregator($aggregator);
        return $aggregator;
    }

}