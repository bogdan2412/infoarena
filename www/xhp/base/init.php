<?php
require_once 'core.php';
require_once 'html.php';

// Infoarena-only hack. The XHP codebase allows one to disable escaping of a
// string by using something like HTML('...'), but that was not defined
// anywhere. This makes it work, but is extremely limited.
function HTML($str) {
  return new HTML($str);
}

class HTML {
  function __construct($str) {
    $this->str = $str;
  }

  function render() {
    return $this->str;
  }
}
