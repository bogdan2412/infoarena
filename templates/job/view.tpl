{extends "layout.tpl"}

{block "title"}Borderou de evaluare (job #{$job->id}){/block}

{block "content"}
  <h1>Borderou de evaluare (job #{$job->id})</h1>

  <table class="job">
    <tr>
      <th class="user-id">
        Utilizator
      </th>
      <td class="user-id">
        {include "bits/userTiny.tpl" user=$job->getUser()}
      </td>
      <th class="submit-time">
        Dată
      </th>
      <td class="submit-time">
        {$job->submit_time|fullDateTime}
      </td>
    </tr>

    <tr>
      <th class="task-id">
        Problemă
      </th>
      <td class="task-id">
        {include "bits/taskLink.tpl" task=$job->getTask()}
      </td>
      <th class="status">
        Status
      </th>
      <td class="status">
        <strong>{$job->getShortStatusMessage()}</strong>
      </td>
    </tr>

    <tr>
      <th class="round-id">
        Rundă
      </th>
      <td class="round-id">
        {include "bits/roundLink.tpl" round=$job->getRound()}
      </td>
      <th class="compiler-id">
        Compilator
      </th>
      <td class="compiler-id">
        {$job->compiler_id}
        {if $job->isSourceViewable()}
          |
          <a href="{url_job_view_source($job->id)}">
            vezi sursa
          </a>
        {/if}
      </td>
    </tr>

    <tr>
      <th class="score">
        Scor
      </th>
      {$colspan = (Identity::mayViewIpAddresses()) ? 1 : 3}
      <td class="score" colspan="{$colspan}">
        {if $job->isScoreViewable()}
          {$job->score|default:'ascuns'}
        {else}
          ascuns
        {/if}
      </td>

      {if Identity::mayViewIpAddresses()}
        <th class="ip">
          IP
        </th>
        <td class="ip">
          {$job->remote_ip_info|default:'lipsește'}
        </td>
      {/if}
    </tr>
  </table>

  <h2>Raport evaluator</h2>

  {if $job->isDone()}
    <div class="job-eval-log">
      {$job->eval_log|escape}
    </div>
  {else}
    <p>Această sursă nu a fost evaluată încă.</p>
  {/if}

  {if $showScoreTable}
    <table class="job-eval-tests alternating-colors">
      <thead>
        <tr>
          {if $showFeedbackColumn}
            <th>Public</th>
          {/if}
          <th>Test</th>
          <th>Timp execuție</th>
          <th>Memorie folosită</th>
          <th>Mesaj</th>
          <th>Punctaj/test</th>
          {if $showGroups}
            <th>Punctaj/grupă</th>
          {/if}
        </tr>
      </thead>

      <tbody>
        {foreach $tests->getGroups() as $groupNo => $group}
          {foreach $group as $testIdx => $testNo}
            {if $tests->isTestViewable($testNo)}
              {$test=$tests->getJobTest($testNo)}
              <tr class="{$tests->getGroupCssClass($groupNo)}">
                {if $showFeedbackColumn}
                  <td class="number">
                    {if $tests->isPublicTest($testNo)}
                      ✓
                    {else}
                      ✗
                    {/if}
                  </td>
                {/if}
                <td class="number">
                  {$testNo}
                </td>
                <td class="number">
                  {$test->getTimeUsedMessage()}
                </td>
                <td class="number">
                  {$test->getMemoryUsedMessage()}
                </td>
                <td>
                  {$test->grader_message|escape}
                </td>
                <td class="number">
                  {$test->points}
                </td>
                {if $showGroups && $testIdx == 0}
                  <td class="number" rowspan="{$group|count}">
                    {$tests->getGroupScore($groupNo)}
                  </td>
                {/if}
              </tr>
            {/if}
          {/foreach}
        {/foreach}

        {if $penalty->amount}
          <tr>
            <td colspan="{$numColumns-1}">
              Penalizare ({$penalty->description})
            </td>
            <td class="number">
              {$penalty->amount}
            </td>
          </tr>
        {/if}

        {if $job->isScoreViewable()}
          <tr>
            <td colspan="{$numColumns-1}">
              Punctaj total
            </td>
            <td class="total_score number">
              {$job->score}
            </td>
          </tr>
        {/if}
      </tbody>
    </table>

    {if !$job->isScoreViewable()}
      <div>
        Notă: Acesta este un raport parțial care include doar testele publice.
      </div>
    {/if}
  {/if}

  {Wiki::include('template/borderou')}

{/block}
