<table>
  <thead>
    <tr>
      <th>rundă</th>
      <th>creator</th>
      <th>tip</th>
      <th>probleme</th>
      <th>surse</th>
      <th>ultima editare</th>
      <th>acțiuni</th>
    </tr>
  </thead>

  <tbody>
    {foreach $report->getRounds() as $round}
      <tr>
        <td>
          {include "bits/roundLink.tpl"}
          {if $round->hasNonstandardPage()}
            <span class="text-danger">
              (pagina: {$round->page_name})
            </span>
          {/if}
        </td>
        
        <td>
          {include "bits/userTiny.tpl" user=$round->getUser()}
        </td>

        <td>
          {$round->type}
        </td>
        
        <td>
          {$round->countTasks()}
        </td>
        
        <td>
          {$round->countJobs()}
        </td>
        
        <td>
          {$last=$round->getLastEdit()}
          {if $last}
            {$last|fullDateTime}
          {else}
            necunoscut (pagină lipsă)
          {/if}
        </td>

        <td>
          <a href="?report_action=round_delete&amp;round_id={$round->id}">șterge</a>
        </td>
      </tr>
    {/foreach}
  </tbody>

</table>
</ol>
