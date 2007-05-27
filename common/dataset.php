<?php

// Datasets provide a unified way of accesing a list of records.
// Simple datasets provide an iterator interface and range selection while
// more complicated datasets may implement sorting and filtering.
 
// Base class for datasets
// All datasets implement iterator interfaces and provide range selection.
abstract class Dataset implements Iterator {
    protected $limitStart;
    protected $limitCount;

    function __construct() {
        $this->limitStart = null;
        $this->limitCount = null;
    }

    // Count all rows in dataset.
    abstract function count();

    // Range select
    function limit($start, $count) {
        $this->limitStart = $start;
        $this->limitCount = $count;
    }
}


// Sortable datasets
// Only sorting by a single field is currently supported.
interface Sortable {
    // Sort dataset by $field, ascendent (SORT_ASC) or descendent (SORT_DESC) 
    function sortBy($field, $direction = SORT_ASC);

    // Tells whether $field is a valid sortable field
    function isSortableField($field);
}


// Filterable datasets implement filtering rules for returned rows.
// Filters are applied before count() or limit()

// Filter types and their predicates
$FILTER_GRAMMAR = array(
    "numeric" => array("eq", "lt", "gt"),
    "string" => array("eq", "contains", "like"),
    "enum" => array("eq"),
    "datetime" => array("eq", "lt", "gt"),
);

interface Filterable {
    // Add new filter rule
    function addFilter($filter_field, $filter_predicate, $filter_value,
                       $negate = false);

    // Returns hash with available filter fields
    function availableFilters();

    // Returns a hash with all active filters
    function activeFilters();
}

?>
