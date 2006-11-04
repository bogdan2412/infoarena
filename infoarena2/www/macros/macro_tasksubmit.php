<?php

// Display solution submission form for a given task
//
// Arguments:
//      task_id (required)            Task identifier (without task/ prefix)
//
// Examples:
//      TaskSubmit(task_id="adunare")
function macro_tasksubmit($args) {
    $task_id = getattr($args, 'task_id');

    // validate arguments
    if (!$task_id) {
        return macro_error("Expecting parameter `task_id`");
    }

    // fetch & validate task
    $task = task_get($task_id);
    if (!$task) {
        return macro_error("Invalid task identifier");
    }

    // permission check
    if (!identity_can('task-submit', $task)) {
        return macro_permission_error();
    }

    // display form
    ob_start();
?>

<form enctype="multipart/form-data" action="<?= url('submit', array('action' => 'save')) ?>" method="post" class="inlineSubmit" id="task_submit">

<input type="hidden" id="output_only" value="<?= 'output-only' == $task['type'] ? $task['id'] : '' ?>" />

<ul class="form">
    <input type="hidden" name="task_id" value="<?= $task['id'] ?>" id="form_task" />

    <li id="field_solution">
        <label for="form_solution">Fisier solutie</label>
        <input type="file" name="solution" id="form_solution" />
    </li>

    <li id="field_compiler">
        <label for="form_compiler">Compilator</label>
        <select name="compiler_id" id="form_compiler">
            <option value="-">[ Alegeti compilator ]</option>
            <option value="c">GNU C</option>
            <option value="cpp">GNU C++</option>
            <option value="fpc">FreePascal</option>
        </select>
        <span class="fieldHelp"><a href="<?= url('Compilatoare') ?>">Detalii despre compilatoare</a></span>
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
