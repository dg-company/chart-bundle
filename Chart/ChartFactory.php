<?php

namespace DGC\ChartBundle\Chart;

use Twig\Environment as TemplateEngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class ChartFactory
{
    protected $templateEngine;
    protected $requestStack;

    public function __construct(TemplateEngineInterface $templateEngine, RequestStack $requestStack){
        $this->templateEngine =  $templateEngine;
        $this->requestStack = $requestStack;
    }

    protected function initChart(AbstractChart $chart)
    {
        $chart->setTemplateEngine($this->templateEngine);
        $chart->setRequestStack($this->requestStack);
    }

    public function createAdvancedChart($chartName) {
        $chart = new AdvancedChart($chartName);
        $this->initChart($chart);
        return $chart;
    }

    public function createLineChart($chartName) {
        $chart = new LineChart($chartName);
        $this->initChart($chart);
        return $chart;
    }

    public function createClusteredLineChart($chartName) {
        $chart = new ClusteredLineChart($chartName);
        $this->initChart($chart);
        return $chart;
    }

    public function createBarChart($chartName) {
        $chart = new BarChart($chartName);
        $this->initChart($chart);
        return $chart;
    }

    public function createColumnChart($chartName) {
        $chart = new ColumnChart($chartName);
        $this->initChart($chart);
        return $chart;
    }

    public function createFunnelChart($chartName) {
        $chart = new FunnelChart($chartName);
        $this->initChart($chart);
        return $chart;
    }

    public function createPieChart($chartName) {
        $chart = new PieChart($chartName);
        $this->initChart($chart);
        return $chart;
    }

    public function createListChart($chartName) {
        $chart = new ListChart($chartName);
        $this->initChart($chart);
        return $chart;
    }

    public function createSingleNumberChart($chartName) {
        $chart = new SingleNumberChart($chartName);
        $this->initChart($chart);
        return $chart;
    }
}