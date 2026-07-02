<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'customer_id'       => $this->customer_id,
            'package_id'        => $this->package_id,
            'status'            => $this->status->value,
            'status_label'      => $this->status->label(),
            'subscription_type' => $this->subscription_type->value,
            'suspension_type'   => $this->suspension_type,
            'billing_day'       => $this->billing_day,
            'activated_at'      => $this->activated_at?->toISOString(),
            'suspended_at'      => $this->suspended_at?->toISOString(),
            'terminated_at'     => $this->terminated_at?->toISOString(),
            'created_at'        => $this->created_at?->toISOString(),
        ];
    }
}
