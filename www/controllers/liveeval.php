<?php

require_once(Config::ROOT."common/db/job.php");
require_once(Config::ROOT."common/db/task.php");
require_once(Config::ROOT."www/controllers/job_filters.php");

function controller_liveeval() {
    global $identity_user;
    identity_require("job-liveeval");

    $view = array();
    if (!request("rounds")) {
        FlashMessage::addError("Parametrul rounds este obligatoriu pentru liveeval.");
        redirect(url_home());
    }

    $rounds = request("rounds");
    $rounds = explode("|", $rounds);
    $filters = array("round" => $rounds);
    $job_data = job_get_range($filters, 0, job_get_count($filters));

    $view["title"] = 'Monitorul de evaluare';
    $view['jobs'] = $job_data;

    execute_view_die('views/liveeval.php', $view);
}
