<?php

function macro_taskparameter($args)
{
    $context = $args['context'];
    if (!isset($context['task'])) {
        return make_error_div("This macro only works in task pages.");
    }
    $params = $context['task_parameters'];

    if (!isset($args['param'])) {
        return make_error_div("This macro needs a 'param' argument.");
    }
    $param = $args['param'];

    if ($param == 'author') {
        return $context['task']['author'];
    }
    if ($param == 'source') {
        return $context['task']['source'];
    }
    if ($param == 'type') {
        return $context['task']['type'];
    }
    if ($param == 'id') {
        return $context['task']['id'];
    }
    if (!isset($params[$param])) {
        return make_error_div("Task doesn't have parameter '$param'");
    }
    return $params[$param];
}
?>
