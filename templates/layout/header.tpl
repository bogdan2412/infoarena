<div class="clear" id="header">
  {if $identity}
    <div id="userbox">
      <a href="{User::getProfileUrl($identity->username)}">
        <img
          class="avatar-normal"
          alt="imagine de profil {$identity->username}"
          src="{User::getAvatarUrl($identity->username, 'normal')}">
      </a>

      <div class="user">
        <strong>{$identity->full_name|escape}</strong>
        <br>

        {include "bits/ratingBadge.tpl" rb=$ratingBadge}
        <span id="active-username">
          <a href="{User::getProfileUrl($identity->username)}">
            {$identity->username}
          </a>
        </span>
        <br>

        <a
          class="logout"
          href="javascript:PostData('{url_logout()}', [])">
          logout
        </a>
        |
        <a accesskey="c" href="{User::getAccountUrl()}">
          <span class="access-key">c</span>ontul meu
        </a>
      </div>
    </div>
  {/if}

  {if Config::DEVELOPMENT_MODE}
    <div id="dev_warning">
      Bravely working in development mode&hellip;<br>Keep it up!
    </div>
  {/if}

  <a class="homepage-link" href="{Config::URL_PREFIX}">
    {Config::SITE_NAME} — informatică de performanță
  </a>
</div>
