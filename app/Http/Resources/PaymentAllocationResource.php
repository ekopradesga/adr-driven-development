<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentAllocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'invoice_id' => $this->invoice_id,
            'allocated_amount' => $this->allocated_amount,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'payment_number' => $this->payment?->payment_number,
            'invoice_number' => $this->invoice?->invoice_number,
            'allocated_at' => $this->allocated_at?->toISOString(),
            'reversed_at' => $this->reversed_at?->toISOString(),
            'reversal_reason' => $this->reversal_reason,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
