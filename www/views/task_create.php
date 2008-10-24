<?php include('header.php');

// FIXME: copy/paste is stupid. Centralize field infos
$form_fields = array (
        'type' => array(
                'name' => 'Tipul problemei',
                'description' => 'Tipul problemei determina modul de evaluare',
                'type' => 'enum',
                'values' => task_get_types(),
                'default' => 'classic',
        ), 
        'id' => array(
                'name' => 'Id-ul problemei',
                'description' => 'Identificatorul unic si permanent al problemei.',
                'type' => 'string',
                'default' => '',
        ),
);

?>

<h1><?= html_escape($title) ?></h1>

<form action="<?= html_escape(url_task_create()) ?>" method="post" class="task create clear">
<fieldset>
<legend>Informatii initiale</legend>
    <ul class="form">
        <?= view_form_field_li($form_fields['id'], 'id') ?>
        <?= view_form_field_li($form_fields['type'], 'type') ?>
    </ul>
</fieldset>
    <div class="submit">
        <ul class="form">
            <li id="field_submit">
                <input type="submit" value="Creeaza task" id="form_submit" class="button important" />
            </li>
        </ul>
    </div>
</form>

<?php include('footer.php'); ?>
