<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'user_id',
        'dining_table_id',
        'total_price',
        'session_id',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function calculateTotalPrice()
    {
        $this->total_price = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        $this->save();
    }

    public function generateSerialNumber()
    {
        $date = now()->format('YmdHis');
        $count = self::whereDate('created_at', now()->toDateString())->count() + 1;

        return $date . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
