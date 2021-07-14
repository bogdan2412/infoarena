<?php

require_once(IA_ROOT_DIR.'common/newsletter.php');

// site header
include(CUSTOM_THEME . 'header.php');

// wiki page header (actions)
include('textblock_header.php');

// revision warning
if (getattr($view, 'revision')) {
    include('revision_warning.php');
}

// newsletter title (subject)
echo format_tag('h1', newsletter_subject($textblock, identity_get_user()));

// Display newsletter body inside an iframe. This makes the preview closer
// to what email recipients normally see.
$preview_url = url_absolute(url_newsletter_preview_body($textblock['name'],
        $revision));
?>
<iframe src="<?= html_escape($preview_url) ?>" name="newsletter_preview" id="newsletter_preview">
    <a href="<?= html_escape($preview_url) ?>">Vizualizează newsletter-ul.</a>
</iframe>
<?php

// site footer
include('footer.php');

?>
