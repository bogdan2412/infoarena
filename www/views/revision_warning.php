<div class="warning">
    Atentie! Aceasta pagina nu este actuala (este varianta de la <?= html_escape($textblock['timestamp']) ?>)</br>
    <?php
    if ($view['revision'] > 1) {
        echo format_link(url_textblock_revision($view['page_name'], $view['revision'] - 1), "Revizia anterioara");
    }
    else {
        echo "Revizia anterioara";
    }
    ?>
    &nbsp;
    <?php
    if ($view['revision'] < $view['revision_count']) {
        echo format_link(url_textblock_revision($view['page_name'], $view['revision'] + 1), "Revizia urmatoare");
    } 
    else {
        echo "Revizia urmatoare";
    }
    ?>
    &nbsp;
    <?php
    if (identity_can('textblock-delete-revision', $view['textblock'])) {
        echo format_post_link(url_textblock_delete_revision($view['page_name'], $view['revision']), "Sterge");
    }
    ?>
</div>
