<?php
    include(CUSTOM_THEME . 'header.php');
?>

<h1><?= html_escape($title) ?></h1>

<?php
    echo "Modifică punctajele per problemă pentru utilizatorul <b>".$view['user']['username']."</b> la concursul <b>".$view['round']['title']."</b>.";
    echo '<form action="?user_id='.$view['user']['id'].'&round_id='.$view['round']['id'].'" method="post" class="login clear">';
    echo '<fieldset>';
    echo '<legend>Scor total: '.$view['total_score'].'</legend>';
    echo '<ul class="form">';
    foreach ($view['tasks'] as $task) {
	    echo '<li>';
	    echo '<label for="form_'.$task['task_id'].'">'.$task['task_id'].'</label>';
	    echo "<?= ferr_span('".$task['task_id']."') ?>";
	    echo '<input type="text" name="'.$task['task_id'].'" id="form_'.$task['task_id'].'" value="'.$task['score'].'" />';
	    echo '</li>';
    }
    echo '</ul>';
    echo '</fieldset>';
    echo '<ul class="form clear">';
    echo '<li>';
    echo '<input type="submit" value="Modificare punctaj" id="form_submit" class="button important" />';
    echo '</li>';
    echo '</ul>';
    echo '</form>';
?>

<?php
    include('footer.php');
?>
