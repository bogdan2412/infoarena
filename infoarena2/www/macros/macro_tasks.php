<?php

// Lists all tasks attached to a given round
// Takes into consideration user permissions
//
// Synopsis:
// == Tasks() ==            
// == Tasks(round="preONI2006/1/9-10") ==
function macro_tasks($args) {
    if (!getattr($args['context'], 'round')) {
        return make_error_div('This macro needs a contest round context.');
    }

    // get round object and associated tasks
    $round_id = getattr($args, 'round', $args['context']['round']['id']);
    if ($round_id != $args['context']['page_name']) {
        $round = round_get($round_id);
    }
    else {
        $round = $args['context']['round'];
    }

    // get round tasks
    if ($round_id != $args['context']['page_name']) {
        $tasks = round_get_permitted_tasks($round_id, 'view');
    }
    else {
        $tasks = $args['context']['round_tasks'];
    }

    // generate HTML
    ob_start();

    if (0 < count($tasks)) {
?>
<ul class="tasks">
    <?php foreach ($tasks as $task) { ?>
        <li<?= $task['hidden'] ? ' class="hidden"' : '' ?>><a href="<?= url('task/' . $task['id']) ?>"><?= $task['title'] ?></a></li>
    <?php } ?>
</ul>
<?php
    }
    else {
?>

<em>Problemele nu sunt vizibile inca.</em>

<?php
    }

    $res = ob_get_contents();
    ob_end_clean();
    return $res;
}

?>
