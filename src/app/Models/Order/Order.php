<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Store\DiningTable;
use App\Models\Store\Payment;
use App\Models\Store\Promotion;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'serial_number',
        'user_id',
        'customer_id',
        'dining_table_id',
        'promotion_id',
        'paid',
        'shipping',
        'total_price',
        'final_price',
        'note',
        'status'
    ];

    const DELETED_AT = 'deleted_at';

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

    public function calculateTotalPrice()
    {
        $this->total_price = $this->items->sum(function ($item) {
            return $item->price;
        });
        $this->save();
    }

    public function calculateFinalPrice()
    {
        $promotion = $this->promotion;

        $discount = $promotion ? $promotion->discount : 1;
        $this->final_price = $this->total_price * $discount;

        $this->save();
    }

    public function generateSerialNumber()
    {
        $date = now()->format('YmdHis');
        $count = self::whereDate('created_at', now()->toDateString())->count() + 1;

        return $date . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
