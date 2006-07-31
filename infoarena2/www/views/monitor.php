<?php include('header.php'); ?>

<h1><?= htmlentities($view['title']) ?></h1>
<h3>Elementele de la pagina <?=$page?></h3>
<table class='monitor'>
    <thead>
    <tr>
<?php
        foreach ($jobs[0] as $key => $val) {
        echo '<th>';
        echo $key;
        echo '</th>';
        }
?>
    </tr>
    </thead>
    <tbody>
<?php
    $con = 1;
    foreach ($jobs as $line) {
        // style each line based on parity
        if ($con++ % 2 == 0) {
            echo "<tr class='even'>\n";
        }
        else {
            echo "<tr class='odd'>\n";
        }
        foreach ($line as $val) {
            echo "<td>";
            echo $val;
            echo "</td>";
        }
        echo "</tr>\n";
    }
?>
    </tbody>
</table>
<div class='paginator'>
    <div class='prev_next'>
<?php   if ($page > 1) { ?>
            <a href="<?= url("monitor", array('page_num' => $page-1)) ?>">Inapoi</a>
<?php   }
        if ($page < $page_max) { ?>
            <a href="<?= url("monitor", array('page_num' => $page+1 )) ?>">Inainte</a>
<?php   } ?>
    </div>
    <div class='jump'>
<?php
        // show exponential page numbers increasing from 1 to current page
        for ($i=8, $ac=0; $page-$i>0; $i*=2) {
            $pn = $page-$i;
            $a[$ac++] = '<a href="'. url("monitor", array('page_num' => ($page-$i))) . '">' . ($page-$i) . '</a> ';
        }
        for ($i=$ac-1; $i>=0; --$i) {
            echo $a[$i];
        }
        if ($ac) {
            echo "\n".'<span class="separator_left"> &laquo; </span>'."\n";
        }
        unset($a); unset($ac); // remove unused variables
        for ($i=max(1, $page-3); $i<=min($page_max, $page+3); ++$i) {
            if ($i==$page) {
                echo "<strong>".$i."</strong>\n";
            }
            else {
?>
            <a href="<?= url("monitor", array('page_num' => $i)) ?>"><?= $i ?></a>
<?php
            }
        }
        if ($page+8 <= $page_max) {
            echo '<span class="separator_right"> &raquo; </span>'."\n";
        }
        // show exponential page numbers increasing from current page to max
        for ($i=8; $page+$i<=$page_max; $i*=2) {
            echo '<a href="'. url("monitor", array('page_num' => ($page+$i))) . '">' . ($page+$i) . '</a> ';
        }
?>
    </div>
</div>
<?php include('footer.php'); ?>