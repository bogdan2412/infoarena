<?php

// Lists all tasks attached to a given round
// Takes into consideration user permissions.
//
// Arguments;
//      round_id (required)     Round identifier
//
// Examples:
//      Tasks(round_id="archive")
function macro_tasks($args) {
    $round_id = getattr($args, 'round_id');
    if (!$round_id) {
        return macro_error('Expecting argument `round_id`');
    }

    // fetch round info
    $round = round_get($round_id);
    if (!$round) {
        return macro_error('Invalid round identifier');
    }
    if (!identity_can('round-view', $round)) {
        return macro_permission_error();
    }

    // get round tasks
    $tasks = round_get_permitted_tasks($round_id, 'view');

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
