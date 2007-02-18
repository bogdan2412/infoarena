<?php include('header.php');

// FIXME: copy/paste is stupid. Centralize field infos
$form_fields = array(
       'id' => array(
                'name' => 'Id-ul rundei',
                'description' => 'Identificator unic si permanent.',
                'type' => 'string',
                'default' => '',
        ),
        'type' => array(
                'name' => 'Tipul rundei',
                'type' => 'enum',
                'values' => round_get_types(),
                'default' => 'classic',
        ), 
);

?>

<h1><?= htmlentities($title) ?></h1>

<form action="<?= htmlentities(url_round_create()) ?>"
      method="post"
      class="task create clear">
 <fieldset>
  <legend>Informatii initiale</legend>
  <ul class="form">
   <?= view_form_field_li($form_fields['id'], 'id') ?>
   <?= view_form_field_li($form_fields['type'], 'type') ?>
  </ul>
 </fieldset>
 <div class="submit">
  <ul class="form">
   <li id="field_submit">
    <input type="submit"
           value="Creeaza runda"
           id="form_submit"
           class="button important" />
   </li>
  </ul>
 </div>
</form>

<?php include('footer.php'); ?>
