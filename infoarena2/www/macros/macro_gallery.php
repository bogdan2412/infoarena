<?php

require_once(IA_ROOT_DIR . "common/db/attachment.php");
require_once(IA_ROOT_DIR . "www/format/pager.php");

define("MACRO_GALLERY_RESIZE", "130x80");

// Display image gallery of file (image) attachments.
// Arguments:
//      page (required)     Textblock name. You may use % as wildcard
//      file (required)     File attachment name. You may use % as wildcard
//
// Examples:
//      Gallery(page="preONI/Ziua1/Poze" file="%.jpg")
//          displays all files ending with .jpg, attached to
//          page preONI/Ziua1/Poze
//      Gallery(page="preONI/Ziua%" file="%.jpg")
//          displays all files ending with .jpg, attached to
//          any page beginning with preONI/Ziua
function macro_gallery($args) {
    if (!isset($args['display_entries'])) {
        $args['display_entries'] = 18;
    }
    $options = pager_init_options($args);

    $page = getattr($args, 'page');
    $file = getattr($args, 'file');

    // validate arguments
    if (!$page) {
        return macro_error('Expecting argument `page`');
    }
    if (!$file) {
        return macro_error('Expecting argument `file`');
    }

    // get attachment list
    $options['total_entries'] = attachment_get_count($page, $file);
    $atts = attachment_get_all($page, $file, $options['first_entry'], $options['display_entries']);

    // display gallery
    $buffer = '<div class="gallery"><div class="images">';
    foreach ($atts as $attach) {
        $thumbsrc = url_image_resize($attach['page'], $attach['name'], "130x80");
        $fullsrc = url_image_resize($attach['page'], $attach['name'], null);
        $buffer .= "<a href=\"".htmlentities($fullsrc)."\"><img src=\"".htmlentities($thumbsrc)."\" alt=\"".htmlentities($attach['page'])."\" /></a>";
    }
    $buffer .= '</div>';
    $buffer .= format_pager($options);
    $buffer .= '</div>';

    return $buffer;
}

?>
