<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_name' => $this->shop_name,
            'shop_slug' => $this->shop_slug,
            'shop_description' => $this->shop_description,
            'shop_logo' => $this->shop_logo,
            'shop_banner' => $this->shop_banner,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            'rating_average' => $this->rating_average,
            'total_reviews' => $this->total_reviews,
            'total_products' => $this->total_products,
            'total_sales' => $this->total_sales,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
