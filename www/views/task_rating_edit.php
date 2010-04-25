<?php

require_once(IA_ROOT_DIR . "www/format/form.php");
require_once(IA_ROOT_DIR . "www/views/task_edit_header.php");

include('views/header.php');

echo task_edit_tabs($view['task_id'], request("action"));

$form_fields = array(
    'idea' => array(
            'name' => 'Rating idee',
            'description' => 'Notă pentru ideea de rezolvare.',
            'type' => 'string',
    ),
    'theory' => array(
            'name' => 'Rating teorie',
            'description' => 'Notă pentru noţiunile teoretice necesare.',
            'type' => 'string',
    ),
    'coding' => array(
            'name' => 'Rating implementare',
            'description' => 'Notă pentru nivelul de implementare.',
            'type' => 'string',
    )
);
?>

<h1>Editare ratinguri <a href="<?= html_escape(url_task($view['task_id'])) ?>">
<?= html_escape($view['task_id']) ?></a></h1>

<form action="<?= html_escape(url_task_edit($task_id, 'task-edit-ratings')) ?>"
      method="post" class="task">
<fieldset>
<legend>Ratinguri</legend>
<ul class="form">
<?php
    echo view_form_field_li($form_fields['idea'], 'idea');
    echo view_form_field_li($form_fields['theory'], 'theory');
    echo view_form_field_li($form_fields['coding'], 'coding');
?>
</ul>
</fieldset>

<div class="submit">
    <ul class="form">
        <li id="field_submit">
            <input type="submit" value="Salveaza" id="form_submit"
                   class="button important" />
        </li>
    </ul>
</div>
</form>

<?php include('views/footer.php');
