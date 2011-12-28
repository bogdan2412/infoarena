<?php

require_once(IA_ROOT_DIR . 'www/xhp/ui/base.php');

class :ui:flash-message extends :ui:element {
    attribute
        string class;

    protected function render() {
        return
          <div class={'flash ' . $this->getAttribute('class')}>
            {$this->getChildren()}
          </div>;
    }
}
