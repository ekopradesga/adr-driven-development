<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CustomerResource — JSON API representation of a Customer.
 *
 * Used for API responses and any JSON rendering context.
 * Exposes only safe, non-sensitive customer data.
 */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'customer_number' => $this->customer_number,
            'name'            => $this->name,
            'customer_type'   => $this->customer_type->value,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'whatsapp_phone'  => $this->whatsapp_phone,
            'address'         => $this->address,
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'status'          => $this->status->value,
            'status_label'    => $this->status->label(),
            'cluster_id'      => $this->cluster_id,
            'service_area_id' => $this->service_area_id,
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}
