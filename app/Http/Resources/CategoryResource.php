<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        $lang = request()->header('Accept-Language', 'en');
        return [
            'id' => $this->id,
            'name' => $lang === 'ar' ? $this->name_ar : $this->name_en,
            'description' => $lang === 'ar' ? $this->description_ar : $this->description_en,
            'image' => $this->image,
            'status' => $this->status ? 'active' : 'not active',
        ];
    }
}
