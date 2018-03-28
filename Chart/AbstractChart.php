<?php

namespace DGC\ChartBundle\Chart;

use DGC\ChartBundle\Aggregator\AbstractAggregator;
use DGC\ChartBundle\Filter\AbstractFilter;
use Twig\Environment as TemplateEngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractChart
{
    protected $name;
    protected $title = "";
    protected $xLabel = "";
    protected $yOptions = array();

    protected $ignoreXField = false;

    protected $legendHidden = false;
    protected $colors;

    /** @var array */
    protected $filters = array();

    /** @var AbstractAggregator[] */
    protected $aggregators = array();

    //Dependency injection:

    /** @var TemplateEngineInterface */
    protected $templateEngine;

    /** @var RequestStack */
    protected $requestStack;

    /** @var bool */
    protected $hasData = null;

    /**
     * @param TemplateEngineInterface $templateEngine
     */
    public function setTemplateEngine(TemplateEngineInterface $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    /**
     * AbstractChart constructor.
     * @param $name
     */
    public function __construct($name){
        $this->name = $name;
        $this->colors = array(

            //blue
            '#3B516B',
            '#526D87',

            //light blue
            '#C1D0E3',
            '#AFC3DC',

            //green
            '#429E3D',
            '#5AB24F',

            //yellow
            '#F1B744',
            '#ECA439',

            //red
            '#E13F47',
            '#D93039',

            //purple
            '#C53379',
            '#B5265D',

            //light yellow
            '#FADFA6',
            '#F9D792'
        );
    }

    /**
     * Return true is the chart contains displayable data
     * @return bool
     */
    public function hasData()
    {
        return $this->hasData;
    }

    /**
     * @param AbstractFilter $filter
     * @param string|null $filterField
     * @return self
     */
    public function addFilter(AbstractFilter $filter, $filterField = null){
        $this->filters[] = array(
            "filter" => $filter,
            "filterField" => $filterField
        );
        return $this;
    }

    /**
     * @param AbstractAggregator $aggregator
     * @return self
     */
    public function addAggregator(AbstractAggregator $aggregator)
    {
        $this->aggregators[] = $aggregator;
        return $this;
    }

    /**
     * @param $title
     * @return self
     */
    public function setTitle($title){
        $this->title = $title;
        return $this;
    }

    /**
     * @return $this
     */
    public function hideLegend()
    {
        $this->legendHidden = true;
        return $this;
    }

    /**
     * @param string[] $colors
     * @return $this
     */
    public function setColors($colors)
    {
        $this->colors = $colors;
        return $this;
    }

    /**
     * @param string $label
     * @param int $yIndex
     * @return self
     */
    public function setYLabel($label, $yIndex=0)
    {
        if (!isset($this->yOptions[$yIndex])) $this->yOptions[$yIndex] = array();
        $this->yOptions[$yIndex]['label'] = $label;
        return $this;
    }

    /**
     * @param array $options
     * @param int $yIndex
     * @return self
     */
    public function setYOptions($options, $yIndex=0)
    {
        if (!isset($this->yOptions[$yIndex])) $this->yOptions[$yIndex] = array();

        foreach ($options as $k=>$v) {
            $this->yOptions[$yIndex][$k] = $v;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return "chart_".$this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setXLabel($name)
    {
        $this->xLabel = $name;
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getViewData()
    {
        $keys = array();
        $values = array();

        //iterate over all aggregators to get values
        $aggregatorCounter = 0;

        foreach ($this->aggregators as $aggregator) {

            //add all filters to aggregator
            foreach ($this->filters as $filter) {
                $aggregator->addFilter($filter['filter'], $filter['filterField']);
            }

            //get data for aggregator and add values to data array
            foreach ($aggregator->getData($this->ignoreXField) as $x=>$yValues) {

                if (!isset($values[$x])) {
                    $values[$x] = array();
                }

                //add Y values
                foreach ($yValues as $yKey=>$y) {

                    $values[$x][$aggregatorCounter."_".$yKey] = $y;

                }

            }

            foreach ($aggregator->getYFields() as $field) {
                $keys[$aggregatorCounter."_".$field['field']] = $field;
            }

            $aggregatorCounter++;
            

        }

        //make sure that all y fields are set and in the right order for each x-field
        foreach ($values as $x=>$yValues) {

            $newYValues = array();

            foreach ($keys as $key=>$keyInfo) {

                if (!isset($yValues[$key])) {
                    $newYValues[$key] = 0;
                } else {
                    $newYValues[$key] = $yValues[$key];
                }

            }

            foreach ($yValues as $k=>$v) {
                if (preg_match('@\_id$@isU', $k)) {
                    $newYValues['_id'] = $v;
                }
            }

            $values[$x] = $newYValues;

        }

        $this->hasData = (count($values) > 0);

        return array(
            "values" => $values,
            "keys" => $keys,
            "xLabel" => $this->xLabel,
            "yOptions" => $this->yOptions,
            "id" => $this->getId(),
            "title" => $this->title,
            "colors" => $this->colors,
            "legendHidden" => $this->legendHidden
        );

    }

    /**
     * @return string
     */
    abstract protected function render();


}