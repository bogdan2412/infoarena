<?php
require_once(IA_ROOT_DIR."common/task.php");
require_once(IA_ROOT_DIR."common/tags.php");
require_once(IA_ROOT_DIR."www/format/form.php");
require_once(IA_ROOT_DIR."www/views/task_edit_header.php");

$view['head'] = getattr($view, 'head').
    "<script src=\"" . html_escape(url_static("js/parameditor.js")) . "\"></script>";

include(CUSTOM_THEME . 'header.php');
include('tags_header.php');

echo task_edit_tabs($view['task_id'], request("action"));

// Validate task.
log_assert_valid(task_validate($task));

$form_fields = array(
        'title' => array(
                'name' => "Titlul problemei",
                'description' => "Numele sub care apare problema pentru utilizator.",
                'type' => 'string',
        ),
        'page_name' => array(
                'name' => "Pagina cu enunțul",
                'description' => "Aceasta este pagina la care este trimis ".
                                 "utilizatorul când dă click pe problemă.",
                'type' => 'string',
        ),
        'user' => array(
                'name' => "Utilizatorul care adaugă problema",
                'description' => "Utilizatorul care are drepturi de editare ".
                                 "asupra problemei. Poate fi lăsat gol.",
                'type' => 'string',
        ),
        'source' => array(
                'name' => "Sursa",
                'type' => 'string',
        ),
        'security' => array(
                'name' => 'Securitate',
                'description' => 'Nivelul de securitate al problemei.',
                'type' => 'enum',
                'values' => task_get_security_types(),
                'default' => 'private',
        ),
        'type' => array(
                'name' => 'Tipul problemei',
                'type' => 'enum',
                'values' => task_get_types(),
                'default' => 'classic',
        ),
        'open_source' => array(
                'name' => 'Acces liber la surse',
                'type' => 'bool'
        ),
        'open_tests' => array(
                'name' => 'Acces liber la teste',
                'type' => 'bool'
        ),
        'test_count' => array(
                'name' => "Număr de teste",
                'default' => 10,
                'type' => 'integer',
        ),
        'test_groups' => array(
                'default' => '1;2;3;4;5;6;7;8;9;10',
                'type' => 'string',
                'name' => "Grupare teste",
        ),
        'public_tests' => array(
                'description' => "Lista de teste pe care utilizatorii pot vedea rezultatele în concursuri.",
                'default' => '',
                'type' => 'string',
                'name' => "Feedback detaliat",
        ),
        'use_ok_files' => array(
                'default' => '0',
                'type' => 'bool',
                'name' => "Folosește .ok",
        ),
        'evaluator' => array(
                'description' => "Sursa evaluatorului. Poate fi omis pentru evaluare cu diff.",
                'default' => 'eval.c',
                'type' => 'string',
                'name' => "Evaluator",
        ),
);

?>

<h1>
    Editare parametri
    <a href="<?= html_escape(url_task($view['task_id'])) ?>">
        <?= html_escape($view['task_id']) ?>
    </a>
</h1>

<?php if (identity_can("task-delete", $task)) { ?>
    <form action="<?= html_escape(url_task_delete()) ?>" method="post" style="float: right">
        <input type="hidden" name="task_id" value="<?= html_escape($task_id) ?>">
        <input
            onclick="return confirm('Această acțiune este ireversibilă! Dorești să continui?')"
            type="submit"
            value="Șterge problema"
            id="form_delete"

            <?php if ($view['secure_delete']): ?>
            class="button important disabled"
            disabled
            title="Pentru a preveni greșelile, problemele cu fișiere atașate nu pot fi șterse. Te rugăm să ștergi întîi atașamentele problemei."
            <?php else: ?>
            class="button important"
            <?php endif; ?>
       >
    </form>
<?php } ?>

<form action="<?= html_escape(url_task_edit($task_id, 'task-edit-params')) ?>"
      method="post"
      class="task"
      <?= tag_form_event() ?>>
    <fieldset>
    <legend>Despre problemă</legend>
    <ul class="form">
        <?= view_form_field_li($form_fields['title'], 'title') ?>
        <?= view_form_field_li($form_fields['page_name'], 'page_name') ?>
        <?php if (identity_can('task-edit-owner', $task)) { ?>
            <?= view_form_field_li($form_fields['user'], 'user') ?>
        <?php } ?>
        <?= view_form_field_li($form_fields['source'], 'source') ?>
        <?php if (identity_can('task-change-security', $task)) { ?>
           <?= view_form_field_li($form_fields['security'], 'security') ?>
        <?php } ?>
   </ul>
    </fieldset>

    <?php if (identity_can('task-tag', $task)) {
        $tag_fields = Array('author' => Array("label" => "Autor",
                                                "name" => "tag_author"),
                            'contest' => Array("label" => "Concurs",
                                                "name" => "tag_contest"),
                            'year' => Array("label" => "Anul",
                                            "name" => "tag_year"),
                            'round' => Array("label" => "Runda",
                                            "name" => "tag_round"),
                            'age_group' => Array("label" => "Grupa de vârstă",
                                        "name" => "tag_age_group"),
                        );
    ?>
        <fieldset>
            <legend>Taguri</legend>
            <ul class="form">
            <?php
                foreach (array_keys($tag_fields) as $tag) {
                    echo tag_format_input_box($tag_fields[$tag], fval('tag_'.$tag), 50, 'tag_'.$tag);
                }
            ?>
            </ul>
        </fieldset>
    <?php } ?>


<?php
// FIXME: Field should be generated from task_get_types()
?>
    <?php if (identity_can('task-change-open', $task)) { ?>
    <fieldset>
    <legend>Acces la surse și teste</legend>
    <ul class="form">
        <?= view_form_field_li($form_fields['open_source'], 'open_source') ?>
        <?= view_form_field_li($form_fields['open_tests'], 'open_tests') ?>
    </ul>
    </fieldset>
    <?php } ?>

    <fieldset>
    <legend>Detalii despre evaluare</legend>
    <ul class="form">
        <?= view_form_field_li($form_fields['test_count'], 'test_count') ?>
        <?= view_form_field_li($form_fields['test_groups'], 'test_groups') ?>
        <?= view_form_field_li($form_fields['public_tests'], 'public_tests') ?>
        <?= view_form_field_li($form_fields['use_ok_files'], 'use_ok_files') ?>
        <?= view_form_field_li($form_fields['evaluator'], 'evaluator') ?>

        <?= view_form_field_li($form_fields['type'], 'type') ?>
        <li><hr></li>
        <li id="field_params">
            <?= format_param_editor_list(
                    $param_infos, $form_values, $form_errors); ?>
        </li>
    </ul>
    </fieldset>

    <div class="submit">
        <ul class="form">
            <li id="field_submit">
                <input type="submit" value="Salvează" id="form_submit" class="button important">
            </li>
        </ul>
    </div>
</form>

<?php include('footer.php'); ?>
