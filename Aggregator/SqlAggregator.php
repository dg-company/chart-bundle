<?php

namespace DGC\ChartBundle\Aggregator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class SqlAggregator
 * @package DGC\ChartBundle\Aggregator
 *
 * Example:
 * <code>
 * <?php
 *
 * $aggregator
 *     ->setDatabaseConnection($this->get("database_connection"))
 *     ->setXField('date')
 *     ->addYField('count', 'Views')
 * ;
 * $aggregator->getQueryBuilder()
 *     ->select('date', "COUNT(*) as count")
 *     ->from('views')
 *     ->groupBy('date')
 * ;
 *
 * ?>
 * </code>
 *
 */
class SqlAggregator extends AbstractAggregator
{

    /** @var Connection */
    protected $connection;

    /** @var QueryBuilder */
    protected $queryBuilder;

    public function __clone() {
        //clone query builder on clone
        $this->queryBuilder = clone $this->queryBuilder;
    }

    /**
     * @param Connection $connection
     * @return self
     */
    public function setDatabaseConnection(Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        if (!$this->connection) throw new \Exception("You have to set a database connection before getting the query builder");

        if (!$this->queryBuilder) {
            $this->queryBuilder = $this->connection->createQueryBuilder();
        }

        return $this->queryBuilder;
    }

    protected function applyFilters()
    {
        foreach ($this->filters as $filter) {

            if (!method_exists($filter['filter'], "updateSqlQuery")) throw new \Exception("Missing method updateSqlQuery in filter ".get_class($filter['filter']));

            $filter['filter']->updateSqlQuery($this->getQueryBuilder(), $filter['filterField']);
            
        }
    }

    /**
     * @return array
     */
    public function query()
    {
        $this->applyFilters();

        $data = $this->queryBuilder->execute()->fetchAll();

        return $data;
    }

}