{if $job}
  <a href="{Config::URL_PREFIX}job_detail/{$job->id}">#{$job->id}</a>
{else}
  [ID invalid]
{/if}
