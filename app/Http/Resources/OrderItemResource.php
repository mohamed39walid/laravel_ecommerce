<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'product' => new ProductResource($this->product),
            'quantity' => $this->quantity,
            'price' => $this->price,
        ];
    }
}
