<?php

class :ia:login-form extends :x:element {
    children empty;

    protected function render() {
        // FIXME: remove old style global form_values and form_errors
        // FIXME: implement new XHP based way of displaying form fields.
        return
          <form action={url_login()} method="post" class="login">
            <fieldset>
              <legend><img src={url_static('images/icons/login.png')} alt="!" /> Autentificare</legend>
              <ul class="form">
                {HTML(view_form_field_li(array(
                  'name' => 'Cont de utilizator',
                  'type' => 'string',
                  'access_key' => 'c',
                ), 'username'))}
                {HTML(view_form_field_li(array(
                  'name' => 'Parola',
                  'type' => 'string',
                  'is_password' => true,
                  'access_key' => 'p',
                ), 'password'))}
                <li>
                  <input type="checkbox" value="on" id="form_remember" name="remember" class="checkbox" checked={fval('remember') ? 'checked' : null} />
                  <label class="checkbox" for="form_remember">Pastreaza-ma autentificat 5 zile</label>
                </li>
                <li>
                  <input type="submit" value="Autentificare" id="form_submit" class="button important" />
                </li>
              </ul>
            </fieldset>
          </form>;
    }
}
