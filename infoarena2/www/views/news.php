<?php

// RSS discovery
$view['head'] = '<link rel="alternate" href="' . url($page_name, array('action' => 'feed')) . '" title="RSS Stiri info-arena" type="application/rss+xml" />';

include('header.php');

?>

<div class="newsbox">
    <h2>
        <?php if (getattr($view, 'feed_link')) { ?>
        <a class="feed" href="<?= htmlentities($view['feed_link']) ?>" title="RSS Stiri info-arena">
            RSS Stiri info-arena
        </a>
        <?php } ?>
        Stiri
    </h2>
    
    <?php foreach ($view['news'] as $v) { ?>
        <div class="newspiece">
        <?php
            echo '<span class="newsdate">'.htmlentities($v['timestamp']).'</span>';
            if (identity_can('wiki-view', $v)) {
                echo '<h3><a href="'.url($v['name']).'?action=view'.'">'.htmlentities(getattr($v, 'title')).'</a></h3>';
            }
            else {
                echo '<h3>'.htmlentities(getattr($v, 'title')).'</h3>';
            }
            echo '<div class="wiki_text_block">';
            $minicontext = array('page_name' => $v['name'],
                                 'title' => $v['title']);
            echo wiki_process_text_recursive(getattr($v, 'text'), $minicontext);
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
