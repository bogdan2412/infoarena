<?php

require_once(IA_ROOT_DIR . "www/format/table.php");
require_once(IA_ROOT_DIR . "www/format/pager.php");
require_once(IA_ROOT_DIR . "common/db/round.php");

function format_title($row) {
    $title = "<span>" . format_link(url_textblock($row["page_name"]), $row["title"]) . "</span>";
    return $title;
}

function format_user_id($row) {
    return format_user_tiny($row["user_name"], $row["user_fullname"], $row["user_rating"]);
}

function format_status($status) {
    return '<span title="' . $status . '" class="round-' . $status . '"></span>';
}

function format_start_date($start_date) {
    if (is_null($start_date))
        return "Data de inceput nu a fost setata.";
    else
        return format_date($start_date);
}

// Lists all rounds with filters
//
// Arguments;
//      type (optional)         Round type - classic/archive/user-defined
//      name (optional)         Name regexp
//      order (optional)        ASC/DESC - Order of rounds
//      show_authors (optional) Show round author
//
// Examples:
//      RoundList(type="user-defined")
function macro_roundlist($args) {
    $options = pager_init_options($args);
    $options['show_count'] = getattr($args, 'show_count', true);
    $options['show_display_entries'] = getattr($args, 'show_display_entries', true);

    $filters = array();
    $filters["limit"] = (int)$options["display_entries"];
    $filters["offset"] = (int)$options["first_entry"];

    $show_authors = getattr($args, "show_authors", false);
    if ($show_authors) {
        $filters["username"] = true;
    }

    if (getattr($args, "type")) {
        $filters["type"] = $args["type"];
    }

    if (getattr($args, "name")) {
        $filters["name_regexp"] = $args["name"];
    }

    // Get rounds
    $filters["get_count"] = true;
    $rounds = round_get_many($filters);
    $options["total_entries"] = (int)$rounds["count"];
    unset($rounds["count"]);

    $options["css_class"] = "fill-screen rounds";

    $column_infos = array();
    $column_infos[] = array(
        'title' => 'Titlul concursului',
        'css_class' => 'round',
        'rowform' => 'format_title'
    );
    if ($show_authors) {
        $column_infos[] = array(
            'title' => 'Autor',
            'css_class' => 'author',
            'rowform' => 'format_user_id',
        );
    }
    $column_infos[] = array(
        'title' => 'Stare',
        'css_class' => 'state',
        'key' => 'state',
        'valform' => 'format_status',
    );
    $column_infos[] = array(
        'title' => 'Data de inceput',
        'css_class' => 'start_time',
        'key' => 'start_time',
        'valform' => 'format_start_date',
    );

    return format_table($rounds, $column_infos, $options);
}

?>

