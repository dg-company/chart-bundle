<?php

namespace DGC\ChartBundle\Aggregator;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class MongoAggregator
 * @package DGC\ChartBundle\Aggregator
 *
 * Example:
 * <code>
 * <?php
 *
 * $aggregator
 *     ->setDocumentManager($this->get("doctrine_mongodb.odm.main_document_manager"))
 *     ->setCollection("xxxBundle:Test")
 *     ->match(array(
 *         'type' => 'test'
 *     ))
 *     ->group(array(
 *         '_id' => '$time',
 *         'count' => array('$sum' => 1)
 *     ))
 *     ->addResultCallback(function($data) {
 *         //modify each result
 *         //...
 *         return $data;
 *     })
 *     ->setXField('_id')
 *     ->addYField('count', 'Number of x')
 * ;
 *
 * ?>
 * </code>
 *
 */
class MongoAggregator extends AbstractAggregator
{

    /** @var array */
    protected $pipelineStages = array();

    /** @var string */
    protected $collectionName;

    /** @var DocumentManager */
    protected $documentManager;


    /**
     * @param string $collectionName
     * @return self
     */
    public function setCollection($collectionName)
    {
        $this->collectionName = $collectionName;
        return $this;
    }


    /**
     * @param DocumentManager $documentManager
     * @return self
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
        return $this;
    }

    /**
     * @param string $type
     * @param mixed $query
     * @return self
     */
    protected function addPipelineStage($type, $query)
    {
        $this->pipelineStages[] = array(
            'type' => $type,
            'query' => $query
        );
    }

    /**
     * @param array $query
     * @return self
     */
    public function match($query)
    {
        $this->addPipelineStage('match', $query);
        return $this;
    }

    /**
     * @param array $query
     * @return self
     */
    public function project($query)
    {
        $this->addPipelineStage('project', $query);
        return $this;
    }

    /**
     * @param array $query
     * @return self
     */
    public function redact($query)
    {
        $this->addPipelineStage('redact', $query);
        return $this;
    }

    /**
     * @param array $query
     * @return self
     */
    public function unwind($query)
    {
        $this->addPipelineStage('unwind', $query);
        return $this;
    }

    /**
     * @param array $query
     * @return self
     */
    public function group($query)
    {
        $this->addPipelineStage('group', $query);
        return $this;
    }

    /**
     * @param array $query
     * @return self
     */
    public function sample($query)
    {
        $this->addPipelineStage('sample', $query);
        return $this;
    }

    /**
     * @param array $query
     * @return self
     */
    public function geoNear($query)
    {
        $this->addPipelineStage('geoNear', $query);
        return $this;
    }

    /**
     * @param array $query
     * @return self
     */
    public function lookup($query)
    {
        $this->addPipelineStage('lookup', $query);
        return $this;
    }

    /**
     * @param array $sort
     * @return self
     */
    public function sort($sort)
    {
        foreach ($sort as $k=>$v) {
            if (strtolower($v) == 'asc') $sort[$k] = 1;
            if (strtolower($v) == 'desc') $sort[$k] = -1;
        }

        $this->addPipelineStage('sort', $sort);

        return $this;
    }

    /**
     * @param int $number
     * @return self
     */
    public function limit($number)
    {
        $this->addPipelineStage('limit', $number);
        return $this;
    }

    /**
     * @param int $number
     * @return self
     */
    public function skip($number)
    {
        $this->addPipelineStage('skip', $number);
        return $this;
    }


    /**
     * @return string
     * @throws \Exception
     */
    protected function getShortCollectioName()
    {
        $parts = explode(":", $this->collectionName);
        if (!isset($parts[1])) throw new \Exception("Invalid collection name. Use following notation: BundleName:CollectionName");
        return $parts[1];
    }


    /**
     * @return \Doctrine\MongoDB\Collection
     * @throws \Exception
     */
    protected function getConnection()
    {
        return $this->documentManager->getDocumentDatabase($this->collectionName)->selectCollection($this->getShortCollectioName());
    }

    protected function applyFilters()
    {
        $filterQueries = array();

        foreach ($this->filters as $filter) {

            if (!method_exists($filter['filter'], "getMongoFilterQuery")) throw new \Exception("Missing method getMongoFilterQuery in filter ".get_class($filter['filter']));

            $q = $filter['filter']->getMongoFilterQuery($filter['filterField']);

            if ($q) {
                $filterQueries[] = $q;
            }

        }

        if (count($filterQueries) == 0) return; //no filters applied

        //find first match-stage in pipeline and add filter queries

        foreach ($this->pipelineStages as $k=>$pipelineStage) {

            if ($pipelineStage['type'] != "match") continue;

            if (!isset($this->pipelineStages[$k]['query']['$and'])) {
                $this->pipelineStages[$k]['query']['$and'] = array();
            }

            foreach ($filterQueries as $filterQuery) {
                $this->pipelineStages[$k]['query']['$and'][] = $filterQuery;
            }

            return;
        }

        //no filter stage found --> add as first stage
        $and = array();

        foreach ($filterQueries as $filterQuery) {
            $and[] = $filterQuery;
        }

        array_unshift($this->pipelineStages, array(
            'type' => 'match',
            'query' => array(
                '$and' => $and
            )
        ));

    }

    protected function getPipeline()
    {
        $pipeline = array();

        foreach ($this->pipelineStages as $pipelineStage) {
            $pipeline[] = array(
                '$'.$pipelineStage['type'] => $pipelineStage['query']
            );
        }

        $this->logger->debug("MongoDB aggregation Query: ".json_encode($pipeline));

        return $pipeline;
    }

    /**
     * @return array
     */
    public function query()
    {
        $this->applyFilters();

        $this->getConnection()->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED);
        
        return $this->getConnection()->aggregate($this->getPipeline(), array(
            //options
        ))->toArray();

    }

}