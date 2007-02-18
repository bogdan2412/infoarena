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
    if (preg_match('/^[0-9]*$/', $display_entries) == false) {
        $display_entries = IA_PAGER_DEFAULT_DISPLAY_ENTRIES;
        log_warn("Bad display_entries");
    }
    $display_entries = 
            max(IA_PAGER_MIN_DISPLAY_ENTRIES,
            min($display_entries, IA_PAGER_MAX_DISPLAY_ENTRIES));

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

    $options = array(
            'display_entries' => $display_entries,
            'pager_style' => $pager_style,
            'first_entry' => $first_entry,
    );
    if ($prefix != "") {
        $options['param_prefix'] = $prefix;
    }
    return $options;
}

// Call this on a pager options struct to find out if you need to send 'total_entries'
// This is just an optimization, total_entries can't hurt.
function pager_needs_total_entries($options) {
    return $options['pager_style'] == 'standard';
}

// Format pager.
function format_pager($options)
{
    if ($options['pager_style'] == 'standard') {
        return '<div class="pager"><div class="standard-pager">' . format_standard_pager($options) . '</div></div>';
    } else {
        return '';
    }
}

// Internal for format_standard_pager
function _format_standard_pager_link($options, $number) {
    $url_args = getattr($options, 'url_args', $_GET);
    $param_prefix = getattr($options, 'param_prefix', '');
    $display_entries = getattr($options, 'display_entries', IA_PAGER_DEFAULT_DISPLAY_ENTRIES);

    $url_args[$param_prefix.'first_entry'] = $number * $display_entries;
    ++$number;
    $access_keys = getattr($options, 'use_digit_access_keys', true);
    $args = array();
    if ($access_keys && $number >= 0 && $number <= 9) {
        $args['accesskey'] = $number;
    }

    return format_link(url_from_args($url_args), $number, false, $args) . ' ';
}

// Formats a standard pager. Used by format_table.
function format_standard_pager($options)
{
    log_assert($options['pager_style'] == "standard");
    $first_entry = getattr($options, 'first_entry', 0);
    $total_entries = $options['total_entries'];
    $display_entries = getattr($options, 'display_entries', IA_PAGER_DEFAULT_DISPLAY_ENTRIES);
    $surround_pages = getattr($options, 'surround_pages', 5);
    $access_keys = getattr($options, 'use_digit_access_keys', true);

    assert(is_whole_number($display_entries));
    assert(is_whole_number($first_entry));
    assert(is_whole_number($total_entries));

    //log_print_r($options);

    $curpage = (int)($first_entry / $display_entries);
    $totpages = (int)(($total_entries + $display_entries - 1) / $display_entries);

    if ($totpages == 1) {
        return "";
    }
    $result = "Vezi pagina: ";
    if ($curpage < 8) {
        for ($i = 0; $i < $curpage; ++$i) {
            $result .= _format_standard_pager_link($options, $i);
        }
    } else {
        for ($i = 0; $i < $surround_pages; ++$i) {
            $result .= _format_standard_pager_link($options, $i);
        }
        $result .= "... ";
        for ($i = $curpage - $surround_pages; $i < $curpage; ++$i) {
            $result .= _format_standard_pager_link($options, $i);
        }
    }
    $result .= '<span class="selected"><strong>'.($curpage + 1)."</strong></span> ";
    if ($totpages - $curpage < 3 + 2 * $surround_pages) {
        for ($i = $curpage + 1; $i < $totpages; ++$i) {
            $result .= _format_standard_pager_link($options, $i);
        }
    } else {
        for ($i = $curpage + 1; $i <= $curpage + $surround_pages; ++$i) {
            $result .= _format_standard_pager_link($options, $i);
        }
        $result .= "... ";
        for ($i = $totpages - $surround_pages; $i < $totpages; ++$i) {
            $result .= _format_standard_pager_link($options, $i);
        }
    }

    return $result;
}

?>
