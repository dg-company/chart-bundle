<?php

namespace DGC\ChartBundle\Filter;

use Doctrine\DBAL\Query\QueryBuilder as QueryBuilder;

class DateRangeFilter extends AbstractFilter
{
    /** @var \DateTime */
    protected $startDate;

    /** @var \DateTime */
    protected $endDate;

    /** @var string */
    protected $sqlField;
    protected $sqlFieldRangeEnd = null;

    /** @var string */
    protected $mongoField;

    protected $dateFormat = "Ymd";

    /**
     * @param \DateTime $startDate
     * @return self
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate=$startDate;
        return $this;
    }

    /**
     * @param \DateTime $endDate
     * @return self
     */
    public function setEndDate(\DateTime $endDate)
    {
        $endDate = clone $endDate;
        $this->endDate = $endDate->modify('+1 day');
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param string $dateFormat
     * @return self
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
        return $this;
    }

    /**
     * @param string $sqlField
     * @return DateRangeFilter
     */
    public function setSqlField($sqlField)
    {
        $this->sqlField = $sqlField;
        return $this;
    }

    /**
     * @param $sqlField
     * @return $this
     */
    public function setSqlFieldRangeEnd($sqlField)
    {
        $this->sqlFieldRangeEnd = $sqlField;
        return $this;
    }

    /**
     * @param string $mongoField
     * @return DateRangeFilter
     */
    public function setMongoField($mongoField)
    {
        $this->mongoField = $mongoField;
        return $this;
    }




    /**
     * @param string|null $filterField
     * @return array|null
     */
    public function getMongoFilterQuery($filterField)
    {
        $requestParameters = $this->getRequestParameters();

        $startDate = isset($requestParameters['start'])?(new \DateTime($requestParameters['start'])):$this->startDate;
        $endDate = isset($requestParameters['end'])?(new \DateTime($requestParameters['end'])):$this->endDate;

        if (!$startDate OR !$endDate) return null; //do not use this filter

        $startDate->setTimezone(new \DateTimeZone("UTC"));
        $endDate->setTimezone(new \DateTimeZone("UTC"));

        if (!$filterField) $filterField = $this->mongoField;
        
        return array(
            $filterField => array(
                '$gte' => new \MongoDate($startDate->getTimestamp()),
                '$lte' => new \MongoDate($endDate->getTimestamp()),
            )
        );
    }

    /**
     * @param string|null $filterField
     * @param QueryBuilder $qb
     * @return null
     */
    public function updateSqlQuery(QueryBuilder $qb, $filterField)
    {
        $requestParameters = $this->getRequestParameters();

        $startDate = isset($requestParameters['start'])?(new \DateTime($requestParameters['start'])):$this->startDate;
        $endDate = isset($requestParameters['end'])?(new \DateTime($requestParameters['end'])):$this->endDate;

        if (!$startDate OR !$endDate) return null; //do not use this filter

        $rangeEndField = $this->sqlFieldRangeEnd;

        if ($filterField) {
            if (is_array($filterField)) {
                $filterField = $filterField['start'];
                $rangeEndField = $filterField['end'];
            }
        } else {
            $filterField = $this->sqlField;
        }

        if($rangeEndField != null){
            $qb->andWhere($filterField."<=".$endDate->format($this->dateFormat)." AND (".$this->sqlFieldRangeEnd.">=".$startDate->format($this->dateFormat)." OR (isnull(".$rangeEndField.")))");
        }else{
            $qb->andWhere($filterField." >= '".$startDate->format($this->dateFormat)."'");
            $qb->andWhere($filterField." <= '".$endDate->format($this->dateFormat)."'");
        }
    }

    /**
     * @return string
     */
    public function render()
    {
        $requestParameters = $this->getRequestParameters();

        $startDate = isset($requestParameters['start'])?(new \DateTime($requestParameters['start'])):$this->startDate;
        $endDate = isset($requestParameters['end'])?(new \DateTime($requestParameters['end'])):$this->endDate;

        return $this->templateEngine->render("@DGCChart/Filters/date_range_filter.html.twig", array(
            "id" => $this->getId(),
            "startDate" => $startDate,
            "endDate" => $endDate,
        ));
    }

}