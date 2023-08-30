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
        {if $showSkips}
          <th>Ignoră submisii</th>
        {/if}
      </tr>
    </thead>

    <tbody>
      {foreach $jobs as $job}
        <tr>
          <td>
            {include "bits/jobLink.tpl"}
          </td>
          <td>
            {include "bits/userTiny.tpl" user=$job->getUser()}
          </td>
          <td>
            {include "bits/taskLink.tpl" task=$job->getTask()}
          </td>
          <td>
            {include "bits/roundLink.tpl" round=$job->getRound()}
          </td>
          <td>
            {include "bits/sourceLink.tpl"}
          </td>
          <td>
            {$job->submit_time|fullDateTime}
          </td>
          <td>
            {include "bits/jobStatus.tpl"}
          </td>
          {if $showSkips}
            <td>
              {include "bits/jobSkip.tpl"}
            </td>
          {/if}
        </tr>
      {/foreach}
    </tbody>
  </table>

  {include "bits/pager.tpl"}

  {if $showSkips}
    <div class="skip-job">
      <form
        action="job_skip"
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
