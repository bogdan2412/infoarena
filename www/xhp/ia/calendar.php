<?php

require_once(IA_ROOT_DIR . 'www/macros/macro_calendar.php');

class :ia:calendar extends :x:element {
    children empty;

    protected function render() {
        // FIXME: This should not be done like this.
        return
          <div id="calendar">
            {HTML(macro_calendar(array()))}
          </div>;
    }
}
