<?php

require_once(IA_ROOT_DIR . 'common/db/user.php');

class :ia:user-count extends :x:element {
    children empty;

    protected function render() {
        return
          <p class="user-count">
            {user_count()} membri înregistraţi
          </p>;
    }
}
