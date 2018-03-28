<?php

namespace DGC\ChartBundle\Filter;

use Twig\Environment as TemplateEngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractFilter
{

    /** @var string */
    protected $name;

    //Dependency injection:

    /** @var TemplateEngineInterface */
    protected $templateEngine;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param TemplateEngineInterface $templateEngine
     * @return self
     */
    public function setTemplateEngine(TemplateEngineInterface $templateEngine)
    {
        $this->templateEngine = $templateEngine;
        return $this;
    }

    /**
     * @param RequestStack $requestStack
     * @return self
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        return $this;
    }

    /**
     * AbstractFilter constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }



    /**
     * @return string
     */
    public function getId()
    {
        return "chartFilter_".$this->name;
    }

    /**
     * @return array
     */
    public function getRequestParameters()
    {
        $requestParameters = $this->requestStack->getCurrentRequest()->get('chartFilter');
        $id = $this->getId();
        $id = str_replace('chartFilter_', '', $id);
        
        if (isset($requestParameters[$id])) return $requestParameters[$id];
        return array();
    }

}