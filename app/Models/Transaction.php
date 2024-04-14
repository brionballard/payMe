<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance_after_activity',
        'amount',
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
            'balance_after_activity' => $this->balance_after_activity,
            'amount' => $this->amount,
            'timestamp' => $this->timestamp,
            'api_key' => $this->api_key,
            'action' => $this->action
        ];

        return '[' . \Carbon\Carbon::now() . ']' . json_encode($data);
    }
}
