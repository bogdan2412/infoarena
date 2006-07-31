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
<table>
    <tr class='navigation'>
<?php   if ($page > 1) { ?>
            <td><a href="<?= url("monitor", array('page_num' => $page-1)) ?>">Inapoi</a></td>
<?php   } ?>
<?php   for ($i=max(1, $page-5); $i<=min($page_max, $page+5); ++$i) {?>
            <td><a href="<?= url("monitor", array('page_num' => $i)) ?>"><?= $i==$page?"<strong>".$i."</strong>":$i; ?></a></td>
<?php   } ?>
<?php   if ($page < $page_max) { ?>
            <td><a href="<?= url("monitor", array('page_num' => $page+1 )) ?>">Inainte</a></td>
<?php   }?>
    </tr>
</table>
<?php include('footer.php'); ?>