{if $report->getLiveCount()}
  <table>
    <thead>
      <tr>
        <th>id</th>
        <th>nume</th>
        <th>pagină</th>
        <th>mărime</th>
        <th>utilizator</th>
        <th>MIME type</th>
        <th>dată</th>
        <th>acțiuni</th>
      </tr>
    </thead>

    <tbody>
      {foreach $report->getAttachments() as $att}
        <tr>
          <td>
            {$att->id}
          </td>

          <td>
            {$att->name}
          </td>

          <td>
            {include "bits/pageLink.tpl" page=$att->page}
          </td>

          <td>
            {$att->size}
          </td>

          <td>
            {$user=$att->getUser()}
            {if $user}
              {include "bits/userTiny.tpl"}
              (#{$att->user_id})
            {else}
              #{$att->user_id}
            {/if}
          </td>

          <td>
            {$att->mime_type}
          </td>

          <td>
            {$att->timestamp|fullDateTime}
          </td>

          <td>
            <a href="?report_action=attachment_delete&amp;attachment_id={$att->id}">
              șterge
            </a>
          </td>
        </tr>
      {/foreach}
    </tbody>

  </table>
{else}
  Nu există înregistrări.
{/if}
