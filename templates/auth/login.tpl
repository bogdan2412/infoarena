{extends "layout.tpl"}

{block "title"}Autentificare{/block}

{block "content"}
  <h1>Autentificare</h1>

  <p>
    Dacă nu ai un cont, te poți
    <a href="{url_register()}">
      înregistra
    </a>;

    dacă ți-ai uitat parola, o poți
    <a href="{url_resetpass()}">
      reseta aici
    </a>.
  </p>

  {include "auth/loginForm.tpl"}
  {Wiki::include('template/login')}
{/block}
