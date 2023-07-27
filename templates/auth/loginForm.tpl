{$referrer=$referrer|default:''}
{$remember=$remember|default:false}
{$username=$username|default:''}
<form action="{url_login()|escape}" class="login" method="post">
  <input type="hidden" name="referrer" value="{$referrer|escape}">

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
                Păstrează-mă autentificat 30 de zile.
              </label>
            </li>
          </ul>
        </fieldset>
      </td>
    </tr>
  </table>

  <ul class="form clear">
    <li>
      <input type="submit" value="Autentificare" id="form_submit" class="button important">
    </li>
  </ul>
</form>
