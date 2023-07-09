<?php
require_once(Config::ROOT . "www/views/utilities.php");

// Returns list of rounds to which a task can be submitted
// along with a default round
function task_get_submit_options($task_id) {
    // Fetch task parent rounds.
    $rounds = array();
    $round_ids = task_get_submit_rounds($task_id,
                                        identity_get_user());

    // Check if task is new and hasn't been added to any round yet
    if (count($round_ids) == 0) {
        $task = task_get($task_id);
        if (identity_can("task-submit", $task)) {
            $rounds[] = array("id" => "", "title" => "Inexistent");
        }
        $default = "";
    } else {
        // Try and determine what round a user wants to submit to.
        $default = getattr($_SESSION, '_ia_last_submit_round', NULL);
        if (!is_round_id($default) || !in_array($default, $round_ids)) {
            if (count($round_ids) == 1) {
                $default = $round_ids[0];
            } else {
                $rounds[] = array("id" => "", "title" => "[ Alegeți runda ]");
                $default = "";
            }
        }
        foreach($round_ids as $round_id) {
            $round = round_get($round_id);
            $rounds[] = array("id" => $round_id,
                              "title" => $round["title"]);
        }
    }

    return array("rounds" => $rounds, "default" => $default);
}

function solution_field() {
?>
    <li id="field_solution">
        <label for="form_solution">Fișier</label>
        <input type="file" name="solution" id="form_solution">
        <?= ferr_span('solution', false) ?>
    </li>
<?php
}

function compiler_field() {
    $cid = fval('compiler_id');
?>
    <li id="field_compiler">
        <label for="form_compiler">Compilator</label>
        <select name="compiler_id" id="form_compiler">
            <option value="-">[ Alegeți compilatorul ]</option>
            <?php foreach (Config::ENABLED_COMPILERS as $comp => $text) { ?>
                <option
                    value="<?= $comp ?>"
                    <?= ($comp == $cid) ? ' selected="selected"' : '' ?>>
                    <?= $text ?>
                </option>
            <?php } ?>
        </select>
        <?= ferr_span('compiler_id') ?>
    </li>
<?php
}

function multiple_round_warning($rounds) {
    if (count($rounds) > 1) {
?>
    <p class="submit-warning">Această problemă face parte din mai multe concursuri. Selectează-l pe cel la care participi!</p>
<?php
    }
}

function round_field($rounds, $default_round, $warning_field = true) {
?>
    <li id="field_round">
<?php if ($warning_field) { ?>
        <div id="field_round_warning"><?php multiple_round_warning($rounds); ?></div>
<?php } ?>
        <label for="form_round">Concurs</label>
        <select name="round_id" id="form_round">
<?php foreach ($rounds as $round) { ?>
            <option value="<?= html_escape($round['id']) ?>"<?= ($round['id'] == $default_round) ? ' selected="selected"' : ''?>><?= html_escape($round['title']) ?></option>
<?php } ?>
        </select>
        <?= ferr_span('round_id', false) ?>
    </li>
<?php
}

function task_field($tasks) {
?>
    <li id="field_task">
        <label for="form_task">Problema</label>
        <select name="task_id" id="form_task">
            <option value="">[ Alegeți problema ]</option>
<?php foreach ($tasks as $task) {  ?>
            <option value="<?= html_escape($task['id']) ?>"<?= fval('task_id') == $task['id'] ? ' selected="selected"' : '' ?>><?= html_escape($task['title']) ?></option>
<?php } ?>
        </select>
        <?= ferr_span('task_id') ?>
    </li>
<?php
}

function display_submit_form($inline_form = false, $task_id = null) {
?>
<form enctype="multipart/form-data" action="<?= html_escape(url_submit()) ?>" method="post" class="<?= $inline_form ? "inlineSubmit" : "submit" ?>" id="task_submit">

<?php
    if ($task_id) {
        list($rounds, $default_round) =
            array_values(task_get_submit_options($task_id));
    } else {
        log_assert(!$inline_form, "Must specify task_id to display inline submit form");
        $rounds = array();
        $default_round = NULL;
    }
    if ($inline_form) {
?>
<input type="hidden" name="task_id" value="<?= html_escape($task_id) ?>" id="form_task">
<ul class="form">
<?php
        solution_field();
        round_field($rounds, $default_round, false);
        compiler_field();
    } else {
?>
<ul class="form">
<?php
        global $tasks;
        task_field($tasks);
        round_field($rounds, $default_round);
        solution_field();
        compiler_field();
    }
?>
    <li id="field_submit">
        <input type="submit" class="button" value="Trimite" id="form_submit">
    </li>
</ul>
</form>
<?php
    if ($inline_form) {
        multiple_round_warning($rounds);
    }
}
?>
