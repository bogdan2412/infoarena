{extends "layout.tpl"}

{block "title"}Monitorul de evaluare{/block}

{block "content"}
  {if $showReevalForm}
    <form
      action="{url_reeval($filters)}"
      class="reeval"
      enctype="multipart/form-data"
      id="job_reeval"
      method="post"
      onsubmit="return confirm('Confirmi reevaluarea a {$jobCount} de joburi?');">

      <ul class="form hollyfix">
        <li id="field_submit">
          <input
            class="button important"
            id="form_reeval"
            type="submit"
            value="Re-evaluează!">
        </li>
      </ul>

    </form>
  {/if}

  <h1>Monitorul de evaluare</h1>

  {if count($tabs) > 1}
    {format_ul($tabs, 'htabs')}
  {/if}

  <div id="monitor-table">
    {include "bits/monitorTable.tpl"}
  </div>

  <p>
    <input
      {if $smarty.const.MONITOR_AUTOREFRESH}checked{/if}
      data-config="{$smarty.const.MONITOR_AUTOREFRESH}"
      data-interval="{$smarty.const.MONITOR_AUTOREFRESH_INTERVAL}"
      id="autorefresh"
      type="checkbox">

    <label for="autorefresh">
      auto refresh monitor
    </label>
  </p>

  <br>

  <p>
    <a href="{Config::URL_PREFIX}documentatie/monitorul-de-evaluare">
      Ce este și cum se folosește
    </a>
    monitorul de evaluare.
  </p>

{/block}
