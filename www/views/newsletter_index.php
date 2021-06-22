<?php

global $identity_user;

require_once(IA_ROOT_DIR.'www/format/table.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/wiki/wiki.php');

$table_options = array(
    'css_class' => 'fill-screen newsletter',
    'show_count' => true,
);
$table_columns = array(
    array(
        'title' => 'Titlu',
        'key' => 'title',
        'rowform' => 'format_newsletter_title',
    ),
    array(
        'title' => 'Editor',
        'key' => 'username',
        'rowform' => 'format_newsletter_user',
    ),
    array(
        'title' => 'Data',
        'key' => 'creation_timestamp',
        'valform' => 'format_date',
    ),
);

function format_newsletter_title($row) {
    return format_link(url_textblock($row['name']), $row['title']);
}

function format_newsletter_user($row) {
    return format_user_tiny($row["user_name"], $row["user_fullname"],
        $row["rating_cache"]);
}

// site header
include(CUSTOM_THEME.'header.php');
echo format_tag('h1', html_escape($view['title']));

?>

<p>Aceasta este lista de newsletter-e trimise până acum membrilor infoarena
care s-au abonat să le primească.</p>

<div class="notice">
    <?php if (identity_is_anonymous()) { ?>

      <p>Ca să primeşti newsletter-ul infoarena trebuie să fii
        <a href="<?= url_register() ?>">înregistrat</a>.</p>

      <p>Dacă ai deja cont pe infoarena
        <a href="<?= url_account() ?>">verifică opţiunile contului tău</a>.</p>

    <?php } elseif ($identity_user['newsletter']) { ?>

      <p>Eşti abonat la newsletter-ul infoarena cu adresa de email
      <strong><em><?= $identity_user['email'] ?></em></strong>.</p>

      <p>Dacă nu mai doreşti să primeşti newsletter-ul infoarena, te poţi
      dezabona din <a href="<?= url_account(identity_get_username()) ?>">
      opţiunile contului tău</a>.</p>

    <?php } else { ?>

      <p><strong>Nu eşti abonat la newsletter-ul infoarena.</strong> :(

      Te rugăm sa te abonezi modificând
      <a href="<?= url_account(identity_get_username()) ?>">opţiunile contului
      tău</a>.</p>

      <p>Te poţi dezabona oricând ulterior.</p>

    <?php } ?>
</div>

<?php
echo format_table($letters, $table_columns, $table_options);

// site footer
include('footer.php');

?>
