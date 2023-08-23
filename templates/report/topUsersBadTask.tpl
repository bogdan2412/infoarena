{if $report->getLiveCount()}
  <form class="vspace-1" method="post">
    <button
      class="button"
      type="submit"
      name="report_action"
      value="cleanup">
      șterge tot
    </button>
  </form>

  <table>
    <thead>
      <tr>
        <th>problemă</th>
        <th>rundă</th>
        <th>utilizator</th>
        <th>job</th>
        <th>criteriu</th>
        <th>valoare</th>
        <th>dată</th>
      </tr>
    </thead>

    <tbody>
      {foreach $report->getTop() as $entry}
        <tr>
          <td>
            {$task=$entry->getTask()}
            {if $task}
              {include "bits/taskLink.tpl"}
            {else}
              {$entry->task_id}
            {/if}
          </td>

          <td>
            {include "bits/roundLink.tpl" round=$entry->getRound()}
          </td>
          
          <td>
            {include "bits/userTiny.tpl" user=$entry->getUser()}
          </td>

          <td>
            {$job=$entry->getJob()}
            {if $job}
              {include "bits/jobLink.tpl"}
            {else}
              #{$entry->job_id}
            {/if}
          </td>

          <td>
            {$entry->criteria}
          </td>
          
          <td>
            {$entry->special_score}
          </td>
          
          <td>
            {$entry->submit_time|fullDateTime}
          </td>
        </tr>
      {/foreach}
    </tbody>

  </table>
{else}
  Nu există înregistrări de top pentru probleme inexistente.
{/if}
