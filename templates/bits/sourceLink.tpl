{* Whether the link text is the source size or a generic message. *}
{$showSize=$showSize|default:true}

{if !$job->isSourceViewable()}
  ...
{else}
  <a href="job_detail/{$job->id}?action=view-source">
    {if $showSize}
      {$job->getSizeString()}
    {else}
      vezi sursa
    {/if}
  </a>
{/if}
