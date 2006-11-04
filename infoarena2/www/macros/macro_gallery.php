<?php

define("MACRO_GALLERY_MAX", 100);
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
    $page = getattr($args, 'page');
    $file = getattr($args, 'file');

    // validate arguments
    if (!$page) {
        return macro_error('Expecting argument `page`');
    }
    if (!file) {
        return macro_error('Expecting argument `file`');
    }

    // get attachment list
    $att_unfiltered = attachment_get_all($page, $file);

    // filter attachments by user permissions
    $attachments = array();
    foreach ($att_unfiltered as $attach) {
        if (!identity_can('attach-download', $attach)) {
            continue;
        }
        $attachments[] = $attach;
    }

    // display gallery
    $buffer = '<div class="gallery">';
    foreach ($attachments as $attach) {
        $thumbsrc = url($attach['page'], array('action'=>'download', 'file'=>$attach['name'],
                                                    'resize'=>MACRO_GALLERY_RESIZE));
        $fullsrc = url($attach['page'], array('action'=>'download', 'file'=>$attach['name']));
        $buffer .= "<a href=\"{$fullsrc}\"><img src=\"{$thumbsrc}\" alt=\"{$attach['page']}\" /></a>";
    }
    $buffer .= '</div>';

    return $buffer;
}

?>
