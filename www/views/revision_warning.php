<div class="warning">
    Atentie! Aceasta pagina nu este actuala (este varianta de la <?= htmlentities($textblock['timestamp']) ?>)</br>
    <?php
    if ($view['revision'] > 1) {
        echo format_link(url_textblock_revision($view['page_name'], $view['revision'] - 1), "<<<");
    }
    else {
        echo "<<<";
    }
    ?>
    &nbsp;
    <?php
    if ($view['revision'] < $view['revision_count']) {
        echo format_link(url_textblock_revision($view['page_name'], $view['revision'] + 1), ">>>");
    } 
    else {
        echo ">>>";
    }
    ?>
</div>
