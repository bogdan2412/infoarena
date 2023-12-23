<h3>
  {if $solved}
    Probleme din arhivă rezolvate
  {else}
    Probleme din arhivă încercate
  {/if}
  ({$tasks|count})
</h3>

<p>
  {foreach $tasks as $i => $task}
    {if $i}•{/if}
    {include "bits/taskLink.tpl"}
  {/foreach}
</p>
