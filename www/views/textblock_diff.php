<?php

include(CUSTOM_THEME . 'header.php');

$min_rev_id = min($revfrom_id, $revto_id);
$max_rev_id = max($revfrom_id, $revto_id);

?>

<div class="swap_diff">
<?= format_link(url_textblock_diff($page_name, $revto_id, $revfrom_id), "Inversează reviziile") ?>
</div>

<h1>Diferențe pentru
<?= format_link(url_textblock($page_name), $page_name) ?> între reviziile
    <?= format_link(url_textblock_revision($page_name, $revfrom_id), "#$revfrom_id") ?> si
    <?= format_link(url_textblock_revision($page_name, $revto_id), "#$revto_id") ?>
</h1>

<div class="diff_navbar">
<?php if ($max_rev_id < $rev_count) { ?>
    <div class="next_diff">
    <?= format_link(url_textblock_diff($page_name, $max_rev_id, $max_rev_id + 1), "Diferențe între #" . $max_rev_id . " si #" . ($max_rev_id + 1)); ?>
    </div>
<?php } ?>
<?php if ($min_rev_id > 1) { ?>
    <div class="prev_diff">
    <?= format_link(url_textblock_diff($page_name, $min_rev_id - 1, $min_rev_id), "Diferențe între #" . ($min_rev_id - 1) . " si #" . $min_rev_id); ?>
    </div>
<?php } ?>
</div>

<?php

function print_diff($diff) {
    foreach ($diff as $block) {
        echo '<div class="diff">';
        foreach ($block as $op) {
            echo '<pre class="'.$op['type'].'">';
            foreach ($op['lines'] as $line) {
                $output = "";
                if (!is_array($line)) {
                    $output = html_escape($line);
                } else {
                    // line contains inline diff
                    foreach ($line as $chunk) {
                        if ($chunk['type'] != 'normal') {
                            $output .= '<'.$chunk['type'].'>';
                        }
                        $output .= html_escape($chunk['string']);
                        if ($chunk['type'] != 'normal') {
                            $output .= '</'.$chunk['type'].'>';
                        }
                    }
                }

                $output = str_replace("\n", '', $output);
                $output = str_replace("\r", '', $output);
                if ($output == "") {
                    $output = " ";
                }
                echo $output."\n";
            }
            echo '</pre>';
        }
        echo '</div>';
    }
}

if (empty($view['diff_title'])) {
    echo "<h3>Nu există diferențe între titluri.</h3>";
}
else {
    echo "<h3>Diferențe între titluri:</h3>";
    print_diff($view['diff_title']);
}
?>

<?php
if (empty($view['diff_content'])) {
    echo "<h3>Nu există diferențe între conținut.</h3>";
}
else {
    echo "<h3>Diferențe între conținut:</h3>";
    print_diff($view['diff_content']);
}
?>

<?php
if (empty($view['diff_security'])) {
    echo "<h3>Nu există diferențe între securitate.</h3>";
}
else {
    echo "<h3>Diferențe între securitate:</h3>";
    print_diff($view['diff_security']);
}
?>

<?php include('footer.php'); ?>
