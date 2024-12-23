<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'promotion';

    protected $fillable = [
        'name',
        'description',
        'discount',
        'start_time',
        'end_time',
        'status',
    ];

    const DELETED_AT = 'deleted_at';
}
