<?php

namespace DGC\ChartBundle\Filter;

use Doctrine\DBAL\Query\QueryBuilder as QueryBuilder;

class NumberRangeFilter extends AbstractFilter
{
    /** @var float */
    protected $min;

    /** @var float */
    protected $max;

    /** @var float*/
    protected $rangeMin = 0;

    /** @var float */
    protected $rangeMax = 100;

    /** @var $string*/
    protected $label = '';

    /** @var string */
    protected $unit = '';

    /** @var string */
    protected $sqlField;

    /** @var string */
    protected $mongoField;

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed string
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     * @return NumberRangeFilter
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }
    

    /**
     * @return float
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param float $min
     * @return self
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @return float
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param float $max
     * @return self
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @return float
     */
    public function getRangeMin()
    {
        return $this->rangeMin;
    }

    /**
     * @param float $rangeMin
     * @return self
     */
    public function setRangeMin($rangeMin)
    {
        $this->rangeMin = $rangeMin;
        return $this;
    }

    /**
     * @return float
     */
    public function getRangeMax()
    {
        return $this->rangeMax;
    }

    /**
     * @param float $rangeMax
     * @return NumberRangeFilter
     */
    public function setRangeMax($rangeMax)
    {
        $this->rangeMax = $rangeMax;
        return $this;
    }
    
    /**
     * @param string $sqlField
     * @return self
     */
    public function setSqlField($sqlField)
    {
        $this->sqlField = $sqlField;
        return $this;
    }

    /**
     * @param string $mongoField
     * @return self
     */
    public function setMongoField($mongoField)
    {
        $this->mongoField = $mongoField;
        return $this;
    }


    /**
     * @param string|null $filterField
     * @return array|null
     */
    public function getMongoFilterQuery($filterField)
    {
        $requestParameters = $this->getRequestParameters();

        $min = isset($requestParameters['min']) ? $requestParameters['min'] : $this->min;
        $max = isset($requestParameters['max']) ? $requestParameters['max'] : $this->max;

        if (!$min OR !$max) return null; //do not use this filter

        return array(
            $this->mongoField => array(
                '$gte' => $min,
                '$lte' => $max
            )
        );
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $filterField
     * @return null
     */
    public function updateSqlQuery(QueryBuilder $qb, $filterField)
    {

        $requestParameters = $this->getRequestParameters();

        $min = isset($requestParameters['min']) ? intval($requestParameters['min']) : null;
        $max = isset($requestParameters['max']) ? intval($requestParameters['max']) : null;

        if ($min === null OR $max === null OR ($min == $this->min AND $max == $this->max)) return null; //do not use this filter

        $qb
            ->andWhere("`".$this->sqlField."` >= :min AND `".$this->sqlField."` <= :max")
            ->setParameter("min", $min)
            ->setParameter("max", $max)
        ;
        
    }

    /**
     * @return string
     */
    public function render()
    {
        $requestParameters = $this->getRequestParameters();

        $min = isset($requestParameters['min']) ? $requestParameters['min'] : $this->min;
        $max = isset($requestParameters['max']) ? $requestParameters['max'] : $this->max;

        return $this->templateEngine->render("DGCChartBundle:Filters:number_range_filter.html.twig", array(
            "id" => $this->getId(),
            "label" => $this->getLabel(),
            "rangeMin"=>$this->getRangeMin(),
            "rangeMax"=>$this->getRangeMax(),
            "min" => $min,
            "max" => $max,
            "unit" => $this->unit
        ));
    }

}