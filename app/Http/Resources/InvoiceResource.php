<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'invoice_number'     => $this->invoice_number,
            'customer_id'        => $this->customer_id,
            'subscription_id'    => $this->subscription_id,
            'status'             => $this->status->value,
            'status_label'       => $this->status->label(),
            'period_start'       => $this->period_start?->toDateString(),
            'period_end'         => $this->period_end?->toDateString(),
            'issue_date'         => $this->issue_date?->toDateString(),
            'due_date'           => $this->due_date?->toDateString(),
            'subtotal_amount'    => $this->subtotal_amount,
            'tax_amount'         => $this->tax_amount,
            'discount_amount'    => $this->discount_amount,
            'total_amount'       => $this->total_amount,
            'paid_amount'        => $this->paid_amount,
            'balance_amount'     => $this->balance_amount,
            'published_at'       => $this->published_at?->toISOString(),
            'overdue_at'         => $this->overdue_at?->toISOString(),
            'cancelled_at'       => $this->cancelled_at?->toISOString(),
            'cancellation_reason'=> $this->cancellation_reason,
            'created_at'         => $this->created_at?->toISOString(),
        ];
    }
}
