<?php

/**
 * Everything to do with parsing and rendering Textile, LaTex and in-house macros.
 **/

class Wiki {

  // Parses and prints a textblock. Use this to insert dynamic textblocks
  // inside static templates / views.
  static function include($page_name, $template_args = null, $div = true) {
    $textblock = textblock_get_revision($page_name);
    log_assert($textblock, "Nu am găsit $page_name");

    if ($div) {
      echo '<div class="wiki_text_block">';
    }
    if (!is_null($template_args)) {
      textblock_template_replace($textblock, $template_args);
      // No caching, we're using template magic.
      echo wiki_process_text($textblock['text']);
    } else {
      echo wiki_process_textblock($textblock);
    }
    if ($div) {
      echo '</div>';
    }
  }

}
