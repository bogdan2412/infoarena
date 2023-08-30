{extends "layout.tpl"}

{block "title"}Cod sursă (job #{$job->id}){/block}

{block "content"}
  <h1>
    Cod sursă (job
    <a href="{$job->id}">#{$job->id}</a>)
  </h1>

  {include "bits/jobHeader.tpl"}

  {if $job->needsToForceViewSource()}
    {Wiki::include('template/force-view-source-page')}

    <form method="post">
      <input
        class="button important"
        id="force_view_source"
        name="force_view_source"
        type="submit"
        value="Vezi sursa">
    </form>
  {else}
    <div class="code">
      {strip}
      <pre>
        <code>
          {$job->file_contents|escape}
        </code>
      </pre>
      {/strip}
    </div>
  {/if}

{/block}
