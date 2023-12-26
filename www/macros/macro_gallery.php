<?php

// Display image gallery of file (image) attachments.
// Arguments:
//      page (required)     Textblock name.
//      file (required)     File attachment name. You may use % as wildcard.
//
// Examples:
//      Gallery(page="preONI/Ziua1/Poze" file="%.jpg")
//          displays all files ending with .jpg, attached to
//          page preONI/Ziua1/Poze
//      Gallery(page="preONI/Ziua%" file="%.jpg")
//          displays all files ending with .jpg, attached to
//          any page beginning with preONI/Ziua
function macro_gallery(array $args): string {
  $textblockName = $args['page'] ?? '';
  $tb = Textblock::get_by_name($textblockName);
  if (!$tb) {
    return macro_error("Pagina „{$textblockName}” nu există.");
  }

  $fileWildcard = $args['file'] ?? '';
  if (!$fileWildcard) {
    return macro_error('Parametrul „file” este obligatoriu.');
  }

  $attachments = $tb->getImageAttachments($fileWildcard);

  Smart::assign('attachments', $attachments);
  return Smart::fetch('macro/gallery.tpl');
}
