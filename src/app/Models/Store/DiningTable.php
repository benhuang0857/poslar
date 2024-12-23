<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dining_table';

    protected $fillable = [
        'name',
        'quantity',
        'qrcode',
        'description',
        'status',
    ];

    const DELETED_AT = 'deleted_at';
}
