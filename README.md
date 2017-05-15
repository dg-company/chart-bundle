# ChartBundle

Bundle to create charts from SQL or MongoDB database queries

## Requirements

- PHP 5.6+
- Doctrine ORM and/or Doctine ODM
- Symfony2+

## Installation

Request bundle using composer

    composer require dgc/chart-bundle
    
Add ChartBundle to your AppKernel.php

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = [
                ...
    
                new DGC\ChartBundle\DGCChartBundle(),
    
                new AppBundle\AppBundle(),
            ];
            ...
    
Add JavaScript and CSS dependencies to your views

    ...
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        
        {% include '@DGCChart/Includes/lib_daterangepicker.html.twig' %}
        {% include '@DGCChart/Includes/lib_echarts.html.twig' %}
        {% include '@DGCChart/Includes/lib_morris.html.twig' %}
    </head>
    ...
    
## Add Charts

TestController.php

    // Create a new aggregator
    $aggregator = $this->get('dgc_chart.factory.aggregator')->createSqlAggregator();
    
    // Build the query
    $aggregator
        ->setDatabaseConnection($this->get("doctrine.dbal.ext_connection"))
        ->getQueryBuilder()
        ->select('`date`, orders, clients')
        ->from('stats')
        ->orderBy('date', 'DESC')
    ;
    
    // Set which fields will be used for X and Y values 
    $aggregator
        ->setXField('date')
        ->addYField('orders', 'Number of orders')
        ->addYField('clients', 'Number of clients')
        ->addResultCallback(function ($data) {
            return $data; //optional: manipulate data used to render the chart
        })
    ;

    // Optionally create and add filters
    $filter = $this->get("dgc_chart.factory.filter")->createDateRangeFilter("test");
    $filter->setSqlField('date');

    // Create the chart
    $chart = $this->get('dgc_chart.factory.chart')->createAdvancedChart('test');
    $chart
        ->setTitle("Just a test")
        ->addAggregator($aggregator)
        ->addFilter($filter)
    ;

    return $this->render('@App/Test/index.html.twig', array(
        'chart' => $chart->render(),
        'filter' => $filter->render()
    ));
    
index.html.twig

    {{ filter|raw }}
    {{ chart|raw }}
    
## Chart types

- AdvancedChart (eCharts)
- LineChart (Morris.js)
- PieChart (Morris.js)
- BarChart (Morris.js)

### AdvancedChart

    $chart = $this->get('dgc_chart.factory.chart')->createAdvancedChart('test');
    $chart
        ->setHeight(...)
        ->setWidth(...)
        ->setLabelFormat(...)
        ->setSeriesLabelPosition(...)
        ->setXAxisLabelInterval(...)
        ->setYAxisLabelInterval(...)
        ->setRelativeScale(...)
        ->setHorizontal(...)
        ->showXAxis(...)
        ->showYAxis(...)
        ->showToolbar(...)
        ->showData(...)
        ->showDownload(...)
        ->showSplitLines(...)
        ->setTooltipFormat(...)
        ->hideLabelLengthDependent(...)
    
        ->setTitle("Just a test")
        ->addAggregator($aggregator)
        ->addFilter($filter)
    ;
    
### LineChart

    $chart = $this->get('dgc_chart.factory.chart')->createLineChart('test');
    $chart
        ->setSmooth(...)        
    
        ->setTitle("Just a test")
        ->addAggregator($aggregator)
        ->addFilter($filter)
    ;

### PieChart

    $chart = $this->get('dgc_chart.factory.chart')->createPieChart('test');
    $chart
        ->setStartAngle()
        ->formatTooltip()
        ->setThresholdDisplayPieSlice()
        ->setThresholdDisplayPieSliceDescription()        
    
        ->setTitle("Just a test")
        ->addAggregator($aggregator)
        ->addFilter($filter)
    ;

### BarChart

    $chart = $this->get('dgc_chart.factory.chart')->createBarChart('test');
    $chart
        ->setTitle("Just a test")
        ->addAggregator($aggregator)
        ->addFilter($filter)
    ;
    
## Filter

- Date range filter
- Number range filter
- Reference filter (choices)

### Date range filter

    $filter = $this->get("dgc_chart.factory.filter")->createDateRangeFilter("test");
    $filter
        ->setStartDate(...)
        ->setEndDate(...)
        ->setDateFormat(...)
        ->setSqlField(...)
        ->setSqlFieldRangeEnd(...)
        ->setMongoField(...)
    ;
    
## Number range filter

    $filter = $this->get("dgc_chart.factory.filter")->createNumberRangeFilter("test");
    $filter
        ->setLabel(...)
        ->setUnit(...)
        ->setMin(...)
        ->setMax(...)
        ->setRangeMin(...)
        ->setRangeMax(...)
        ->setMongoField(...)
        ->setSqlField(...)
    ;

## Reference filter (choices)

    $filter = $this->get("dgc_chart.factory.filter")->createReferenceFilter("test");
    $filter
        ->setReference(...)
        ->setChoices(...)
        ->setLabel(...)
        ->setMongoField(...)
        ->addCustomWhereClause(...)
    ;