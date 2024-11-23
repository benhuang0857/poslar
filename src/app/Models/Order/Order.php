<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Store\DiningTable;
use App\Models\Store\Payment;
use App\Models\Store\Promotion;
use App\Models\User;
use App\Models\Customer;

class Order extends Model
{
    use HasFactory;

    public function items()
    {
        return $this->hasMany(OrderItem::class);
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
