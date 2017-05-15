<?php

namespace DGC\ChartBundle\Chart;

class ListChart extends AbstractChart
{
    /** @var bool */
    protected $compact = false;

    /** @var null */
    protected $viewData = null;

    /** @var null */
    protected $exportFields = null;


    /**
     * generates csv output
     */
    public function download()
    {
        $data = $this->getViewData();
        $csvData = array();

        //header
        foreach ($data['keys'] as $key) {
            if( (!in_array($key['field'],$this->exportFields)) ) continue; //filter

            if (isset($key['label'])) {
                $header[] = $key['label'];
            } else {
                $header[] = "";
            }
        }

        $csvData[] = $header;

        //data
        foreach ($data['values'] as $key => $row) {

            //filter row
            $rowFiltered = array();
            foreach($row as $key => $value){
                $key = substr($key,2); //remove index chars
                if( (!in_array($key,$this->exportFields)) ) continue;
                $rowFiltered[$key]=$value;
            }
            $csvData[] = array_values($rowFiltered);
        }

        //Output
        header('Content-type: text/comma-separated-values');
        header('Content-Disposition: attachment; filename="' . date("Ymd") . '_' . $this->title . '.csv"');

        foreach ($csvData as $lineIndex => $line) {
            echo '"' . implode('";"', str_replace('"', '', $line)) . '"' . "\n";
        }

        die();
    }

    /**
     * setDownloadFields set fields for excel export
     *
     * @param $exportFields
     */
    public function setExportFields($exportFields){
        $this->exportFields = $exportFields;
    }


    /**
     * @return string
     */
    public function getViewData()
    {
        if ($this->viewData !== null) return $this->viewData;
        $this->viewData = parent::getViewData();
        return $this->viewData;
    }


    /**
     * Enable compact design
     *
     * @param bool $compact
     * @return self
     */
    public function setCompact($compact = true)
    {
        $this->compact = $compact;
        return $this;
    }


    /**
     * @return string
     */
    public function render()
    {
        $data = $this->getViewData();

        $values = $data["values"];
        $place = 1;

        $valuesNew = array();
        foreach($values as $valueName => $value){
            //shorten value name
            $limit = 50;
            if(strlen($valueName)>$limit){
                $valueName = substr($valueName,0,$limit) . "...";
            }

            $value["place"]=$place;
            $place++;
            $valuesNew[$valueName]=$value;
        }

        $data["values"] = $valuesNew;

        return $this->templateEngine->render("DGCChartBundle:Charts:listchart.html.twig", array(
            "data" => $data,
            "compact" => $this->compact,
        ));
    }
}