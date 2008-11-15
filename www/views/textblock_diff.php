<?php

include('header.php');

?>

<h1>Diferente pentru
<?= format_link(url_textblock($page_name), $page_name) ?> intre reviziile
    <?= format_link(url_textblock_revision($page_name, $revfrom_id), "#$revfrom_id") ?> si
    <?= format_link(url_textblock_revision($page_name, $revto_id), "#$revto_id") ?>
</h1>
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
    echo "<h3>Nu exista diferente intre titluri.</h3>";
}
else {
    echo "<h3>Diferente intre titluri:</h3>";
    print_diff($view['diff_title']);
}
?>

<?php
if (empty($view['diff_content'])) {
    echo "<h3>Nu exista diferente intre continut.</h3>";
}
else {
    echo "<h3>Diferente intre continut:</h3>";
    print_diff($view['diff_content']);
}
?>

<?php
if (empty($view['diff_security'])) {
    echo "<h3>Nu exista diferente intre securitate.</h3>";
}
else {
    echo "<h3>Diferente intre securitate:</h3>";
    print_diff($view['diff_security']);
}
?>

<?php include('footer.php'); ?>
