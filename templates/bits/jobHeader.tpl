<table class="job">
  <tr>
    <th class="user-id">
      Utilizator
    </th>
    <td class="user-id">
      {include "bits/userTiny.tpl" user=$job->getUser()}
    </td>

    <th class="ip">
      IP
    </th>
    <td class="ip">
      {if Identity::mayViewIpAddresses()}
        {$job->remote_ip_info|default:'lipsește'}
      {else}
        ascuns
      {/if}
    </td>
  </tr>

  <tr>
    <th class="task-id">
      Problemă
    </th>
    <td class="task-id">
      {include "bits/taskLink.tpl" task=$job->getTask()}
    </td>

    <th class="compiler-id">
      Compilator
    </th>
    <td class="compiler-id">
      {$job->compiler_id}
      {if $job->isSourceViewable()}
        |
        {if $showSourceLink}
          <a href="{url_job_view_source($job->id)}">
            vezi sursa
          </a>
        {else}
          {$job->getSizeString()}
        {/if}
      {/if}
    </td>
  </tr>

  <tr>
    <th class="round-id">
      Rundă
    </th>
    <td class="round-id">
      {include "bits/roundLink.tpl" round=$job->getRound()}
    </td>

    <th class="status">
      Status
    </th>
    <td class="status">
      <strong>{$job->getShortStatusMessage()}</strong>
    </td>
  </tr>

  <tr>
    <th class="submit-time">
      Dată
    </th>
    <td class="submit-time">
      {$job->submit_time|fullDateTime}
    </td>

    <th class="score">
      Scor
    </th>
    <td class="score">
      {if $job->isScoreViewable()}
        {$job->score|default:'ascuns'}
      {else}
        ascuns
      {/if}
    </td>
  </tr>
</table>
