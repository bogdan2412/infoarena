<?php

// Provides a bunch of magic functions you can use to transform SQL queries.

// Token types
define("SQL_TOK_UNKNOWN", 1);
define("SQL_TOK_PAREN_O", 2);
define("SQL_TOK_PAREN_C", 4);
define("SQL_TOK_STRING", 8);
define("SQL_TOK_COMMENT", 16);
define("SQL_TOK_WSPACE", 32);
define("SQL_TOK_KEYWORD", 64);
define("SQL_ALL_TOKENS", (1 << 10) - 1);

// Parse SQL query and split it into several parts.
//
// NOTE: Parsing preserves everything, including whitespace, comments,
//       new lines etc. At any time, this should be invariant:
//       >> sqlmagic_compile(sqlmagic_parse($query)) == $query <<
function sqlmagic_parse($sql_query) {
    $tokens = sqlmagic_tokens($sql_query);

    // find query predicate (SELECT / UNION / UPDATE ... )
    $k = sqlmagic_find($tokens, 0, SQL_TOK_KEYWORD);
    if ($k >= $tokens['count']) {
        log_error("Cannot find query predicate");
    }
    $predicate = $tokens['values'][$k];

    // Dispatch specialized parser
    if (!strcasecmp($predicate, "SELECT")) {
        // SELECT statement
        return sqlmagic_parse_select($tokens);
    }
    else {
        log_error("sqlmagic does not support query predicate '{$predicate}'");
    }
}

// Parse SELECT statement and split it into parts.
//
// Here's how the output looks like:
// array(
//     "predicate" => i.e. "SELECT", "SELECT DISTINCT",
//                         "SELECT STRAIGHT_JOIN" etc.
//     "columns" => i.e. "*" or "LENGTH(foo) + 13 AS bar, second_field"
//     "from" => i.e. "FROM table1, table2 LEFT JOIN table3 ON ..."
//     "where" => i.e. "WHERE this AND that"
//     "having" => i.e. "HAVING something"
//     "order" => i.e. "ORDER BY foo DESC, bar"
//     "limit" => i.e. "LIMIT 10, 24", "LIMIT 10"
// );
function sqlmagic_parse_select($tokens) {
    $output_parts = array("type" => "select");
    $collect_parts = array("FROM", "WHERE", "HAVING", "ORDER", "LIMIT",
                           "*/BORDER\*");

    // some handy references
    $ttypes = $tokens['types'];
    $tvalues = $tokens['values'];
    $count = $tokens['count'];

    // collect predicate
    //  - find SELECT
    $u = sqlmagic_find($tokens, 0, SQL_TOK_KEYWORD);
    log_assert(!strcasecmp('SELECT', $tvalues[$u]));
    //  - find any keywords after select ( DISTINCT, SQL_CALC_FOUND_ROWS ... )
    $u = sqlmagic_find($tokens, $u,
                       SQL_ALL_TOKENS & ~SQL_TOK_KEYWORD & ~SQL_TOK_WSPACE);
    log_assert($u < $count, "Cannot process this query. There seems to be "
                            ."nothing after SELECT <keyword-list>.");
    $output_parts['predicate'] = implode("", array_slice($tvalues, 0, $u));

    // collect columns
    $v = sqlmagic_find($tokens, $u, SQL_TOK_KEYWORD, $collect_parts);
    $output_parts['columns'] = implode("", array_slice($tvalues, $u, $v - $u));
    $u = $v;

    // collect rest of query parts
    foreach ($collect_parts as $part) {
        $part = array_shift($collect_parts);
        if ($u >= $count || strcasecmp($part, $tvalues[$u])) {
            continue;
        }

        // collecting $part ...
        $v = sqlmagic_find($tokens, $u, SQL_TOK_KEYWORD, $collect_parts);
        $output_parts[strtolower($part)] =
            implode("", array_slice($tvalues, $u, $v - $u));
        $u = $v;
    }

    return $output_parts;
}

// Compile SQL parts into query string.
//
// NOTE: Compiling and parsing should preserve everything. This is invariant:
//       >> sqlmagic_compile(sqlmagic_parse($query)) == $query <<
function sqlmagic_compile($query_parts) {
    switch ($query_parts['type']) {
        case 'select':
            return sqlmagic_compile_select($query_parts);

        default:
            log_error("sqlmagic cannot compile query parts of type "
                      ."'{$query_parts['type']}'");
    }
}

// Compile SELECT statement
function sqlmagic_compile_select($query_parts) {
    $parts_order = array("predicate", "columns", "from", "where", "having",
                         "order", "limit");
    $sql = "";
    foreach ($parts_order as $part) {
        $sql .= getattr($query_parts, $part, "");
    }

    return $sql;
}

// Split SQL query into tokens. Returns array with 3 elements:
// array(
//     'types' => <array of token types>,
//     'values' => <array of token values>,
//     'count' => <number of tokens>
// )
//
// NOTE: We're only interested in determining a few types of tokens,
//       the minimum necessary for our sql magic. 
//       Imploding tokenizer output should always yield initial query.
function sqlmagic_tokens($sql_query) {
    // PHP requires escaped backslashes inside single/heredoc quotes.
    // If you need \\ inside the regular expression (a single \ in the
    // matching string), you have to put \\\\ inside the PHP string literal.
    $token_re = '
        (?P<paren_o>           # open parenthesis
            [\(]
        )
        | (?P<paren_c>         # closed parenthesis
            [\)]
        )
        | (?P<str_dq>          # double quoted string literals
            "(?: [^"\\\\] | \\\\. | "" )*"
        )
        | (?P<str_sq>          # single quoted string literals
            \'(?: [^\'\\\\] | \\\\. | \'\' )*\'
        )
        | (?P<cmnt_d>          # double dash comments
            --[\ \t\S]*
        )
        | (?P<cmnt_c>          # C comments
            /\*[\ \t\n\S]*?\*/
        )
        | (?P<ws>              # whitespace
            [\t\ \n\r]+
        )
        | (?P<any>             # anything else
            [^\s\(\)]+
        )';

    // mysql reserved words
    // Reference: http://dev.mysql.com/doc/refman/5.0/en/reserved-words.html
    $keywords = array(
        "ADD", "ALL", "ALTER", "ANALYZE", "AND", "AS", "ASC", "ASENSITIVE",
        "BEFORE", "BETWEEN", "BIGINT", "BINARY", "BLOB", "BOTH", "BY", "CALL",
        "CASCADE", "CASE", "CHANGE", "CHAR", "CHARACTER", "CHECK", "COLLATE",
        "COLUMN", "CONDITION", "CONSTRAINT", "CONTINUE", "CONVERT", "CREATE",
        "CROSS", "CURRENT_DATE", "CURRENT_TIME", "CURRENT_TIMESTAMP",
        "CURRENT_USER", "CURSOR", "DATABASE", "DATABASES", "DAY_HOUR",
        "DAY_MICROSECOND", "DAY_MINUTE", "DAY_SECOND", "DEC", "DECIMAL",
        "DECLARE", "DEFAULT", "DELAYED", "DELETE", "DESC", "DESCRIBE",
        "DETERMINISTIC", "DISTINCT", "DISTINCTROW", "DIV", "DOUBLE", "DROP",
        "DUAL", "EACH", "ELSE", "ELSEIF", "ENCLOSED", "ESCAPED", "EXISTS",
        "EXIT", "EXPLAIN", "FALSE", "FETCH", "FLOAT", "FLOAT4", "FLOAT8",
        "FOR", "FORCE", "FOREIGN", "FROM", "FULLTEXT", "GRANT", "GROUP",
        "HAVING", "HIGH_PRIORITY", "HOUR_MICROSECOND", "HOUR_MINUTE",
        "HOUR_SECOND", "IF", "IGNORE", "IN", "INDEX", "INFILE", "INNER",
        "INOUT", "INSENSITIVE", "INSERT", "INT", "INT1", "INT2", "INT3",
        "INT4", "INT8", "INTEGER", "INTERVAL", "INTO", "IS", "ITERATE", "JOIN",
        "KEY", "KEYS", "KILL", "LEADING", "LEAVE", "LEFT", "LIKE", "LIMIT",
        "LINES", "LOAD", "LOCALTIME", "LOCALTIMESTAMP", "LOCK", "LONG",
        "LONGBLOB", "LONGTEXT", "LOOP", "LOW_PRIORITY", "MATCH", "MEDIUMBLOB",
        "MEDIUMINT", "MEDIUMTEXT", "MIDDLEINT", "MINUTE_MICROSECOND",
        "MINUTE_SECOND", "MOD", "MODIFIES", "NATURAL", "NOT",
        "NO_WRITE_TO_BINLOG", "NULL", "NUMERIC", "ON", "OPTIMIZE", "OPTION",
        "OPTIONALLY", "OR", "ORDER", "OUT", "OUTER", "OUTFILE", "PRECISION",
        "PRIMARY", "PROCEDURE", "PURGE", "RAID0", "READ", "READS", "REAL",
        "REFERENCES", "REGEXP", "RELEASE", "RENAME", "REPEAT", "REPLACE",
        "REQUIRE", "RESTRICT", "RETURN", "REVOKE", "RIGHT", "RLIKE", "SCHEMA",
        "SCHEMAS", "SECOND_MICROSECOND", "SELECT", "SENSITIVE", "SEPARATOR",
        "SET", "SHOW", "SMALLINT", "SONAME", "SPATIAL", "SPECIFIC", "SQL",
        "SQL_BIG_RESULT", "SQL_CALC_FOUND_ROWS", "SQLEXCEPTION",
        "SQL_SMALL_RESULT", "SQLSTATE", "SQLWARNING", "SSL", "STARTING",
        "STRAIGHT_JOIN", "TABLE", "TERMINATED", "THEN", "TINYBLOB", "TINYINT",
        "TINYTEXT", "TO", "TRAILING", "TRIGGER", "TRUE", "UNDO", "UNION",
        "UNIQUE", "UNLOCK", "UNSIGNED", "UPDATE", "USAGE", "USE", "USING",
        "UTC_DATE", "UTC_TIME", "UTC_TIMESTAMP", "VALUES", "VARBINARY",
        "VARCHAR", "VARCHARACTER", "VARYING", "WHEN", "WHERE", "WHILE", "WITH",
        "WRITE", "X509", "XOR", "YEAR_MONTH", "ZEROFILL");

    // extract tokens
    $count = preg_match_all("{".$token_re."}ix", $sql_query, $matches);
    //  - translate capture names into token types
    $ttrans = array(
        "any" => SQL_TOK_UNKNOWN,
        "paren_o" => SQL_TOK_PAREN_O,
        "paren_c" => SQL_TOK_PAREN_C,
        "str_dq" => SQL_TOK_STRING,
        "str_sq" => SQL_TOK_STRING,
        "cmnt_d" => SQL_TOK_COMMENT,
        "cmnt_c" => SQL_TOK_COMMENT,
        "ws" => SQL_TOK_WSPACE,
    );
    //  - Re-organize $matches into a more useful form
    $token_types = array_fill(0, $count, null);
    $token_values = array_fill(0, $count, null);
    foreach ($matches as $token_type => $match) {
        if (is_numeric($token_type)) {
            continue;
        }
        foreach ($match as $pos => $value) {
            if (strlen($value)) {
                $token_types[$pos] = $ttrans[$token_type];
                $token_values[$pos] = $value;
            }
        }
    }

    // some tokens are reserved keywords
    $keyword_hash = array_combine($keywords, $keywords);
    for ($i = 0; $i < $count; ++$i) {
        if (SQL_TOK_UNKNOWN == $token_types[$i]
                && isset($keyword_hash[strtoupper($token_values[$i])])) {

            $token_types[$i] = SQL_TOK_KEYWORD;
        }
    }

    return array('types' => $token_types, 'values' => $token_values,
                 'count' => count($token_types));
}

// Find next token starting from $offset with mask in $token_mask.
// Returns token position (integer) or $tokens['count'] if no such
// token was found.
//
// When given $values as an array, it will only accept those token values.
// NOTE: sqlmagic_find skips everything inside parentheses. It only searches
//       for tokens in the top-level scope.
function sqlmagic_find($tokens, $offset, $token_mask, $values = null) {
    // some handy references
    $count = $tokens['count'];
    $ttypes = $tokens['types'];
    $tvalues = $tokens['values'];

    if ($values) {
        $value_hash = array_combine(array_map("strtoupper", $values), $values);
    }

    for ($depth = 0; $offset < $count; ++$offset) {
        $ttype = $ttypes[$offset];
        if (SQL_TOK_PAREN_O == $ttype) {
            ++$depth;
            continue;
        }
        if (SQL_TOK_PAREN_C == $ttype) {
            --$depth;
            log_assert($depth >= 0, "Invalid nested brackets");
            continue;
        }

        if (0 != $depth) {
            // we're only interested in top-level tokens
            continue;
        }

        if (0 == ($ttypes[$offset] & $token_mask)) {
            // doesn't match token mask
            continue;
        }
        if ($values && !isset($value_hash[strtoupper($tvalues[$offset])])) {
            // doesn't match token value
            continue;
        }

        return $offset;
    }

    log_assert(0 == $depth, "Invalid nested brackets");
    return $offset;
}


// Now comes a bunch of function you can use to transform sql parts

function sqlmagic_limit($query_parts, $start, $count) {
    log_assert(!is_null($count));

    if (is_null($start)) {
        $query_parts['limit'] = sprintf(" LIMIT %d ", $count);
    }
    else {
        $query_parts['limit'] = sprintf(" LIMIT %d, %d ", $start, $count);
    }

    return $query_parts;
}

function sqlmagic_remove_limit($query_parts) {
    if (isset($query_parts['limit']))
        unset($query_parts['limit']);
    return $query_parts;
}

function sqlmagic_count($query_parts) {
    $query_parts['columns'] = " COUNT(*) ";
    return $query_parts;
}

function sqlmagic_order_by($query_parts, $sql_order_clause) {
    $query_parts['order'] = ' ORDER BY '.$sql_order_clause;
    return $query_parts;
}

function sqlmagic_remove_order_by($query_parts) {
    if (isset($query_parts['order'])) {
        unset($query_parts['order']);
    }
    return $query_parts;
}

// Insert $sql_expr in WHERE expression with an AND modifier
function sqlmagic_where_and($query_parts, $sql_expr) {
    $where = getattr($query_parts, 'where', '');
    if ($where) {
        $where = '('.substr($where, strlen('WHERE')).')';
        $sql_expr = $where.' AND ('.$sql_expr. ')';
    }

    // FIXME: ' WHERE' because of the leading space, you cannot call
    //         sqlmagic_where_and() multiple times.
    $query_parts['where'] = ' WHERE '.$sql_expr.' ';

    return $query_parts;
}

?>
