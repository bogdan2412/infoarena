<?php

require_once(Config::ROOT."common/db/round.php");
require_once(Config::ROOT."www/macros/macro_include.php");

// Display registration invitation for a round when user is not registered.
// If user is already registered, display a confirmation message instead.
//
// Arguments:
//      round_id (required)
//          - valid round id
//
// Examples:
//      RoundRegister()
function macro_roundregister($args) {
  $round_id = getattr($args, 'round_id');
  if (!is_round_id($round_id)) {
    return macro_error('Invalid round identifier');
  }

  // validate round id
  $round = round_get($round_id);
  if (!$round) {
    return macro_error('Invalid round identifier');
  }
  $round_parameters = round_get_parameters($round_id);
  if (!getattr($round_parameters, "rating_update")) {
    return "";
  }

  $is_registered = Identity::isLoggedIn() &&
    round_is_registered($round['id'], Identity::getId());

  if ($is_registered) {
    $class = "round-registered";
    $msg = "<p>Te-ai înscris la <em>".html_escape($round['title'])."</em>."
      ." <a href=\"".html_escape(url_round_register_view($round['id']))."\">"
      ."Vezi cine s-a mai înscris"
      ."</a>.</p>";

    if ($round['state'] == 'waiting') {
      $msg .= "<p>În caz că nu mai poți participa te poți dezînscrie"
        ." <a href=\"".html_escape(url_round_register($round['id']))
        ."\">aici</a>.</p>";
    }
  }
  else {
    // too late?
    if ('waiting' == $round['state']) {
      $class = "round-register";
      $msg = "<p>Nu ești înscris la "
        ."<em>".html_escape($round['title'])."</em>.   "
        ."Dacă vrei să ți se modifice rating-ul după "
        ."acest concurs, trebuie să te înscrii până la ora "
        .format_date($round['start_time'], 'HH:mm, d MMMM yyyy').".</p>"
        ."<p><a href=\"".html_escape(url_round_register($round['id']))."\">"
        ."<strong>Înscrie-te acum!</strong></a> &nbsp; "
        ." <a href=\"".html_escape(url_round_register_view($round['id']))."\">"
        ."Vezi cine e înscris"
        ."</a></p>"
        ."<p>Poți să participi la concurs și fără să te înscrii, "
        ."însă nu ți se va schimba rating-ul.</p>";
    }
    elseif ('running' == $round['state']) {
      $class = "round-expired";
      $msg = "<p>Nu se mai pot face înscrieri la "
        ."<em>".html_escape($round['title'])."</em>, "
        ."<strong><em>însă mai poți participa</em></strong>.</p>"
        ."<p>Trebuia să te înscrii înainte de ora "
        .format_date($round['start_time'], 'HH:mm, d MMMM yyyy')
        ." dacă voiai să ți se modifice rating-ul la finalul "
        ."rundei. Acum poți să participi, dar nu ți se va modifica "
        ."rating-ul.</p>"
        ."<p><a href=\"".html_escape(url_round_register_view($round['id']))."\">"
        ."Vezi cine s-a înscris"
        ."</a></p>";
    }
    else {
      // 'complete' == $round['state']
      $class = "round-expired";
      $msg = "<p>Nu se mai pot face inscrieri la "
        ."<em>".html_escape($round['title'])."</em>. "
        ."Runda s-a incheiat.</p>"
        ."<p><a href=\"".html_escape(url_round_register_view($round['id']))."\">"
        ."Vezi cine s-a înscris"
        ."</a></p>";
    }
  }

  $msg = "<div class=\"{$class}\">{$msg}</div>";

  return $msg;
}

?>
