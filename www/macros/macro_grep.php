<?php

  require_once(Config::ROOT . "common/db/textblock.php");
  require_once(Config::ROOT . "www/format/format.php");

  // FIXME: document this macro
  function macro_grep($args) {
    $regexp = getattr($args, 'regexp');
    $substr = getattr($args, 'substr');
    $page = getattr($args, 'page');

    if (!$substr && !$regexp) {
      return macro_error('Expecting parameter `substr` or `regexp`');
    }
    if ($substr && $regexp) {
      return macro_error("Parameters `substr` and `regexp` can't be used together");
    }
    if (!$page) {
      return macro_error('Expecting parameter `page`');
    }

    if (!Identity::mayRunSpecialMacros()) {
      return macro_permission_error();
    }

    if ($substr) {
      $textblocks = textblock_grep($substr, $page, false);
    } else {
      $textblocks = textblock_grep($regexp, $page, true);
    }
    $textblocks_good = array();
    foreach ($textblocks as $textblock) {
      if (Identity::mayViewTextblock($textblock)) {
        $textblocks_good[] = $textblock;
      }
    }

    ob_start();
?>
<div class="macroToc">
  <p><strong><?= count($textblocks_good) ?></strong> rezultate.</p>
  <ul>
    <?php foreach ($textblocks_good as $textblock) { ?>
      <li><?= format_link(url_textblock($textblock['name']), $textblock['title']) ?></li>
    <?php } ?>
  </ul>
</div>
<?php
  $buffer = ob_get_clean();

  return $buffer;
  }

?>
