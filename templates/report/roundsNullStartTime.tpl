<p>
  Notă: Legăturile „șterge” șterg runda <span class="text-danger">fără alte
  confirmări</span>. Dacă runda nu are probleme atașate și surse trimise,
  probabil este OK să o ștergi.
</p>

<table>
  <thead>
    <tr>
      <th>rundă</th>
      <th>creator</th>
      <th>tip</th>
      <th>stare</th>
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
          {$round->state}
        </td>

        <td class="number">
          {$round->countTasks()}
        </td>

        <td class="number">
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
