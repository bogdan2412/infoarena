{extends "layout.tpl"}

{block "title"}Borderou de evaluare (job #{$job->id}){/block}

{block "content"}
  <h1>Borderou de evaluare (job #{$job->id})</h1>

  {include "bits/jobHeader.tpl"}

  <h2>Raport evaluator</h2>

  {if $job->isDone()}
    <div class="job-eval-log">
      {$job->eval_log|escape}
    </div>
  {else}
    <p>Această sursă nu a fost evaluată încă.</p>
  {/if}

  {if $showScoreTable}
    {include "bits/jobStaleTests.tpl" errors=$tests->getErrors()}

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
              Penalizare: {$penalty->description}
            </td>
            <td class="number">
              {$penalty->amount}%
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
