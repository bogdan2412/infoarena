<?php

require_once(IA_ROOT . "common/db/task.php");

// Display solution submission form for a given task
//
// Arguments:
//      task_id (required)            Task identifier (without prefix)
//
// Examples:
//      TaskSubmit(task_id="adunare")
function macro_tasksubmit($args) {
    global $identity_user;
    $task_id = getattr($args, 'task_id');

    // validate arguments
    if (!is_task_id($task_id)) {
        return macro_error("Expecting parameter `task_id`");
    }

    // fetch & validate task
    $task = task_get($task_id);
    if (!$task) {
        return macro_error("Invalid task identifier");
    }

    if (identity_anonymous()) {
        $url = htmlentities(url_login());
        return macro_message("Trebuie sa te autentifici pentru a trimite solutii. <a href=\"{$url}\">Click aici</a>", true);
    }

    // Permission check. Should never fail right now.
    if (!identity_can('task-submit', $task)) {
        return macro_message("Nu se (mai) pot trimite solutii la aceasta problema.", true);
    }

    // display form
    ob_start();
?>

<form enctype="multipart/form-data" action="<?= htmlentities(url_submit()) ?>" method="post" class="inlineSubmit" id="task_submit">

<input type="hidden" id="output_only" value="<?= 'output-only' == $task['type'] ? htmlentities($task['id']) : '' ?>" />
<input type="hidden" name="task_id" value="<?= htmlentities($task['id']) ?>" id="form_task" />

<ul class="form">

    <li id="field_solution">
        <label for="form_solution">Fisier solutie</label>
        <input type="file" name="solution" id="form_solution" />
    </li>

    <li id="field_compiler">
        <label for="form_compiler">Compilator</label>
        <select name="compiler_id" id="form_compiler">
            <option value="-">[ Alege ]</option>
            <option value="c">GNU C</option>
            <option value="cpp">GNU C++</option>
            <option value="fpc">FreePascal</option>
        </select>
    </li>

    <li id="field_submit">
        <input type="submit" class="button" value="Trimite solutia" id="form_submit" />
    </li>
</ul>
</form>


<?php
    $buffer = ob_get_contents();
    ob_end_clean();

    // done
    return $buffer;
}

?>
