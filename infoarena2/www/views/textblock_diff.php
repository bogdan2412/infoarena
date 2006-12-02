<?php

include('header.php');

?>

<h1>Diferente pentru <?= format_link(url($page_name), $page_name) ?> intre reviziile
    <?= format_link(url($page_name, array('revision' => $revfrom_id)), "#$revfrom_id") ?> si
    <?= format_link(url($page_name, array('revision' => $revto_id)), "#$revto_id") ?>
</h1>
<?php

function diff_print_color_line($s)
{
    if (preg_match("/^(---|\+\+\+)/", $s)) {
        return;
    }
    if (preg_match("/^(@@)/", $s)) {
        echo "<hr />";
        return;
    }
    if (strlen($s) > 0 && $s[0] == '+') {
        $class = "added";
    } else if (strlen($s) > 0 && $s[0] == '-') {
        $class = "deleted";
    } else {
        $class = "normal";
    }
    echo "<span class=\"$class\">".htmlentities(substr($s, 1))."</span>";
}

if (count($view['diff_title']) <= 1) {
    echo "<h3>Nu exista diferente intre titluri.</h3>";
}
else {
    echo "<h3>Diferente intre titluri:</h3>";
    echo "<div class=\"diff\">";
    for ($i = 0; $i+1 < count($view['diff_title']); $i++) {
        $s = $view['diff_title'][$i];
        $class = diff_print_color_line($s);
    }
    echo "</div>";
}
?>

<?php
if (count($view['diff_content']) <= 1) {
    echo "<h3>Nu exista diferente intre continut.</h3>";
}
else {
    echo "<h3>Diferente intre continut:</h3>";
    echo '<div class="diff">';
    for ($i = 0; $i+1 < count($view['diff_content']); $i++) {
        $s = $view['diff_content'][$i];
        $class = diff_print_color_line($s);
    }
    echo "</div>";
}
?>

<?php include('footer.php'); ?>
