<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $locale = $request->header('Accept-Language', 'en');

        return [
            'id' => $this->id,
            'name' => $locale === 'ar' ? $this->name_ar : $this->name_en,
            'description' => $locale === 'ar' ? $this->description_ar : $this->description_en,
            'images' => $this->images,
            'price' => $this->price,
            'discounted_price' => $this->discounted_price,
            'quantity' => $this->quantity,
            'status' => $this->status ? 'active' : 'not active',
            'categories' => CategoryResource::collection($this->categories),
        ];
    }
}
