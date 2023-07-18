<?php

class Round extends Base {

  public static $_table = 'ia_round';

  function isEditable(): bool {
    return Identity::ownsRound($this);
  }

}
