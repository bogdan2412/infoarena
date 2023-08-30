<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>utilizator</th>
      <th>problemă</th>
      <th>rundă</th>
      <th>dată</th>
      <th>acțiuni</th>
    </tr>
  </thead>

  <tbody>
    {foreach $report->getJobs() as $job}
      <tr>
        <td>
          {include "bits/jobLink.tpl"}
        </td>

        <td>
          {include "bits/userTiny.tpl" user=$job->getUser()}
        </td>

        <td>
          {include "bits/taskLink.tpl" task=$job->getTask() taskId=$job->task_id}
        </td>

        <td>
          {include "bits/roundLink.tpl" round=$job->getRound()}
        </td>

        <td>
          {$job->submit_time|fullDateTime}
        </td>

        <td>
          <a href="?report_action=job_delete&amp;job_id={$job->id}">șterge</a>
        </td>
      </tr>
    {/foreach}
  </tbody>

</table>
