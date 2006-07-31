<?php include('header.php'); ?>

<h1><?= htmlentities($view['title']) ?></h1>
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
    foreach ($jobs as $row) {
        echo '<tr>';
        foreach ($row as $column) {
            echo '<td>';
            echo $column;
            echo '</td>';
        }
        echo '</tr>';
    }
?>
    </tbody>
</table>
<?php include('footer.php'); ?>