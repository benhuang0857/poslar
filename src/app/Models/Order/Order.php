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
        // Ensure `items` relation is loaded to avoid N+1 queries
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
    
        // Calculate total price by summing all items' prices
        $this->total_price = $this->items->sum(function ($item) {
            return $item->price;
        });
    
        // Save the updated total price
        $this->save();
    }

    public function calculateFinalPrice()
    {
        $promotion = $this->promotion;
    
        // Check if a promotion exists
        if (!$promotion) {
            $this->final_price = $this->total_price;
            $this->save();
            return;
        }

        // Check if promotion is active and not expired
        if ($promotion->status && $promotion->end_time >= now()) {
            // Handle numeric discount type
            if ($promotion->type === 'numeric') {
                $discount = $promotion->discount;
                $this->final_price = $this->total_price - $discount;
            } 
            // Handle percentage discount type
            elseif ($promotion->type === 'percentage') {
                $discount = $promotion->discount / 100; // Convert percentage to decimal
                $this->final_price = $this->total_price * (1 - $discount);
            }
        } else {
            // If promotion is inactive or expired, final price equals total price
            $this->final_price = $this->total_price;
        }
    
        // Save the updated final price
        $this->save();
    }    

    public function generateSerialNumber()
    {
        $date = now()->format('YmdHis');
        $count = self::whereDate('created_at', now()->toDateString())->count() + 1;

        return $date . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
