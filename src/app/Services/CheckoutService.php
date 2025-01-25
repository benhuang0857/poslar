<?php

namespace App\Services;

use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionType;
use App\Models\Product\ProductOptionValue;
use App\Exceptions\OutOfStockException;
use Illuminate\Database\Eloquent\Builder;

class CheckoutService
{
    public function checkStock($item, $quantity, $type = 'product')
    {
        if ($item->enable_stock && $item->stock < $quantity) {
            $message = $type === 'product'
                ? '商品: "' . $item->name . '" 售罄'
                : '商品品項: "' . $item->value . '" 售罄';

            $data = $type === 'product'
                ? ['product_id' => $item->id]
                : ['option_value_id' => $item->id];

            throw new OutOfStockException($message, $data);
        }
    }

    public function applyFilters(Builder $query, array $filters): Builder
    {
        // Filter by date range
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('created_at', [
                $filters['start_date'],
                $filters['end_date'],
            ]);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by paid status
        if (isset($filters['paid'])) {
            $query->where('paid', filter_var($filters['paid'], FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by shipping type
        if (!empty($filters['shipping'])) {
            $query->where('shipping', $filters['shipping']);
        }

        // Default filter (if no other filters are provided)
        if (empty($filters)) {
            $query->whereDate('created_at', now()->toDateString())->limit(100);
        }

        return $query;
    }
}
