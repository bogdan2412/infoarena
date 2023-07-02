<?php
    require_once 'header.php';
?>

<h1><?= html_escape($title) ?></h1>

<p>
  Introduceți numele celui căruia vreți să îi modificați punctajul și ID-ul
  concursului.
</p>

<form action="<?= html_escape(url_penalty()) ?>" method="post" class="login clear">
  <fieldset>
    <legend>Date generale</legend>
    <ul class="form">

      <li>
        <label for="form_username">Nume utilizator</label>
        <?= ferr_span('username') ?>
        <input type="text" name="username" id="form_username" value="<?= fval('username') ?>">
      </li>

      <li>
        <label for="form_round_id">ID concurs</label>
        <?= ferr_span('round_id') ?>
        <input type="text" name="round_id" id="form_round_id" value="<?= fval('round_id') ?>">
      </li>

    </ul>
  </fieldset>

  <ul class="form clear">
    <li>
      <input type="submit" value="Înainte" id="form_submit" class="button important">
    </li>
  </ul>
</form>

<?php
    include('footer.php');
?>
