<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'delivery_address' => [
                'city' => $this->city,
                'address' => $this->address,
                'building_number' => $this->building_number
            ],
            'items' => OrderItemResource::collection($this->items),
            'created_at' => $this->created_at
        ];
    }
}