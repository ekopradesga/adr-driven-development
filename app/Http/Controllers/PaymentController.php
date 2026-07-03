<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\AllocatePaymentRequest;
use App\Http\Requests\Payment\CompletePaymentRequest;
use App\Http\Requests\Payment\FailPaymentRequest;
use App\Http\Requests\Payment\ReceivePaymentRequest;
use App\Http\Requests\Payment\RecordPaymentRequest;
use App\Http\Requests\Payment\ReversePaymentRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Http\Requests\Payment\ValidatePaymentRequest;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        return view('payments.index', $this->paymentService->buildIndexData(
            $request->only('search', 'status', 'customer_id')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Payment::class);

        return view('payments.create', $this->paymentService->buildCreateData());
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $payment = $this->paymentService->create($request->validated());

        return redirect()->route('payments.show', $payment)
            ->with('success', "Payment {$payment->payment_number} intent created.");
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        return view('payments.show', $this->paymentService->findForShow($payment));
    }

    public function edit(Payment $payment): View
    {
        $this->authorize('update', $payment);

        return view('payments.edit', $this->paymentService->buildEditData($payment));
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->update($payment, $request->validated());

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment updated.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $this->paymentService->delete($payment);

        return redirect()->route('payments.index');
    }

    public function receive(ReceivePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->receive($payment, $request->validated());

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment marked as received.');
    }

    public function validatePayment(ValidatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->validatePayment($payment);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment validated.');
    }

    public function record(RecordPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->record($payment);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment recorded.');
    }

    public function allocate(AllocatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->allocate(
            $payment,
            $request->validated('allocations'),
            $request->validated('notes')
        );

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment allocation applied.');
    }

    public function complete(CompletePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->complete($payment);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment completed.');
    }

    public function reverse(ReversePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->reverse($payment, $request->validated('reversal_reason'));

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment reversed.');
    }

    public function fail(FailPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->fail($payment, $request->validated('failure_reason'));

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment marked as failed.');
    }
}
