<?php
    include('header.php');
    $task_link = url_task($view['task']['id']);
    $task_title = $view['task']['title'];
    $action_link = url_task_edit_tags($view['task']['id']);
    $task_tags = $view['task_tags'];
?>
<h1>Taguri pentru problema <a href="<?= $task_link ?>"><?= $task_title ?></a></h1>
<form name="task_tags" action="<?= $action_link ?>" method="post">
<?php
    $tags_tree = $view['tags_tree'];
    foreach ($tags_tree as $tag) {
        echo '<h3>'.$tag['tag_name'].'</h3><ul class="tag_list">';
        foreach ($tag['sub_tags'] as $sub_tag) {
            // Check if tag is already assigned to this task
            $checked = '';
            foreach ($task_tags as $task_tag) {
                if ($task_tag['tag_id'] == $sub_tag['tag_id']) {
                    $checked = "checked";
                }
            }
            echo '<li class="tag_list_item">
                    <input type="checkbox" '.$checked.' name="algorithm_tags[]" value="'.$sub_tag['tag_id'].'"/> '
                    .$sub_tag['tag_name'].
                '</li>';
        }
        echo "</ul>";
    }
?>
<input type="submit" class="button important" value="Submit" />
</form>

<?php include('footer.php'); ?>
