{if $report->getLiveCount()}
  <form class="vspace-1" method="post">
    <button
      class="button"
      type="submit"
      name="report_action"
      value="cleanup">
      șterge {$report->getLiveCount()} fișiere
    </button>
  </form>

  <table>
    <thead>
      <tr>
        <th>fișier</th>
      </tr>
    </thead>

    <tbody>
      {foreach $report->getFiles() as $file}
        <tr>
          <td>
            {$file}
          </td>
        </tr>
      {/foreach}
    </tbody>

  </table>
{else}
  Nu există înregistrări.
{/if}
