{* Mandatory arguments: $user *}

<div class="normal-user">
  <a href="{User::getProfileUrl($user->username)}">
    <img
      class="avatar-small"
      alt="avatar {$user->username}"
      src="{User::getAvatarUrl($user->username, 'small')}">
  </a>

  <div class="fullname">{$user->full_name}</div>

  {format_user_ratingbadge($user->username, $user->rating_cache)}

  <a class="username" href="{User::getProfileUrl($user->username)}">
    {$user->username}
  </a>
</div>
