<?php

namespace DGC\ChartBundle\Chart;

class PieChart extends AbstractChart
{

    protected $startAngle = 90;
    protected $tooltipFormat = "{b} {d}%";
    protected $thresholdDisplayPieSlice = 3;
    protected $thresholdDisplayPieSliceDescription = 10;

    /**
     * render
     *
     * @return string
     */
    public function render()
    {
        $data = $this->getViewData();
        
        return $this->templateEngine->render("@DGCChart/Charts/piechart.html.twig", array(
            "data" => $data,
            "startAngle" => $this->startAngle,
            "tooltipFormat" => $this->tooltipFormat,
            "thresholdDisplayPieSlice" => $this->thresholdDisplayPieSlice,
            "thresholdDisplayPieSliceDescription" => $this->thresholdDisplayPieSliceDescription,
        ));
    }

    /**
     * setStartAngle
     *
     * @param $startAngle
     * @return $this
     */
    public function setStartAngle($startAngle){
        $this->startAngle = $startAngle;
        return $this;
    }

    /**
     * formatTooltip
     *
     * @param $tooltipFormat
     * @return $this
     */
    public function formatTooltip($tooltipFormat){
        $this->tooltipFormat = $tooltipFormat;
        return $this;
    }

    /**
     * sets the threshold until a pie slice is shown
     *
     * @param $threshold
     */
    public function setThresholdDisplayPieSlice($threshold){
        $this->thresholdDisplayPieSlice = $threshold;
    }

    /**
     * sets the threshold until the description of a pie slice is shown
     *
     * @param $threshold
     */
    public function setThresholdDisplayPieSliceDescription($threshold){
        $this->thresholdDisplayPieSliceDescription = $threshold;
    }
}