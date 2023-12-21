{$penaltyLinks=Identity::isAdmin() && $roundId}
<table class="alternating-colors table-sort">
  <thead>
    <tr>
      <th class="center">Loc</th>
      <th>Utilizator</th>
      {foreach $columns as $col}
        <th class="center">{$col.displayValue}</th>
      {/foreach}
      <th class="center">Total</th>
      {if $penaltyLinks}
        <th class="center">Acțiuni</th>
      {/if}
    </tr>
  </thead>

  <tbody>
    {foreach $tableData as $row}
      <tr>
        <td class="center">
          {$row->rank}
        </td>
        <td class="nowrap">
          {include "bits/userNormal.tpl" user=$row->user}
        </td>
        {foreach $row->scores as $score}
          <td class="center">
            {$score|default:'&ndash;'}
          </td>
        {/foreach}
        <td class="center">
          {$row->total}
        </td>
        {if $penaltyLinks}
          <td class="center">
            <a href="{Config::URL_PREFIX}penalty_edit?userId={$row->user->id}&roundId={$roundId}">
              penalizează
            </a>
          </td>
        {/if}
      </tr>
    {/foreach}
  </tbody>
</table>
