<?php

class OS {

    static function errorAndExit(string $msg, int $exitCode = 1): void {
        log_print("ERROR: $msg");
        exit($exitCode);
    }

    static function execute(string $command): array {
        log_print('Executing %s', $command);
        exec($command, $arr, $exitCode);
        $output = implode("\n", $arr);

        return [
            'output' => $output,
            'exitCode' => $exitCode,
        ];
    }

    static function executeAndAssert(string $command): void {
        $rec = self::execute($command);
        if ($rec['exitCode']) {
            log_print('Output: ' . $rec['output']);
            self::errorAndExit("Failed command: $command (code $exitCode)");
        }
    }
}
