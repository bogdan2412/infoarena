<?php include(CUSTOM_THEME . 'header.php');

// FIXME: copy/paste is stupid. Centralize field infos
$form_fields = array(
       'id' => array(
                'name' => 'Id-ul rundei',
                'description' => 'Identificator unic și permanent. Identificatorul poate fi alcătuit doar din litere mici ale alfabetului, cifre și caracterele punct (.), underscore (_), minus (-).',
                'type' => 'string',
                'default' => '',
        ),
        'type' => array(
                'name' => 'Tipul rundei',
                'type' => 'enum',
                'values' => $round_types,
                'default' => 'user-defined',
        ),
);

?>
<h1><?= html_escape($title) ?></h1>

<form action="<?= html_escape(url_round_create()) ?>"
      method="post"
      class="task create clear">
 <fieldset>
  <legend>Informații inițiale</legend>
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
