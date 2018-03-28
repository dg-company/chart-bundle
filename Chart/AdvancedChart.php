<?php

namespace DGC\ChartBundle\Chart;

use Symfony\Component\HttpFoundation\Response;

class AdvancedChart extends AbstractChart
{

    /** @var bool */
    protected $horizontal = false;
    /** @var string */
    protected $labelFormat = "";
    /** @var int */
    protected $containerWidth = '100%';
    /** @var int */
    protected $containerHeight = '100%';
    /** @var bool */
    protected $showToolbar = false;
    /** @var bool */
    protected $showDownload = false;
    /** @var bool */
    protected $showSplitLines = false;
    /** @var bool */
    protected $showData = false;
    /** @var bool */
    protected $showXAxis = true;
    /** @var bool */
    protected $showYAxis= true;
    /** @var mixed */
    protected $xAxisLabelInterval = 'auto';
    /** @var mixed */
    protected $yAxisLabelInterval = 'auto';
    /** @var bool */
    protected $relativeScale = false;
    
    protected $viewData = null;

    protected $tooltipFormat = "";

    protected $seriesLabelPosition = 'top';

    protected $hideLabelLength = 0;
    protected $hideLabelLengthDependent = false;

    /**
     * Set height of the chart
     *
     * @param int $height
     * @return self
     */
    public function setHeight($height)
    {
        $this->containerHeight = $height;
        return $this;
    }

    /**
     * Set width of the chart
     *
     * @param int $width
     * @return self
     */
    public function setWidth($width)
    {
        $this->containerWidth = $width;
        return $this;
    }


    /**
     * Set format of bar/line labels
     *
     * example:
     * formatLabel('{b}: {c} {a}')
     *
     * generates this output: "Key: Value Unit" (e.g. "Skateboard: 50 units")
     *
     * @param string $format
     * @return self
     */
    public function setLabelFormat($format = ''){

        $this->labelFormat = $format;
        return $this;

    }


    /**
     * Sets the label position of one element in the chart
     * Possible values: top, bottom, left, right, insideLeft, insideRight, insideTop, insideBottom
     *
     * @param string $position
     * @return self
     */
    public function setSeriesLabelPosition($position = 'top'){
        $this->seriesLabelPosition = $position;
        return $this;
    }

    /**
     * specifies which x-axis-labels are shown
     * defaults to 'auto'. 'auto' hides those labels, which eCharts thinks can not be displayed.
     * Sometimes eCharts unnecessarily hides labels.
     *
     * set to 0 to show all labels
     * set to n, to show every n-th label
     * @param mixed $interval
     * @return self
     */

    public function setXAxisLabelInterval($interval = 'auto'){

        $this->xAxisLabelInterval = $interval;
        return $this;
    }

    /**
     * specifies which y-axis-labels are shown
     * defaults to 'auto'. 'auto' hides those labels, which eCharts thinks can not be displayed.
     * Sometimes eCharts unnecessarily hides labels.
     *
     * set to 0, to show all labels
     * set to n, to show every n-th label 
     * @param mixed $interval
     * @return self
     */

    public function setYAxisLabelInterval($interval = 'auto'){

        $this->yAxisLabelInterval = $interval;
        return $this;
    }

    /**
     * If true, the charts view window does not start at 0, but at the lowest y-value
     * @return self
     */
    public function setRelativeScale($relative = true)
    {
        $this->relativeScale = $relative;
        return $this;
    }

    protected function downloadCSV()
    {
        $data = $this->getViewData();
        $csvData = array();

        //header
        $header[] = $data['xLabel'];
        foreach ($data['keys'] as $key) {
            if (isset($key['label'])) {
                $header[] = $key['label'];
            } else {
                $header[] = "";
            }
        }
        $csvData[] = $header;

        //data
        foreach ($data['values'] as $key => $value) {
            $csvData[] = array_merge(array($key), array_values($value));
        }

        //Output
        header('Content-type: text/comma-separated-values');
        header('Content-Disposition: attachment; filename="' . date("Ymd") . '_' . $this->title . '.csv"');

        foreach ($csvData as $lineIndex => $line) {

            echo '"' . implode('";"', str_replace('"', '', $line)) . '"' . "\n";

        }

        die();
    }

    protected function download($type)
    {
        switch ($type) {
            case 'csv':
                $this->downloadCSV();
                break;
        }
        return new Response("Type not found", 404);
    }

    /**
     * Show bar charts horizontally (not working if line charts are used)
     *
     * @param bool $horizontal
     * @return self
     */
    public function setHorizontal($horizontal = true)
    {
        $this->setSeriesLabelPosition("right");
        $this->horizontal = $horizontal;
        return $this;
    }

    /**
     * @param bool $show
     * @return self
     */
    public function showXAxis($show = true)
    {
        $this->showXAxis = $show;
        return $this;
    }

    /**
     * @param bool $show
     * @return self
     */
    public function showYAxis($show = true)
    {
        $this->showYAxis = $show;
        return $this;
    }

    /**
     * @return string
     */
    public function getViewData()
    {
        if ($this->viewData !== null) return $this->viewData;

        if ($this->horizontal) {
            $data = parent::getViewData();
            $data['values'] = array_reverse($data['values']);
            return $data;
        }
        $this->viewData = parent::getViewData();

        return $this->viewData;
    }

    /**
     * Display toolbar
     *
     * @param bool $show
     * @return self
     */
    public function showToolbar($show = true)
    {
        $this->showToolbar = $show;
        return $this;
    }

    /**
     * Show data used to build chart
     *
     * @param bool $show
     * @return self
     */
    public function showData($show = true)
    {
        $this->showData = $show;
        return $this;
    }

    /**
     * Show buttons to download chart
     *
     * @param bool $show
     * @return self
     */
    public function showDownload($show = true)
    {
        $this->showDownload = $show;
        return $this;
    }

    /**
     * Show split lines
     *
     * @param bool $show
     * @return self
     */
    public function showSplitLines($show = true)
    {
        $this->showSplitLines = $show;
        return $this;
    }

    /**
     * @param String $tooltipFormat
     */
    public function setTooltipFormat($tooltipFormat){
        $this->tooltipFormat = $tooltipFormat;
    }


    /**
     * Hides the label of a series in a chart below a certain bar width
     *
     * @param int $barWidthPercent
     * @return self
     */
    public function hideLabelLengthDependent($barWidthPercent = 55){
        $this->hideLabelLength = $barWidthPercent;
        $this->hideLabelLengthDependent = true;
        return $this;
    }


    /**
     * @return string
     */
    public function render()
    {
        $download = $this->requestStack->getCurrentRequest()->query->get("chartDownload");
        if ($download) return $this->download($download);

        $data = $this->getViewData();

        $sums = array();
        foreach ($data['keys'] as $key => $info) {
            $sum = 0;
            foreach ($data['values'] as $x => $yFields) {
                $sum += $yFields[$key];
            }
            $sums[$key] = $sum;

        }

        return $this->templateEngine->render("@DGCChart/Charts/advanced_chart.html.twig", array(
            "data" => $data,
            "horizontal" => $this->horizontal,
            "containerWidth" => $this->containerWidth,
            "containerHeight" => $this->containerHeight,
            "sums" => $sums,
            "showData" => $this->showData,
            "showToolbar" => $this->showToolbar,
            "showDownload" => $this->showDownload,
            "showSplitLines" => $this->showSplitLines,
            "tooltipFormat" => $this->tooltipFormat,
            "labelFormat" => $this->labelFormat,
            "showXAxis" => $this->showXAxis,
            "showYAxis" => $this->showYAxis,
            "xAxisInterval" => $this->xAxisLabelInterval,
            "yAxisInterval" => $this->xAxisLabelInterval,
            "relativeScale" => $this->relativeScale,
            "language" => $this->requestStack->getCurrentRequest()->getLocale(),
            "seriesLabelPosition" => $this->seriesLabelPosition,
            "hideLabelLengthDependent" => $this->hideLabelLengthDependent,
            "hideLabelLength" => $this->hideLabelLength,
        ));
    }
}