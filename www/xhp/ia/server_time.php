<?php

require_once(IA_ROOT_DIR . 'www/format/format.php');

class :ia:server-time extends :x:element {
    children empty;

    protected function render() {
        return
          <x:frag>
            <div id="srv_time" class="user-count"  />
            <script type="text/javascript" src={url_static('js/time.js')} />
            <script type="text/javascript">{'loadTime(' . format_date(null, "%H, %M, %S") . ');'}</script>
           </x:frag>;
    }
}
