<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DutyShift extends Model
{
    protected $table = 'duty_shifts';

    protected $fillable = [
        'title',
        'start_time',
        'end_time',
        'status',
    ];

    const DELETED_AT = 'deleted_at';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
