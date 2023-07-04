{* Mandatory arguments: $username, $name *}
{$rating=$rating|default:null}

<span class="tiny-user">
  <a href="{User::getProfileUrl($username)}">
    <img
      alt="avatar {$username}"
      src="{User::getAvatarUrl($username, 'tiny')}">
    {$name|escape}
  </a>

  <span>
    {format_user_ratingbadge($username, $rating)}
  </span>

  <span class="username">
    <a href="{User::getProfileUrl($username)}">
      {$username}
    </a>
  </span>
</span>
