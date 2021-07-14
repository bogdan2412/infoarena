<?php
    require_once(IA_ROOT_DIR."www/views/task_edit_header.php");

    include(CUSTOM_THEME . 'header.php');
    $task_id = $view['task']['id'];
    $task_link = url_task($view['task']['id']);
    $task_title = $view['task']['title'];
    $action_link = url_task_edit($view['task']['id'], 'task-edit-tags');
    $task_tags = $view['task_tags'];

    echo task_edit_tabs($task_id, request("action"));
?>

<h1>Editare taguri <a href="<?= $task_link ?>"><?= $task_id ?></a></h1>
<form name="task_tags" action="<?= $action_link ?>" method="post">
<?php
    $tags_tree = $view['tags_tree'];
    foreach ($tags_tree as $tag) {
        echo '<h3>'.$tag['name'].'</h3><ul class="tag_list">';
        foreach ($tag['sub_tags'] as $sub_tag) {
            // Check if tag is already assigned to this task
            $checked = '';
            foreach ($task_tags as $task_tag) {
                if ($task_tag['id'] == $sub_tag['id']) {
                    $checked = "checked";
                }
            }
            echo '<li class="tag_list_item">
                    <input type="checkbox" '.$checked.' name="algorithm_tags[]" value="'.$sub_tag['id'].'"/> '
                    .$sub_tag['name'].
                '</li>';
        }
        echo "</ul>";
    }
?>
<input type="submit" class="button important" value="Salvează" />
</form>

<?php include('footer.php'); ?>
