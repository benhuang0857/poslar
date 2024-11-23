<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Store\DiningTable;
use App\Models\Store\Payment;
use App\Models\Store\Promotion;
use App\Models\User;
use App\Models\Customer;

class Cart extends Model
{
    use HasFactory;

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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function dining_table()
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
