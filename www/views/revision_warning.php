<div class="warning">
    <?php
    if ($view['revision'] < $view['revision_count']) {
        echo "Atenţie! Această pagina nu este actuală (este varianta de la ".html_escape($textblock['timestamp']).")</br>\n";
    } else {
        echo "Atenţie! Aceasta este ultima versiune a paginii, scrisă la ".html_escape($textblock['timestamp']).")</br>\n";
    }
    if ($view['revision'] > 1) {
        echo format_link(url_textblock_revision($view['page_name'], $view['revision'] - 1), "Revizia anterioară");
    }
    else {
        echo "Revizia anterioară";
    }
    ?>
    &nbsp;
    <?php
    if ($view['revision'] < $view['revision_count']) {
        echo format_link(url_textblock_revision($view['page_name'], $view['revision'] + 1), "Revizia următoare");
    }
    else {
        echo "Revizia următoare";
    }
    ?>
    &nbsp;
    <?php
    if (identity_can('textblock-delete-revision', $view['textblock'])) {
        echo format_post_link(url_textblock_delete_revision($view['page_name'], $view['revision']), "Şterge");
    }
    ?>
</div>
