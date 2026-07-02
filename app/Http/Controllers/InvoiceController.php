<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\CancelInvoiceRequest;
use App\Http\Requests\Invoice\PublishInvoiceRequest;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Services\Billing\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * InvoiceController — Billing module.
 *
 * Orchestration only. Zero business logic.
 * Delegates all business operations to InvoiceService.
 */
class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        return view('invoices.index', $this->invoiceService->buildIndexData(
            $request->only('search', 'status', 'customer_id')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Invoice::class);

        return view('invoices.create', $this->invoiceService->buildCreateData());
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $invoice = $this->invoiceService->create($request->validated());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} generated.");
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        return view('invoices.show', $this->invoiceService->findForShow($invoice));
    }

    public function edit(Invoice $invoice): View
    {
        $this->authorize('update', $invoice);

        return view('invoices.edit', $this->invoiceService->buildEditData($invoice));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->update($invoice, $request->validated());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        $this->invoiceService->delete($invoice);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice cancelled.');
    }

    public function publish(PublishInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->publish($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice published.');
    }

    public function cancel(CancelInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->cancel($invoice, $request->validated('cancellation_reason'));

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice cancelled.');
    }
}
