{* Mandatory arguments: $user *}
{$rating=$rating|default:null}

<span class="tiny-user">
  <a href="{User::getProfileUrl($user->username)}">
    <img
      alt="avatar {$user->username}"
      src="{User::getAvatarUrl($user->username, 'tiny')}">
    {$user->full_name|escape}
  </a>

  <span>
    {format_user_ratingbadge($user->username, $rating)}
  </span>

  <span class="username">
    <a href="{User::getProfileUrl($user->username)}">
      {$user->username}
    </a>
  </span>
</span>
