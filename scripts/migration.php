#!/usr/bin/php
<?php
/**
 * This script applies data migration patches, consisting of SQL code and PHP
 * scripts.
 *
 * Overview:
 * - Looks in the ia_variable table for Schema.version, which is a 5-digit
 *   number;
 * - Reads files of the form "db-changes/\d\d\d\d\d.extension" in increasing
 *   numerical order;
 * - Ignores files older than, or equal to, Schema.version;
 * - Files with the .sql extension are piped into SQL;
 * - Files with the .php extension are executed within this script;
 * - Files with other extensions are ignored.
 *
 * Use with -n or --dry-run to see what the script would do without actually
 * executing anything.
 **/

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../www/config.php';
require_once __DIR__ . '/../www/identity.php';
require_once __DIR__ . '/../common/log.php';

require_once __DIR__ . '/../lib/Core.php';

const PATCH_DIR = __DIR__ . '/../schema-changes/';
const PATCH_REGEXP = '/^(?<version>\d{5})\.(?<extension>(php|sql))$/';

$opts = getopt('n', ['dry-run']);
$dryRun = isset($opts['n']) || isset($opts['dry-run']);

if ($dryRun) {
  print "---- DRY RUN ----\n";
}

$schemaVersion = DB::tableExists('ia_variable')
  ? Variable::peek('Schema.version', '00000')
  : '00000';
print "Current schema version is <$schemaVersion>\n";

$patchFiles = getPatches(PATCH_DIR, $schemaVersion);
foreach ($patchFiles as $version => $file) {
  runPatch(PATCH_DIR . $file, $dryRun);
  $schemaVersion = $version;
  if (!$dryRun) {
    // Update after each patch, in case one of the patches terminates with error.
    Variable::poke('Schema.version', $schemaVersion);
  }
}

print "New schema version is <$schemaVersion>\n";

/*****************************************************************/

function getPatches($dir, $after) {
  $patches = [];

  foreach (scandir($dir) as $file) {
    if (preg_match(PATCH_REGEXP, $file, $matches) && $matches['version'] > $after) {
      $patches[$matches['version']] = $file;
    }
  }

  return $patches;
}

function runPatch($fileName, $dryRun) {
  $fileName = realpath($fileName);
  $extension = strrchr($fileName, '.');
  if ($extension == '.sql') {
    print "$fileName -- executing with MySQL via OS\n";
    if (!$dryRun) {
      db_execute_sql_file($fileName);
    }
  } else { // .php
    print "$fileName -- executing with PHP\n";
    if (!$dryRun) {
      require $fileName;
    }
  }
}
