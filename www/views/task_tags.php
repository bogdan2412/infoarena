<?php
include(IA_ROOT_DIR.'www/views/header.php');

require_once(IA_ROOT_DIR.'www/format/table.php');

// Returns a tag's name along with a inline form
// which will be used for renaming.
function format_tag_name($row) {
    $tag = inline_post_form(url_task_tags_rename(),
        array(
            "old_name" => $row["name"],
            "type" => $row["type"],
            "parent" => $row["parent"]
        ),
        $row["name"], "Redenumeşte");
    $tag .= ' '.format_link(
        url_task_search(array($row["id"])), $row["name"], true,
        array("class" => "algorithm_tag")
    );
    return $tag;
}

// Returns a delete tag link and a toggle rename form link
function format_operations($row) {
    $delete = format_post_link(url_task_tags_delete(), "Şterge",
        array(
            "name" => $row["name"],
            "type" => $row["type"],
            "parent" => $row["parent"]
        )
    );
    $rename = '<a href="#" class="toggle_rename">Redenumeşte</a>';
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
        'title' => 'Număr probleme',
        'key' => 'task_count',
        'css_class' => 'tag-task-count',
    ),
    array(
        'title' => 'Operaţii',
        'rowform' => 'format_operations',
        'css_class' => 'tag-operations',
    ),
);

?>

<h1>Editare taguri algoritmi</h1>
<div id="add_category" class="task-tag-actions">
[<a href="#">Adaugă categorie nouă</a><?php
    echo inline_post_form(url_task_tags_add(), array("type" => "method"), "", "Adaugă");
?>]
</div>
<?php
foreach ($categories as $category) {
?>
    <h2><?php echo html_escape($category["name"]); ?></h2>
    <ul class="task-tag-actions">
        <li class="algorithm_tag_add">[<a class="toggle_add" href="#">Adaugă tag nou</a><?php
            echo inline_post_form(url_task_tags_add(),
                array("type" => "algorithm", "parent" => $category["id"]),
                "", "Adaugă");
        ?>]</li>
        <li class="delete_method">[<?php echo format_post_link(url_task_tags_delete(), "Şterge categorie",
            array("type" => "method", "name" => $category["name"])); ?>]</li>
        <li class="rename_method">[<a class="toggle_rename" href="#">Redenumeşte categorie</a><?php
            echo inline_post_form(url_task_tags_rename(),
                array("type" => "method", "old_name" => $category["name"]),
                $category["name"], "Redenumeşte");
        ?>]</li>
    </ul>
    <?php
    echo format_table($category["sub_tags"], $column_infos,
        array('css_class' => 'category fill-screen tag-table'));
    ?>
<?php
}
?>
<h1>Editare taguri autori</h1>
<div id="add_author" class="task-tag-actions">[<a class="toggle_add" href="#">Adaugă tag nou</a><?php
    echo inline_post_form(url_task_tags_add(),
        array("type" => "author"),
        "", "Adaugă");
?>]</div>
<?php
echo format_table($authors, $column_infos,
    array('css_class' => 'category fill-screen tag-table'));
?>
<script type="text/javascript" src="<?= html_escape(url_static('js/inline_form.js')) ?>"></script>
<script type="text/javascript">
/* <![CDATA[ */
// Bind add method link to form
addLoadEvent(function() {
    bindToggleLinkToForm($$("#add_category > a")[0], $$("#add_category > form")[0]);
});

// Bind add author link to form
addLoadEvent(function() {
    bindToggleLinkToForm($$("#add_author > a")[0], $$("#add_author > form")[0]);
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
