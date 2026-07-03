<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'customer_id' => $this->customer_id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'payment_date' => $this->payment_date?->toDateString(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'method' => $this->method,
            'channel_reference' => $this->channel_reference,
            'received_by' => $this->received_by,
            'recorded_at' => $this->recorded_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'reversed_at' => $this->reversed_at?->toISOString(),
            'reversal_reason' => $this->reversal_reason,
            'failure_reason' => $this->failure_reason,
            'notes' => $this->notes,
            'allocated_amount' => $this->allocatedAmount(),
            'unallocated_amount' => $this->unallocatedAmount(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
