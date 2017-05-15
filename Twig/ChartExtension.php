<?php

namespace DGC\ChartBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;

class ChartExtension extends \Twig_Extension
{
	protected $router;
    protected $requestStack;
	
	function __construct(Router $router, RequestStack $requestStack) {
		$this->router = $router;
        $this->requestStack = $requestStack;
	}

    public function getName()
    {
        return 'dgc_chart_extension';
    }
	
    public function getFunctions()
    {
		return array(
			new \Twig_SimpleFunction('dgcChart_getFilterURL', array($this, 'getFilterURL'), array('is_safe'=>array('html'))),
            new \Twig_SimpleFunction('dgcChart_getFilterParameter', array($this, 'getFilterParameter'), array('is_safe'=>array('html'))),
			new \Twig_SimpleFunction('dgcChart_addParameter', array($this, 'addParameter'), array('is_safe'=>array('html')))
		);
    }

	public function getFilterURL($id, $params)
	{
        $id = str_replace('chartFilter_', '', $id);

		$parameters = $this->_getQueryParameters($this->requestStack->getCurrentRequest());

        if ($params) {
            foreach ($params as $k => $v) {
                $parameters['chartFilter'][$id][$k] = $v;
            }
        } else {
            unset($parameters['chartFilter'][$id]);
        }

		return $this->router->generate($this->requestStack->getCurrentRequest()->get('_route'), $parameters);
	}

	protected function _getQueryParameters($request)
	{
		$parameters = array();
		
		//add query parameters
		foreach ($request->query as $k=>$v) {
			$parameters[$k] = $v;
		}
		
		//add route parameters
		foreach ($request->attributes->get('_route_params') as $k=>$v) {
			$parameters[$k] = $v;
		}
		
		return $parameters;
	}

    public function getFilterParameter($id, $name)
    {
        $id = str_replace('chartFilter_', '', $id);
        return 'chartFilter['.$id.']['.$name.']';
    }

    public function addParameter($key, $value)
    {
        $parameters = $this->_getQueryParameters($this->requestStack->getCurrentRequest());

        $parameters[$key] = $value;

        return $this->router->generate($this->requestStack->getCurrentRequest()->get('_route'), $parameters);
    }


}