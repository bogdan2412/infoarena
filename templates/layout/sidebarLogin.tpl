{$showSidebarLogin=$showSidebarLogin|default:true}

{if User::isAnonymous() && $showSidebarLogin}
  <div id="login">
    {include "auth/loginForm.tpl"}
    <p>
      <a href="{url_register()}">Mă înregistrez</a>
      <br>
      <a href="{url_resetpass()}">Mi-am uitat parola</a>
    </p>
  </div>
{/if}
