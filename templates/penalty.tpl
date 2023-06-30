{extends "layout.tpl"}

{block "title"}Penalty{/block}

{block "content"}
  <h1>Penalty</h1>

  <p>
    Introduceți numele celui căruia vreți să îi modificați punctajul și ID-ul
    concursului.
  </p>

  <form action="{url_penalty()}" method="post" class="login clear">
    <fieldset>
      <legend>Date generale</legend>
      <ul class="form">

        <li>
          <label for="form_username">Nume utilizator</label>
          {include "bits/fieldError.tpl" error=$formErrors.username|default:null}
          <input
            id="form_username"
            name="username"
            type="text"
            value="{$formValues.username|default:null|escape}">
        </li>

        <li>
          <label for="form_round_id">ID concurs</label>
          {include "bits/fieldError.tpl" error=$formErrors.round_id|default:null}
          {ferr_span('round_id')}
          <input
            id="form_round_id"
            name="round_id"
            type="text"
            value="{$formValues.round_id|default:null|escape}">
        </li>

      </ul>
    </fieldset>

    <ul class="form clear">
      <li>
        <input type="submit" value="Înainte" id="form_submit" class="button important">
      </li>
    </ul>
  </form>
{/block}
