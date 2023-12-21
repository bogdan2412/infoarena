{extends "layout.tpl"}

{block "title"}Editare penalizare{/block}

{block "content"}
  <h1>Editare penalizare</h1>

  Modifică punctajele per problemă pentru utilizatorul <b>{$user->username}</b>
  la runda <b>{$round->title}</b>.
  
  <form method="post" class="login clear">
    <fieldset>
      <legend>Scor total: {$total->score}</legend>
      <ul class="form">
        {foreach $scores as $score}
	        <li>
	          <label for="field_{$score->task_id}">{$score->task_id}</label>
	          {ferr_span('{$score->task_id}')}
	          <input
              id="field_{$score->task_id}"
              name="score_{$score->task_id}"
              type="text"
              value="{$score->score}">
	        </li>
        {/foreach}
      </ul>
    </fieldset>

    <ul class="form clear">
      <li>
        <input
          class="button important"
          id="form_submit"
          type="submit"
          value="Salvează">
      </li>
    </ul>
  </form>
{/block}
