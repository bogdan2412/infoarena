<?php
  // Show wiki operations.
  // Only show operations the current user can do.

  // Check view parameters.
  require_once(Config::ROOT . "common/textblock.php");
  log_assert_valid(textblock_validate($textblock));
?>
<div id="wikiOps">
  <ul>
    <?php if (Identity::mayEditTextblockReversibly($textblock)) { ?>
      <li><?= format_link_access(url_textblock_edit($textblock['name']), 'Editează', 'e') ?></li>
    <?php } ?>
    <?php if (Identity::mayViewTextblock($textblock)) { ?>
      <li><?= format_link_access(url_textblock_history($textblock['name']), 'Istoria', 'i') ?></li>
    <?php } ?>
    <?php if (Identity::mayMoveTextblock($textblock)) { ?>
      <li><?= format_link_access(url_textblock_move($textblock['name']), 'Mută', 'u') ?></li>
    <?php } ?>
    <?php if (Identity::mayEditTextblockReversibly($textblock)) { ?>
      <li><?= format_link_access(url_textblock_copy($textblock['name']), 'Copiază', 'c') ?></li>
    <?php } ?>
    <?php if (Identity::mayDeleteTextblock($textblock)) { ?>
      <li><?= format_post_link(
            url_textblock_delete($textblock['name']),
            'Șterge', array(), true, array('onclick' =>
              "return confirm('Această acțiune este ireversibilă! Dorești să continui?')"),
            'r') ?>
      </li>
    <?php } ?>
    <?php if (Identity::mayEditTextblockReversibly($textblock)) { ?>
      <li><?= format_link_access(url_attachment_new($textblock['name']), 'Atașează', 'a') ?></li>
    <?php } ?>
    <?php if (Identity::mayViewTextblock($textblock)) { ?>
      <li><?= format_link_access(url_attachment_list($textblock['name']), 'Listează atașamente', 'l') ?></li>
    <?php } ?>
  </ul>
</div>
