<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'store';

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone_number',
        'email',
        'website',
        'logo_url',
        'opening_hours',
        'social_links',
        'status',
    ];

    const DELETED_AT = 'deleted_at';
}
