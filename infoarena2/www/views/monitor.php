<?php include('header.php'); ?>

<h1><?= htmlentities($view['title']) ?></h1>
<h3>Elementele de la <?=$start?> la <?=min($start+$rows, $row_max)?></h3>
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
    foreach ($jobs as $line) {
        echo '<tr>';
        foreach ($line as $val) {
            echo '<td>';
            echo $val;
            echo '</td>';
        }
        echo '</tr>';
    }
?>
    <tr class='navigation'>
<?php   if ($start > 0) { ?>
            <td><a href="<?= url("monitor", array('start' => $start-$rows<0 ? 0 : $start-$rows)) ?>">Inapoi</a></td>
<?php   }
        if ($start+$rows <= $row_max) { ?>
            <td><a href="<?= url("monitor", array('start' => $start+$rows )) ?>">Inainte</a></td>
<?php   }?>
    </tr>
    </tbody>
</table>
<?php include('footer.php'); ?>