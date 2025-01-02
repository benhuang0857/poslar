<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DutyHandover extends Model
{
    use HasFactory;

    protected $table = 'duty_handovers';

    protected $fillable = [
        'user_id',
        'note',
        'status',
        'last_triggered_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
