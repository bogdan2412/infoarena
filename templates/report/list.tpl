{extends "layout.tpl"}

{block "title"}Rapoarte{/block}

{block "content"}

  <h1>Rapoarte</h1>

  <ul>
    {foreach $reports as $report}
      <li>
        <a href="{$report->getLinkName()}">
          {$report->getDescription()}
        </a>
        ({$report->getCachedCount()})
      </li>
    {/foreach}
  </ul>

{/block}
