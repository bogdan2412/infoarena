<div class="clear" id="header">
  {if $identity}
    <div id="userbox">
      <a href="{User::getProfileUrl($identity.username)}">
        <img
          alt="imagine de profil {$identity.username}"
          src="{User::getAvatarUrl($identity.username, 'normal')}">
      </a>

      <div class="user">
        <strong>{$identity.full_name|escape}</strong>
        <br>

        {include "bits/ratingBadge.tpl" rb=$ratingBadge}
        <a accesskey="p" href="{User::getProfileUrl($identity.username)}">
          {$identity.username}
        </a>
        <br>

        <a class="logout" href="{url_logout()}">logout</a>
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

  <h1>
    <a href="{Config::URL_PREFIX}">
      {$smarty.const.SITE_NAME} informatică de performanță
    </a>
  </h1>
</div>
