{if empty($jobs)}
  <div>
    Nici o soluție în coada de evaluare.
  </div>
{else}
  {include "bits/pager.tpl"}

  <table class="alternating-colors monitor">
    <thead>
      <tr>
        <th>ID</th>
        <th>Utilizator</th>
        <th>Problemă</th>
        <th>Rundă</th>
        <th>Mărime</th>
        <th>Dată</th>
        <th>Stare</th>
        {if $anySkippableJobs}
          <th>Ignoră submisii</th>
        {/if}
      </tr>
    </thead>

    <tbody>
      {foreach $jobs as $job}
        <tr>
          <td>
            <a href="{url_job_detail($job.id)}">#{$job.id}</a>
          </td>
          <td>
            {include "bits/userTiny.tpl" username=$job.user_name name=$job.user_fullname}
          </td>
          <td>
            {format_task_link($job)}
          </td>
          <td>
            {format_round_link($job)}
          </td>
          <td>
            {format_size($job)}
          </td>
          <td>
            {format_short_date($job.submit_time)}
          </td>
          <td>
            {format_state($job)}
          </td>
          {if $anySkippableJobs}
            <td>
              {format_skip($job)}
            </td>
          {/if}
        </tr>
      {/foreach}
    </tbody>
  </table>

  {include "bits/pager.tpl"}

  {if $anySkippableJobs}
    <div class="skip-job">
      <form
        action="{$skipUrl|escape}"
        class="reeval"
        id="skip-jobs-form"
        method="post">
        <input type="hidden" name="skipped-jobs" id="skipped-jobs">
        <input type="checkbox" id="skip-all-checkbox">
        <input type="submit" class="button important" value="Ignoră joburile selectate">
      </form>
    </div>
  {/if}
{/if}
