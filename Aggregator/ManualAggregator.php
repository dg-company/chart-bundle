<?php

namespace DGC\ChartBundle\Aggregator;

class ManualAggregator extends AbstractAggregator
{
    protected $generator;

    /**
     * @param $generator
     * @return self
     */
    public function setGenerator($generator)
    {
        $this->generator = $generator;
        return $this;
    }

    protected function applyFilters()
    {
        foreach ($this->filters as $filter) {
            // TODO
        }
    }

    /**
     * @return array
     */
    public function query()
    {
        $this->applyFilters();

        $generator = $this->generator;
        if (is_callable($generator)) {
            $data = $generator();
        } else {
            $data = array();
        }

        return $data;
    }

}