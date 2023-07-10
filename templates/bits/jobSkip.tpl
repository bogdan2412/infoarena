{if $job->status == 'skipped'}
  ignorat
{else}
  <input
    class="skip_job"
    type="checkbox"
    value="{$job->id}">
  <a class="skip-job-link" href="#">ignoră</a>
{/if}
