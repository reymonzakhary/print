<?php

namespace App\Foundation\Commands;

use App\Exceptions\ShellCommandFailedException;
use Symfony\Component\Process\Process;

class ShellCommand
{
    /**
     * Used to run terminal commands from the script with response.
     * @return string
     * @throws ShellCommandFailedException
     * @var $cmd
     */
    public static function execute(
        $cmd
    ): string
    {
        exec("command=/bin/sh -c supervisorctl restart queue-worker:*");
//        $process = Process::fromShellCommandline($cmd);
//
//        $processOutput = '';
//
//        $captureOutput = function ($type, $line) use (&$processOutput) {
//            $processOutput .= $line;
//        };
//
//        $process->setTimeout(null)
//            ->run($captureOutput);
//
//        if ($process->getExitCode()) {
//            throw new ShellCommandFailedException($cmd . " - " . $processOutput);
////            report($exception);
//
////            throw $exception;
//        }

//        return $processOutput;
    }
}
