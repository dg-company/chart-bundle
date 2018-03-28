<?php

namespace DGC\ChartBundle\Chart;


class BarChart extends AbstractChart
{
    /**
     * @return string
     */
    public function render()
    {
        return $this->templateEngine->render("@DGCChart/Charts/barchart.html.twig", array(
            "data" => $this->getViewData()
        ));
    }

}