<?php

require_once Config::ROOT . 'www/format/table.php';
require_once Config::ROOT . 'www/format/pager.php';
require_once Config::ROOT . 'www/format/format.php';
require_once Config::ROOT . 'common/db/score.php';

class RankingsRow {
  public int $rank;
  public User $user;
  public array $scores; // floats
  public float $total;

  function __construct(int $rank, User $user, array $scores, float $total) {
    $this->rank = $rank;
    $this->user = $user;
    $this->scores = $scores;
    $this->total = $total;
  }
}

// Takes a string of the form
//
//   S -> roundDef [ | roundDef ]...
//   roundDef -> round ID [ : round name]
//
// Returns an array of [ roundId, roundName ] pairs.
function parseRoundStr(string $roundStr): array {
  $results = [];

  $idNamePairs = explode('|', $roundStr);
  foreach ($idNamePairs as $idNamePair) {
    $parts = explode(':', $idNamePair, 2);
    $roundId = trim($parts[0]);
    $roundName = trim($parts[1] ?? '');
    $round = round_get($roundId);

    if (Identity::mayViewRoundScores($round)) {
      $results[] = [
        'roundId' => $roundId,
        'roundName' => $roundName,
      ];
    }
  }

  return $results;
}

// Returns an array of [ 'roundId', ['taskId',] 'displayValue' ].
function makeColumns(array $roundMap, bool $detailRound, bool $detailTask): array {
  $result = [];

  foreach ($roundMap as $r) {
    if ($detailTask) {
      $tasks = Task::loadByRoundId($r['roundId']);
      foreach ($tasks as $t) {
        $result[] = [
          'roundId' => $r['roundId'],
          'taskId' => $t->id,
          'displayValue' => $t->title,
        ];
      }
    }

    if ($detailRound) {
      $result[] = [
        'roundId' => $r['roundId'],
        'displayValue' => $r['roundName'],
      ];
    }
  }

  return $result;
}

// Collects task/round scores for a given $user. Returns an array of floats.
function collectScores(User $user, array $columns, array $taskScores,
                       array $roundScores): array {
  $scores = [];
  foreach ($columns as $col) {
    $record = isset($col['taskId'])
      ? ($taskScores[$col['roundId']][$col['taskId']] ?? null)
      : ($roundScores[$col['roundId']] ?? null);
    $scores[] = $record[$user->id] ?? null;
  }
  return $scores;
}

// Returns an array of RankingsRow.
function makeTableData(array $roundIds, array $columns): array {
  // Load all kinds of data.
  $totals = ScoreUserRound::loadTotalsByRoundIds($roundIds);
  $userIds = array_column($totals, 'userId');
  $userMap = User::loadAndMapById($userIds);
  $roundScores = ScoreUserRound::loadByRoundIds($roundIds);
  $taskScores = ScoreUserRoundTask::loadByRoundIds($roundIds);

  $tableData = [];
  $prevTotal = -1;
  $prevRank = -1;
  foreach ($totals as $i => $rec) {
    $total = $rec['total'];
    $user = $userMap[$rec['userId']];
    $rank = ($total == $prevTotal) ? $prevRank : ($i + 1);
    $scores = collectScores($user, $columns, $taskScores, $roundScores);

    $tableData[] = new RankingsRow($rank, $user, $scores, $total);

    $prevTotal = $total;
    $prevRank = $rank;
  }
  return $tableData;
}

// Displays rankings for one or more rounds.
//
// Arguments:
//     rounds   (required) a | (pipe) separated list of round_id : round_name.
//              Round name is the name which will appear in the column dedicated
//              to that round in case detail_round == true
//              If detail_round == false you can leave just the round_id (see examples)
//     detail_task   (optional) true/false print score columns for each task
//     detail_round  (optional) true/false print score columns for each round
function macro_rankings($args) {
  $roundStr = $args['rounds'] ?? null;
  $detailRound = ($args['detail_round'] ?? 'false') == 'true';
  $detailTask = ($args['detail_task'] ?? 'false') == 'true';

  if (!$roundStr) {
    return macro_error("Parameter 'rounds' is required.");
  }

  $roundMap = parseRoundStr($roundStr);
  if (!count($roundMap)) {
    return macro_message('Nici un rezultat înregistrat pentru această rundă.');
  }

  $columns = makeColumns($roundMap, $detailRound, $detailTask);
  $roundIds = array_column($roundMap, 'roundId');
  $roundId = (count($roundIds) == 1) ? $roundIds[0] : null;
  $tableData = makeTableData($roundIds, $columns);

  Smart::assign([
    'columns' => $columns,
    'roundId' => $roundId,
    'tableData' => $tableData,
  ]);
  return smart::fetch('macro/rankings.tpl');
}
