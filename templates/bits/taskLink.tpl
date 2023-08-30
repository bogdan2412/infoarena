{$taskId=$taskId|default:''}
{if !$task}
  {if $taskId}
    {$taskId}
  {else}
    Problema nu există!
  {/if}
{elseif !$task->isViewable()}
  ...
{else}
  <a href="{url_task($task->id)}">
    {$task->title}
  </a>
{/if}
