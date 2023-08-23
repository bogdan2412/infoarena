{extends "layout.tpl"}

{block "title"}Raport: {$report->getDescription()}{/block}

{block "content"}

  <h1>Raport: {$report->getDescription()}</h1>

  {include $report->getTemplateName()}

{/block}
