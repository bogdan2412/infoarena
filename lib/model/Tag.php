<?php

class Tag extends Base {

  public static $_table = 'ia_tags';

  function getTaskSearchUrl(): string {
    return sprintf('%scauta-probleme?tag_id[]=%d',
                   Config::URL_PREFIX,
                   $this->id);
  }

}
