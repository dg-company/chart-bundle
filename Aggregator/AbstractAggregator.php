<?php

namespace DGC\ChartBundle\Aggregator;

use Psr\Log\LoggerInterface;
use DGC\ChartBundle\Filter\AbstractFilter;

abstract class AbstractAggregator
{

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @var string
     */
    protected $xField;

    /** @var string|null */
    protected $idField = null;

    /**
     * @var string[]
     */
    protected $yFields = array();

    /**
     * @var string|null
     */
    protected $layerField = null;

    /**
     * @var array
     */
    protected $filters = array();

    /** @var callable[] */
    protected $callbacks = array();

    /** @var callable|null */
    protected $rawCallback = null;

    /**
     * @param LoggerInterface $logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $field
     * @param string|null $idField
     * @return $this
     */
    public function setXField($field, $idField = null)
    {
        $this->xField = $field;
        $this->idField = $idField;
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function addYField($field, $label=false, $options=null)
    {
        $this->yFields[] = array(
            "field" => $field,
            "label" => $label,
            "options" => $options
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getYFields()
    {
        return $this->yFields;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function setLayerField($field)
    {
        $this->layerField = $field;
        return $this;
    }


    /**
     * @param AbstractFilter $filter
     * @param string|null $filterField
     * @return self
     */
    public function addFilter(AbstractFilter $filter, $filterField = null)
    {
        $this->filters[] = array(
            "filter" => $filter,
            "filterField" => $filterField
        );
        return $this;
    }


    /**
     * @param callable $callback
     * @return self
     */
    public function addResultCallback($callback)
    {
        $this->callbacks[] = $callback;
        return $this;
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function setRawCallback($callback)
    {
        $this->rawCallback = $callback;
        return $this;
    }


    /**
     * @return array
     */
    public function getData($ignoreXField = false)
    {
        if (!$ignoreXField AND !$this->xField) throw new \Exception("Missing X field");
        if (!count($this->yFields)) throw new \Exception("Missing Y fields");

        $rawData = $this->query();
        
        if ($this->rawCallback) {
            $rawCallback = $this->rawCallback;
            $rawData = $rawCallback($rawData);
        }

        //apply callbacks
        foreach ($this->callbacks as $callback) {
            if (is_callable($callback)) {
                foreach ($rawData as $k=>$v) {
                    $rawData[$k] = $callback($rawData[$k]);
                }
            }
        }

        $data = array();

        foreach ($rawData as $item) {

            if (!$ignoreXField AND !isset($item[$this->xField])) throw new \Exception("Data entry has no X field '".$this->xField."': ".json_encode($item));

            if ($ignoreXField) {
                $key = 0;
            } else {
                $key = $item[$this->xField];
            }

            if ($key instanceof \MongoId) {
                $key = (String) $key;
            }
            $values = array();

            if ($this->idField) {
                $values['_id'] = $item[$this->idField];
            }

            foreach ($this->yFields as $yField) {
                $yField = $yField['field'];

                if (!array_key_exists($yField, $item)) throw new \Exception("Data entry has no Y field '".$yField."': ".json_encode($item));

                $values[$yField] = $item[$yField];

            }

            $data[$key] = $values;

        }
        
        return $data;
    }



    abstract protected function query();

}