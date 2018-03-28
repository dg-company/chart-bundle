<?php

namespace DGC\ChartBundle\Chart;

use DGC\ChartBundle\Aggregator\MongoAggregator;
use DGC\ChartBundle\Aggregator\SqlAggregator;

class ListChart extends AbstractChart
{

    /** @var null */
    protected $viewData = null;

    /** @var null */
    protected $exportFields = null;

    /** @var string */
    protected $sortOrder = 'DESC';

    /** @var string */
    protected $sortBy = null;

    /**
     * generates csv output
     */
    public function download()
    {
        $data = $this->getViewData();
        $csvData = array();

        //header
        foreach ($data['keys'] as $key) {
            if ((!in_array($key['field'], $this->exportFields))) continue; //filter

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
            foreach ($row as $key => $value) {
                $key = substr($key, 2); //remove index chars
                if ((!in_array($key, $this->exportFields))) continue;
                $rowFiltered[$key] = $value;
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
    public function setExportFields($exportFields)
    {
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
     * sorts the list fields according to url parameters
     */

    private function sort()
    {

        $this->sortBy = $this->requestStack->getCurrentRequest()->get('sort_by');

        if ($this->requestStack->getCurrentRequest()->get('sort_order')) {
            $this->sortOrder = $this->requestStack->getCurrentRequest()->get('sort_order');
        }

        foreach ($this->aggregators as $aggregator) {

            if ($this->sortBy) {
                if ($aggregator instanceof SqlAggregator) {

                    $aggregator
                        ->getQueryBuilder()
                        ->OrderBy($this->sortBy, $this->sortOrder);

                } elseif ($aggregator instanceof MongoAggregator) {

                    $aggregator->sort($this->sortBy, $this->sortOrder);
                }

                if ($this->sortOrder == 'ASC') {
                    $this->sortOrder = 'DESC';
                } else {
                    $this->sortOrder = 'ASC';
                }
            }
        }
    }


    /**
     * @return string
     */
    public function render()
    {
        $this->sort();

        $data = $this->getViewData();

        $data["sort_order"] = $this->sortOrder;
        $data["sort_by"] = $this->sortBy;

        $values = $data["values"];
        $place = 1;

        $valuesNew = array();
        foreach ($values as $valueName => $value) {
            //shorten value name
            $limit = 50;
            if (strlen($valueName) > $limit) {
                $valueName = substr($valueName, 0, $limit) . "...";
            }

            $value["place"] = $place;
            $place++;
            $valuesNew[$valueName] = $value;
        }


        $data["values"] = $valuesNew;


        return $this->templateEngine->render("@DGCChart/Charts/listchart.html.twig", array(
            "data" => $data,
        ));
    }
}