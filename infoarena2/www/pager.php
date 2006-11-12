<?php
// This file contains various paging functions.
//
// A pager_options hash contains the following members:
//  * pager_style       : Pager style. none or standard.
//  * first_entry       : First entry to display.
//  * display_entries   : How many entries are displayed at once.
//  * total_entries     : Total number of entries.
//  * url_args          : Base arguments for the url function. Defaults to _GET.
//  * param_prefix      : Prefix for url parameters.
//
// Certain style might have their own additional args:
//  * style = standard:
//      * surround_pages: How many pages to display around the current page. Defaults to 5.
//
// FIXME: pager_valid_options

// Checks if the string is a valid page style.
// standard and none right now.
function pager_valid_style($style)
{
    return $style == 'none' || $style == 'standard';
}

// Initialize paging options.
// Returns a pager_options hash.
//
// $args contains default values.
//
// Things like first_entry are read from http request, or from args.
// $args['param_prefix'] works as expected.
//
// Feel free to use it from a macro.
function pager_init_options($args = null)
{
    $prefix = getattr($args, "param_prefix", "");

    // How many entries to display at a time.
    // FIXME: user preference.
    $display_entries = request($prefix . 'display_entries', getattr($args, 'display_entries', IA_PAGER_DEFAULT_DISPLAY_ENTRIES));
    if (preg_match('/^[0-9]*$/', $display_entries) == false ||
            $display_entries < IA_PAGER_MIN_DISPLAY_ENTRIES ||
            $display_entries > IA_PAGER_MAX_DISPLAY_ENTRIES) {
        $display_entries = IA_PAGER_DEFAULT_DISPLAY_ENTRIES;
        log_warn("Bad display_entries");
    }

    // First entry.
    $first_entry = request($prefix . 'first_entry', getattr($args, 'first_entry', 0));
    if (preg_match('/^[0-9]+$/', $first_entry) == false || $first_entry < 0) {
        $first_entry = 0;
        log_warn("Bad first_entry");
    }

    // Pager style.
    $pager_style = request($prefix . 'pager_style', getattr($args, 'pager_style', 'standard'));
    if (!pager_valid_style($pager_style)) {
        $pager_style = 'standard';
        log_warn("Bad pager_style");
    }

    return array(
            'display_entries' => $display_entries,
            'pager_style' => $pager_style,
            'first_entry' => $first_entry,
    );
}

// Format pager.
function format_pager($options)
{
    log_print_r($options);
    if ($options['pager_style'] == 'standard') {
        return "<span class=\"standard-pager\">" . format_standard_pager($options) . '</span>';
    } else {
        return '';
    }
}

// Formats a standard pager. Used by format_table.
function format_standard_pager($options)
{
    $first_entry = getattr($options, 'first_entry', 0);
    $total_entries = $options['total_entries'];
    $display_entries = getattr($options, 'display_entries', IA_PAGER_DEFAULT_DISPLAY_ENTRIES);
    $url_args = getattr($options, 'url_args', $_GET);
    $param_prefix = getattr($options, 'pager_prefix', '');
    $surround_pages = getattr($options, 'surround_pages', 5);

    $result = "";

    $curpage = (int)($first_entry / $display_entries);
    $totpages = (int)(($total_entries + $display_entries - 1) / $display_entries);

    if ($totpages == 1) {
        return "Exista o singura pagina.";
    }
    $result .= "Vezi pagina ".($curpage + 1)." din $totpages: ";
    if ($curpage < 8) {
        for ($i = 0; $i < $curpage; ++$i) {
            $url_args[$param_prefix.'first_entry'] = $i * $display_entries;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
    } else {
        for ($i = 0; $i < $surround_pages; ++$i) {
            $url_args[$param_prefix.'first_entry'] = $i * $display_entries;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
        $result .= "... ";
        for ($i = $curpage - $surround_pages; $i < $curpage; ++$i) {
            $url_args[$param_prefix.'first_entry'] = $i * $display_entries;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
    }
    $result .= ($curpage + 1)." ";
    if ($totpages - $curpage < 3 + 2 * $surround_pages) {
        for ($i = $curpage + 1; $i < $totpages; ++$i) {
            $url_args[$param_prefix.'first_entry'] = $i * $display_entries;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
    } else {
        for ($i = $curpage + 1; $i <= $curpage + $surround_pages; ++$i) {
            $url_args[$param_prefix.'first_entry'] = $i * $display_entries;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
        $result .= "... ";
        for ($i = $totpages - $surround_pages; $i < $totpages; ++$i) {
            $url_args[$param_prefix.'first_entry'] = $i * $display_entries;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
    }

    return $result;
}

?>
