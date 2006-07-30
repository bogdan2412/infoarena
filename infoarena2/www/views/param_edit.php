<table class="parameters">
    <thead>
        <tr>
            <th>Parametru</th>
            <th>Valoare</th>
            <th>Descriere</th>
        </tr>
    </thead>
    <tbody>
<?php foreach ($param_list as $param) { ?>
        <tr>
            <td><label for="form_p_<?= $param['id'] ?>"><?= $param['id'] ?></label></td>
            <td><input type="text" class="parameter" value="<?= fval('p_' . $param['id']) ?>" id="form_p_<?= $param['id'] ?>" name="p_<?= $param['id'] ?>"/> <?= ferr_span('p_' . $param['id']) ?></td>
            <td><?= $param['description'] ?></td>
        </tr>
<?php } ?>
    </tbody>
</table>

