<?php
require_once(IA_ROOT_DIR."common/round.php");
require_once(IA_ROOT_DIR."www/format/form.php");
require_once(IA_ROOT_DIR."www/views/round_edit_header.php");
require_once(IA_ROOT_DIR."www/macros/macro_tasks.php");
include(CUSTOM_THEME . 'header.php');

echo round_edit_tabs($view['round_id'], 'round-edit-task-order');
?>

<script>
    function do_post() {
        var formElem = document.task_order_form;

        var tables = formElem.getElementsByTagName("table");
        if (tables.length == 0) {
            formElem.submit();
        }
        var table = tables[0];

        var tbodies = table.getElementsByTagName("tbody");
        if (tbodies.length == 0) {
            formElem.submit();
        }
        var tbody = tbodies[0];

        var rows = tbody.getElementsByTagName("tr");
        if (rows.length == 0) {
            formElem.submit();
        }

        var order_id_list = "";
        for (var i = 0; i < rows.length; ++i) {
            var td = rows[i].childNodes[0];
            order_id_list += (i > 0 ? ";" : "") + td.innerHTML;
        }

        formElem.task_order.value = order_id_list;
        formElem.submit();
    }
</script>

<h1>Editare ordine probleme <?= format_link(url_textblock($round['page_name']), $round['title']) ?></h1>

<form name="task_order_form" action="<?= html_escape(url_round_edit_task_order($view['round_id']))?>" method="post">

<input type="hidden" name="task_order"/>

<?php
$args = array(
    'round_id' => $view['round_id'],
    'drag_and_drop' => true,
    'show_numbers' => true,
    'show_ratings' => true,
    'show_perspective_form' => false,
);
echo macro_tasks($args);
?>

<div class="submit">
    <ul class="form">
        <li id="field_submit">
            <input type="button" value="Salvează modificări"
             onclick="do_post()" class="button important">
        </li>
    </ul>
</div>

</form>

<?php include('footer.php'); ?>
