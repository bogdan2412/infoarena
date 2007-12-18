<?php include('views/header.php'); ?>
<h1>Administrare <?= format_link(url_textblock($page['name']), $page['title']) ?></h1>
<form action="<?= htmlentities(getattr($view, 'action')) ?>" method="post">
 <fieldset>
  <legend>Informatii generale</legend>
  <ul class="form">
   <?= view_form_field_li(array('name' => 'Topic forum', 'default' => $form_values['topic_id'], 'type' => 'string'), 'topic_id') ?>
  </ul>
 </fieldset>
 <div class="submit">
  <ul class="form">
   <li id="field_submit">
    <input type="submit"
           value="Salveaza"
           id="form_submit"
           class="button important" />
   </li>
  </ul>
 </div>
</form>
<?php include('footer.php'); ?>
