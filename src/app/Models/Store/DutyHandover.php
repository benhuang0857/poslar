<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DutyHandover extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'duty_handovers';

    protected $fillable = [
        'user_id',
        'note',
        'status',
        'last_triggered_at',
    ];

    const DELETED_AT = 'deleted_at';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
