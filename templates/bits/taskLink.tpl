{if !$task}
  Problema nu există!
{elseif !$task->isViewable()}
  ...
{else}
  <a href="{url_task($task->id)}">
    {$task->title}
  </a>
{/if}
