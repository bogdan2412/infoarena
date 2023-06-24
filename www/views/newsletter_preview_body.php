<?php

require_once(IA_ROOT_DIR.'common/newsletter.php');

echo newsletter_body_html($textblock, identity_get_user(), true);

?>
