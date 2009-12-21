<?php
include(IA_ROOT_DIR.'www/views/header.php');

require_once(IA_ROOT_DIR.'www/format/table.php');

// Returns a tag's name along with a inline form
// which will be used for renaming.
function format_tag_name($row) {
    $tag = inline_post_form(url_task_tags_rename(),
        array(
            "type" => "algorithm",
            "old_name" => $row["name"],
            "parent" => $row["parent"]
        ),
        $row["name"], "Redenumeste");
    $tag .= ' <a class="algorithm_tag" href="#">' . html_escape($row["name"]) . '</a>';
    return $tag;
}

// Returns a delete tag link and a toggle rename form link
function format_operations($row) {
    $delete = format_post_link(url_task_tags_delete(), "Sterge",
        array(
            "type" => $row["type"],
            "name" => $row["name"],
            "parent" => $row["parent"]
        )
    );
    $rename = '<a href="#" class="toggle_rename">Redenumeste</a>';
    return sprintf("[%s] [%s]", $delete, $rename);
}

// Returns an inline form which can be used for adding or renaming tags.
function inline_post_form($url, $post_data=array(), $default_value="", $submit_button="OK") {
    $form = '<form class="inline_form" method="post" action="' . html_escape($url) . '"> ';
    foreach ($post_data as $key => $value) {
        $form .= '<input type="hidden" name="' . html_escape($key) . '" value="' . html_escape($value) . '" />';
    }
    $form .= '<input type="text" name="name" value="' . html_escape($default_value) . '" /> ';
    $form .= '<input class="button" type="submit" value="' . html_escape($submit_button) . '" />';
    $form .= '</form>';
    return $form;
}

$column_infos = array(
    array(
        'title' => 'Tag',
        'rowform' => 'format_tag_name',
        'css_class' => 'tag-name',
    ),
    array(
        'title' => 'Numar probleme',
        'key' => 'task_count',
        'css_class' => 'tag-task-count',
    ),
    array(
        'title' => 'Operatii',
        'rowform' => 'format_operations',
        'css_class' => 'tag-operations',
    ),
);

?>

<h1>Editare taguri algoritmi</h1>
<div id="add_category" class="task-tag-actions">
[<a href="#">Adauga categorie noua</a><?php
    echo inline_post_form(url_task_tags_add(), array("type" => "method"), "", "Adauga");
?>]
</div>
<?php
foreach ($categories as $category) {
?>
    <h2><?php echo html_escape($category["name"]); ?></h2>
    <ul class="task-tag-actions">
        <li class="algorithm_tag_add">[<a class="toggle_add" href="#">Adauga tag nou</a><?php
            echo inline_post_form(url_task_tags_add(),
                array("type" => "algorithm", "parent" => $category["id"]),
                "", "Adauga");
        ?>]</li>
        <li class="delete_method">[<?php echo format_post_link(url_task_tags_delete(), "Sterge categorie",
            array("type" => "method", "name" => $category["name"])); ?>]</li>
        <li class="rename_method">[<a class="toggle_rename" href="#">Redenumeste categorie</a><?php
            echo inline_post_form(url_task_tags_rename(),
                array("type" => "method", "old_name" => $category["name"]),
                $category["name"], "Redenumeste");
        ?>]</li>
    </ul>
    <?php
    echo format_table($category["sub_tags"], $column_infos,
        array('css_class' => 'category fill-screen tag-table'));
    ?>
<?php
}
?>
<script type="text/javascript" src="<?= html_escape(url_static('js/inline_form.js')) ?>"></script>
<script type="text/javascript">
/* <![CDATA[ */
// Bind add method link to form
addLoadEvent(function() {
    bindToggleLinkToForm($$("#add_category > a")[0], $$("#add_category > form")[0]);
});

// Bind add algorithm links to forms.
var addForms = $$(".algorithm_tag_add form");
var addLinks = $$(".algorithm_tag_add a.toggle_add");
for (var i = 0; i < addForms.length; i++) {
    bindToggleLinkToForm(addLinks[i], addForms[i]);
}

// Bind rename algorithm links to forms
var renameForms = $$("table.category form");
var renameLinks = $$("table.category a.toggle_rename");
var renameOrigs = $$("table.category a.algorithm_tag");
for (var i = 0; i < renameForms.length; i++) {
    bindToggleLinkToForm(renameLinks[i], renameForms[i], renameOrigs[i]);
}

// Bind rename method links to forms
var renameMethodForms = $$(".rename_method form");
var renameMethodLinks = $$(".rename_method a.toggle_rename");
for (var i = 0; i < addForms.length; i++) {
    bindToggleLinkToForm(renameMethodLinks[i], renameMethodForms[i]);
}
/* ]]> */
</script>
<?php
include(IA_ROOT_DIR.'www/views/footer.php');
?>
