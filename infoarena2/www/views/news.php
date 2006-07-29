<?php include('header.php'); ?>

<div class="news">
    <?php foreach ($view['news'] as $v) { ?>
        <div class="item">
        <?php
            if (identity_can('wiki-view', $v)) {
                echo '<a href="'.url($v['name']).'?action=view'.'"><h3>'.htmlentities(getattr($v, 'title')).'</h3></a>';
            }
            else {
                echo '<h3>'.htmlentities(getattr($v, 'title')).'</h3>';
            }
            echo '<span class="date">'.htmlentities($v['timestamp']).'</span>';
            echo '<div class="wiki_text_block">';
            echo wiki_process_text(getattr($v, 'text'), getattr($v, 'name'));
            echo '</div>';
        ?>
        </div>
    <?php } ?>
    
    <span class="paginator">
    <?php
    $cnt = news_count();
    for ($page = 0; $page*IA_MAX_NEWS < $cnt; $page++)
        if ($page != $view['page']) {
            echo '<a href="news?pagenum='.$page.'">' . ($page + 1) . '</a> ';
        }
        else {
            echo '<strong>'.($page + 1).'</strong> ';
        }
    ?>
    </span>
</div>

<?php include('footer.php'); ?>
