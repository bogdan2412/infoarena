<table class="parameters">
    <thead>
        <tr>
            <th>Parametru</th>
            <th>Valoare</th>
            <th>Descriere</th>
        </tr>
    </thead>
    <tbody>

<?php

$display_time = false;

foreach ($param_list as $key => $param) {
    $pid = "p_" . $key;
    if ('datetime' == $param['type']) {
        $display_time = true;
    }

?>
<tr>
    <td><label for="form_p_<?= htmlentities($key) ?>"><?= htmlentities($param['name']) ?></label></td>
    <td><input type="text" class="parameter" value="<?= fval($pid) ?>" id="form_<?= htmlentities($pid) ?>" name="<?= htmlentities($pid) ?>"/> <?= ferr_span($pid) ?></td>
    <td><?= htmlentities($param['description']) ?></td>
</tr>
<?php
}
?>
        <?php if ($display_time) { ?>
            <p><strong>Ora curenta pe server: <?= htmlentities(format_datetime(time())) ?></strong></p>
        <?php } ?>
    </tbody>
</table>

