<?php

// A dataset (see common/dataset.php) specialized in accessing the results
// of an SQL query.

require_once(IA_ROOT_DIR."common/dataset.php");
require_once(IA_ROOT_DIR."common/sqlmagic.php");

class SqlDataset extends Dataset implements Iterator, Sortable, Filterable {
    private $sqlQuery;
    private $queryParts;
    private $sqlResult;
    private $crtRow;
    private $crtKey;

    private $sortBy;
    private $sortDirection;
    private $sortableFields;

    private $filters;
    private $filterableFields;

    private $serverColumns;

    function __construct($sql_query) {
        parent::__construct();
        $this->sqlQuery = $sql_query;
        $this->sqlResult = null;
        $this->crtRow = null;
        $this->crtKey = null;
        $this->sortBy = null;
        $this->sortDirection = null;
        $this->filters = array();

        // parse query
        $this->queryParts = sqlmagic_parse($this->sqlQuery);

        // do query introspection
        $this->introspection();

        // collect sortable fields
        $this->sortableFields = array();
        foreach ($this->serverColumns as $column) {
            $this->sortableFields[] = $column->name;
        }

        // collect filterable fields
        $this->filterableFields = array_keys($this->availableFilters());
    }

    function count() {
        $query_parts = $this->sqlFilters($this->queryParts);
        $query_parts = sqlmagic_count($query_parts);
        $count = db_query_value(sqlmagic_compile($query_parts));

        log_print('sqldataset count: '.$count);

        return $count;
    }

    function isSortableField($field) {
        return (false !== array_search($field, $this->sortableFields));
    }

    function sortBy($field, $direction = SORT_ASC) {
        if (!$this->isSortableField($field)) {
            log_error("Unknown sort field: {$this->field}. Known sortable "
                      ."fields are: ".join(", ", $this->sortableFields));
        }
        $this->sortBy = $field;
        $this->sortDirection = $direction;
    }

    function rewind() {
        $query_parts = $this->queryParts;

        // Apply SQL clauses
        $query_parts = $this->sqlLimit($query_parts);
        $query_parts = $this->sqlOrder($query_parts);
        $query_parts = $this->sqlFilters($query_parts);

        // do query
        $query = sqlmagic_compile($query_parts);
        log_print('sqldataset query: '.$query);
        $this->sqlResult = db_query($query);

        $this->next();
    }

    // Internal; applies LIMIT clause 
    private function sqlLimit($query_parts) {
        if (!is_null($this->limitStart) || !is_null($this->limitCount)) {
            return sqlmagic_limit($query_parts, $this->limitStart,
                                  $this->limitCount);
        }
        else {
            return sqlmagic_remove_limit($query_parts);
        }
    }

    // Internal; applies ORDER clause
    private function sqlOrder($query_parts) {
        if (!is_null($this->sortBy)) {
            $sort_sql = " `{$this->sortBy}` ";
            if (SORT_DESC == $this->sortDirection)
                $sort_sql .= " DESC ";
            else
                $sort_sql .= " ASC ";
            $query_parts = sqlmagic_order_by($query_parts, $sort_sql);
        }

        return $query_parts;
    }

    // Internal; applies dataset filters
    private function sqlFilters($query_parts) {
        global $FILTER_GRAMMAR;

        if (0 == count($this->filters)) {
            return $query_parts;
        }

        $available_filters = $this->availableFilters();
        $sql_and = array();

        foreach ($this->filters as $filter) {
            $filter_field = $filter['field'];
            $filter_pred = $filter['predicate'];
            $filter_value = $filter['value'];
            $filter_type = $available_filters[$filter_field]['type'];
            $negate = $filter['negate'];

            $sql_expr = "`$filter_field` ";

            switch ($filter_type) {
            case 'string':
                // Escape '%' and '_', they have special meanings for op. LIKE
                $esc_value = str_replace('_', '\_',
                                         str_replace('%', '\%', $filter_value));
                if ('like' == $filter_pred) {
                    $filter_value = $esc_value.'%';
                    $op = ($negate ? 'NOT LIKE' : 'LIKE');
                }
                elseif ('contains' == $filter_pred) {
                    $filter_value = '%'.$esc_value.'%';
                    $op = ($negate ? 'NOT LIKE' : 'LIKE');
                }
                else {
                    $op = ($negate ? '!=' : '=');
                }
                $sql_expr .= $op." '".db_escape($filter_value)."'";
                break;

            case 'numeric':
                if (!is_numeric($filter_value)) {
                    // skip filter; invalid numeric value
                    continue;
                }

                $op_map = array('eq' => '=', 'lt' => '<', 'gt' => '>');
                $nop_map = array('eq' => '!=', 'lt' => '>=', 'gt' => '<=');
                $op = ($negate ? $nop_map[$filter_pred] : $op_map[$filter_pred]);
                $sql_expr .= $op." '".db_escape($filter_value)."'";
                break;

            case 'datetime':
                if (!db_date_parse($filter_value)) {
                    // skip filter; invalid date&/time value
                    continue;
                }

                $op_map = array('eq' => '=', 'lt' => '<', 'gt' => '>');
                $nop_map = array('eq' => '!=', 'lt' => '>=', 'gt' => '<=');
                $op = ($negate ? $nop_map[$filter_pred] : $op_map[$filter_pred]);
                $sql_expr .= $op." '".db_escape($filter_value)."'";
                break;

            case 'enum':
                $op = ($negate ? '!=' : '=');
                $sql_expr .= "$op '".db_escape($filter_value)."'";
                break;

            default:
                log_error('Filter not implemented: '.$filter_type);
            }   // end switch

            $sql_and[] = $sql_expr;
        }

        log_assert(0 != count($sql_and));

        if (count($sql_and) > 1) {
            $sql = '(' . join(') AND (', $sql_and) . ')';
        }
        else {
            $sql = $sql_and[0];
        }
        log_print('sqldataset filter expression: '.$sql);
        return sqlmagic_where_and($query_parts, $sql);
    }

    function next() {
        $this->crtRow = db_next_row($this->sqlResult);
        $this->crtKey = is_null($this->crtKey) ? 0 : $this->crtKey + 1;
        $this->doComputedFields($this->crtRow);
    }

    function key() {
        return $this->crtKey;
    }

    function current() {
        return $this->crtRow;
    }

    function valid() {
        return $this->crtRow && (bool)$this->sqlResult;
    }

    // Override this method in order to implement custom dataset fields
    protected function doComputedFields(&$row) {
        /* nothing */
    }

    // Perform query introspection.
    // Obtain information about fields and their data type.
    private function introspection() {
        // FIXME: Cache introspection
        log_print('Doing query introspection');

        $this->rewind();

        $columns = array();
        $count = mysql_num_fields($this->sqlResult);
        for ($i = 0; $i < $count; ++$i) {
            $columns[] = mysql_fetch_field($this->sqlResult, $i);
        }

        // log_print_r($columns);

        $this->serverColumns = $columns;
    }


    function addFilter($filter_field, $filter_predicate, $filter_value,
                       $negate = false) {
        static $available_filters = null;
        global $FILTER_GRAMMAR;

        if (is_null($available_filters)) {
            $available_filters = $this->availableFilters();
        }
        if (false === array_search($filter_field, $this->filterableFields)) {
            log_error('Invalid filter field: '.$filter_field.'. Available '
                      .' filter fields are: '
                      .join(', ', array_keys($available_filters)));
        }

        $valid_predicates = $FILTER_GRAMMAR[$available_filters[$filter_field]['type']];
        if (false === array_search($filter_predicate, $valid_predicates)) {
            log_error('Invalid filter predicate for field '.$filter_field.': '
                      .$filter_predicate.'. Valid predicates are: '
                      .join(', ', $valid_predicates));
        }

        $this->filters[] = array(
            'field' => $filter_field,
            'predicate' => $filter_predicate,
            'value' => $filter_value,
            'negate' => $negate,
        );
    }

    function availableFilters() {
        $collect = array();
        foreach ($this->serverColumns as $column) {
            if ('int' == $column->type || 'real' == $column->type) {
                $type = 'numeric';
            }
            elseif ('string' == $column->type) {
                $type = 'string';
            }
            elseif ('datetime' == $column->type) {
                $type = 'datetime';
            }
            else {
                // unknown field type
                continue;
            }

            $collect[$column->name] = array(
                'name' => $column->name,
                'type' => $type
            );
        }

        return $collect;
    }

    function activeFilters() {
        return $this->filters;
    }
}

?>
