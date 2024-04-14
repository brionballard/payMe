<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $log_statement;
    public $path;

    /**
     * Create a new event instance.
     */
    public function __construct(string $statement)
    {
        $this->log_statement = $statement;
        $this->path = storage_path(env('TRANSACTION_LOG_PATH'));
    }
}
