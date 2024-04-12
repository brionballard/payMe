<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance_at_time_of_activity',
        'amount',
        'card_id',
        'timestamp',
        'api_key',
        'action'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format a string based on model information for log file
     * 
     * @return string
     */
    public function formatLogFileStatement () 
    {
        $data = (object) [
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'card_id' => $this->card_id,
            'timestamp' => $this->timestamp,
            'api_key' => $this->api_key,
            'action' => $this->action
        ];

        return '[' . \Carbon\Carbon::now() . ']' . json_encode($data);
    }
}
