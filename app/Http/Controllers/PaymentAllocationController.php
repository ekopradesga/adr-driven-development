<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentAllocation\ReallocatePaymentAllocationRequest;
use App\Http\Requests\PaymentAllocation\ReversePaymentAllocationRequest;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentAllocationController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function index(Payment $payment): View
    {
        $this->authorize('viewAny', PaymentAllocation::class);

        return view('payment_allocations.index', $this->paymentService->buildAllocationIndexData($payment));
    }

    public function show(Payment $payment, PaymentAllocation $allocation): View
    {
        $this->authorize('view', $allocation);

        abort_unless($allocation->payment_id === $payment->id, 404);

        return view('payment_allocations.show', $this->paymentService->findAllocationForShow($allocation));
    }

    public function reverse(ReversePaymentAllocationRequest $request, Payment $payment, PaymentAllocation $allocation): RedirectResponse
    {
        abort_unless($allocation->payment_id === $payment->id, 404);

        $this->paymentService->reverseAllocation($allocation, $request->validated('reversal_reason'));

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment allocation reversed.');
    }

    public function reallocate(ReallocatePaymentAllocationRequest $request, Payment $payment, PaymentAllocation $allocation): RedirectResponse
    {
        abort_unless($allocation->payment_id === $payment->id, 404);

        $this->paymentService->reallocateAllocation(
            $allocation,
            (int) $request->validated('invoice_id'),
            (float) $request->validated('allocated_amount'),
            $request->validated('notes')
        );

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment allocation reallocated.');
    }
}
