<?php

namespace DGC\ChartBundle\Filter;

use Doctrine\DBAL\Query\QueryBuilder as QueryBuilder;


class ReferenceFilter extends AbstractFilter
{
    /** @var string */
    protected $reference;

    /** @var array */
    protected $choices = array();

    /** @var $string */
    protected $label = '';

    /** @var string */
    protected $sqlField;

    /** @var  string */
    protected $customWhereClause = null;

    /** @var string */
    protected $mongoField;


    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return ReferenceFilter
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param $choices
     * @return mixed
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed string
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }


    /**
     * @param string $sqlField
     * @return self
     */
    public function setSqlField($sqlField)
    {
        $this->sqlField = $sqlField;
        return $this;
    }

    /**
     * @param string $mongoField
     * @return self
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

        $reference = isset($requestParameters['reference']) ? $requestParameters['reference'] : $this->reference;

        if (!$reference) return null; //do not use this filter


        return array(
            '$match' => array(
                $this->mongoField => $reference
            )
        );
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $filterField
     * @return null
     */
    public function updateSqlQuery(QueryBuilder $qb, $filterField)
    {

        if ($this->customWhereClause) {
            $qb->andWhere(":customWhereClause");
            $qb->setParameter('customWhereClause', $this->customWhereClause);

        } else {

            $requestParameters = $this->getRequestParameters();
            $reference = isset($requestParameters['reference']) ? $requestParameters['reference'] : $this->reference;

            if (!$reference) return null; //do not use this filter
            $qb->andWhere("$this->sqlField = :reference");
            $qb->setParameter('reference', $reference);

        }

        return $this;
    }

    public function addCustomWhereClause($whereClause)
    {
        $this->customWhereClause = $whereClause;

        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        $requestParameters = $this->getRequestParameters();

        $reference = isset($requestParameters['reference']) ? $requestParameters['reference'] : $this->reference;

        return $this->templateEngine->render("@DGCChart/Filters/reference_filter.html.twig", array(
            "id" => $this->getId(),
            "label" => $this->getLabel(),
            "reference" => $reference,
            "choices" => $this->getChoices()
        ));
    }

}