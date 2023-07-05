{$username=$username|default:''}
{$remember=$remember|default:false}
{$captcha=$captcha|default:false}
<form action="{url_login()|escape}" class="login" method="post">
  <table class="form">
    <tr>
      <td>
        <fieldset>
          <legend>
            <img src="{url_static('images/icons/login.png')}" alt="!">
            Autentificare
          </legend>

          <ul class="form">
            <li id="field_username">
              <label for="form_username">Cont de utilizator</label>
              <input
                id="form_username"
                name="username"
                type="text"
                value="{$username}"
              >
            </li>
            <li id="field_password">
              <label for="form_password">Parolă</label>
              <input type="password" name="password" id="form_password">
            </li>
            <li>
              <input
                {if $remember}checked{/if}
                class="checkbox"
                id="form_remember"
                name="remember"
                type="checkbox">
              <label class="checkbox" for="form_remember">
                Păstrează-mă autentificat 5 zile
              </label>
            </li>
          </ul>
        </fieldset>
      </td>

      {if $captcha}
        <td>
          <fieldset>
            <legend>Verificare</legend>
            <ul class="form">
              <li>
                <script>
                  var RecaptchaOptions = {
                    theme : 'clean',
                  };
                </script>

                <label>Scrieți cuvintele de mai jos:</label>
                {* ferr_span('captcha') *}
                {* $view['captcha'] *}
                <span class="fieldHelp">
                  Vă rugăm să transcrieți cuvintele de mai sus în această
                  căsuță pentru verificare.
                </span>
              </li>
            </ul>
          </fieldset>
        </td>
      {/if}
    </tr>
  </table>

  <ul class="form clear">
    <li>
      <input type="submit" value="Autentificare" id="form_submit" class="button important">
    </li>
  </ul>
</form>
