{* Mandatory arguments: $user *}

<div class="normal-user">
  <a href="{$user->getProfileUrl()}">
    <img
      class="avatar-small"
      alt="avatar {$user->username}"
      src="{$user->getAvatarUrl('small')}">
  </a>

  <div class="fullname">{$user->full_name}</div>

  {format_user_ratingbadge($user->username, $user->rating_cache)}

  <a class="username" href="{$user->getProfileUrl()}">
    {$user->username}
  </a>
</div>
