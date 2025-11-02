<?php

namespace App\Jobs;

use App\Exceptions\ShellCommandFailedException;
use App\Foundation\Commands\ShellCommand;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RestartSupervisorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Execute the job.
     *
     * @return void
     * @throws ShellCommandFailedException
     */
    public function handle()
    {
        ShellCommand::execute("supervisorctl restart queue-worker:*");
    }
}
