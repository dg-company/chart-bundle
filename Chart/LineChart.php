<?php

namespace DGC\ChartBundle\Chart;

class LineChart extends AbstractChart
{

    /** @var bool */
    protected $smooth = false;

    /**
     * @param bool $smooth
     * @return self
     */
    public function setSmooth($smooth)
    {
        $this->smooth = $smooth;
        return $this;
    }


    /**
     * @return string
     */
    public function render()
    {
        return $this->templateEngine->render("DGCChartBundle:Charts:linechart.html.twig", array(
            "data" => $this->getViewData(),
            "smooth" => $this->smooth
        ));
    }

}