{if !$task}
  Problema nu există!
{elseif !$task->isViewable()}
  ...
{else}
  <a href="{$task->page_name}">
    {$task->title}
  </a>
{/if}
