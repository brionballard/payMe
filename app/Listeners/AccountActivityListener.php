<?php

namespace App\Listeners;

use App\Events\AccountActivity;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AccountActivityListener implements ShouldQueue, ShouldHandleEventsAfterCommit
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     */
    public function handle(AccountActivity $event): void
    {        
        file_exists($event->path) ?
            // Set FILE_APPEND so the file is not overwritten
            file_put_contents($event->path, $event->log_statement . PHP_EOL, FILE_APPEND) :
            file_put_contents($event->path, $event->log_statement . PHP_EOL);
    }
}
