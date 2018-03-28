<?php

namespace DGC\ChartBundle\Filter;

use Twig\Environment as TemplateEngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FilterFactory
{
    protected $templateEngine;
    protected $requestStack;

    public function __construct(TemplateEngineInterface $templateEngine, RequestStack $requestStack){
        $this->templateEngine =  $templateEngine;
        $this->requestStack = $requestStack;
    }

    protected function initFilter(AbstractFilter $filter)
    {
        $filter->setTemplateEngine($this->templateEngine);
        $filter->setRequestStack($this->requestStack);
    }

    public function createDateRangeFilter($name) {
        $filter = new DateRangeFilter($name);
        $this->initFilter($filter);
        return $filter;
    }

    public function createNumberRangeFilter($name){
        $filter = new NumberRangeFilter($name);
        $this->initFilter($filter);
        return $filter;
    }

    public function createReferenceFilter($name) {
        $filter = new ReferenceFilter($name);
        $this->initFilter($filter);
        return $filter;
    }

}