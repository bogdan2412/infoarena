<?php

// `data` dictionary is a dictionary with data to be displayed by form view
// when displaying the form for the first time, this is filled with
$data = array();

// here we store validation errors. It is a dictionary, indexed by field names
$errors = array();

// TODO: This is wrong.
$page_name = join($urlpath, '/');
$action = request('action', 'view');

$page = wikipage_get($page_name);
if (is_null($page)) {
    if ($action == 'view') {
        $action = 'edit';
        $page_content = "Scrie ba aici despre " . $page_name;
    }
    /*	else if $action {
    // TODO: error message?
    redirect("Home");
    }*/
} else {
    $page_content = $page['text'];
}

switch ($action) {
    case 'save':
        // Validate data here and place stuff in errors.
        $page_content = getattr($_POST, 'content', "");
        if (strlen($page_content) < 10) {
            $errors['content'] = "Scrie ma totusi ceva";
        }
        if (!$errors) {
            wikipage_add_revision($page_name, $page_content, 1);
            redirect(url($page_name));

            break;
        }
        else {
            $view['title'] = "Editare " . $page_name;
            $view['action'] = url($page_name, array('action' => 'save'));
            $data['content'] = $page_content;
            include("views/wikiedit.php");

            break;
        }

    case 'edit':
        // This is the creation action.
        $view['title'] = 'Creare ' . $page_name;
        $view['action'] = url($page_name, array('action' => 'save'));
        $data['content'] = $page_content;
        include('views/wikiedit.php');

        break;

        // View
    case 'view':
        $view['title'] = $page_name;
        $view['page_name'] = $page_name;
        $view['content'] = $page_content;

        include('views/wikipage.php');
        break;
}


?>
