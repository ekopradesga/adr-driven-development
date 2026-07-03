<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentAllocationStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAllocationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'super-admin', 'status' => 'active']);
        $user->roles()->attach($role);

        return $user;
    }

    private function activeSubscription(): Subscription
    {
        $customer = Customer::factory()->create(['status' => CustomerStatus::Active->value]);
        $package = Package::create([
            'name' => 'Basic Plan',
            'monthly_price' => 100,
            'downstream_kbps' => 1000,
            'upstream_kbps' => 500,
        ]);

        return Subscription::factory()->active()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'subscription_type' => SubscriptionType::Primary->value,
        ]);
    }

    private function publishedInvoiceFor(Subscription $subscription, float $total): Invoice
    {
        return Invoice::factory()->published()->create([
            'customer_id' => $subscription->customer_id,
            'subscription_id' => $subscription->id,
            'total_amount' => $total,
            'paid_amount' => 0,
            'balance_amount' => $total,
            'status' => InvoiceStatus::Published->value,
        ]);
    }

    private function allocatedPayment(Subscription $subscription, Invoice $invoice, float $amount = 150): Payment
    {
        $payment = Payment::factory()->recorded()->create([
            'customer_id' => $subscription->customer_id,
            'amount' => $amount,
            'method' => 'cash',
        ]);

        $this->actingAs($this->adminUser())
            ->post(route('payments.allocate', $payment), [
                'allocations' => [
                    ['invoice_id' => $invoice->id, 'allocated_amount' => $amount],
                ],
            ])
            ->assertRedirect();

        return $payment->fresh(['allocations.invoice']);
    }

    public function test_allocation_index_requires_authentication(): void
    {
        $subscription = $this->activeSubscription();
        $invoice = $this->publishedInvoiceFor($subscription, 150);
        $payment = $this->allocatedPayment($subscription, $invoice);

        $this->get(route('payments.allocations.index', $payment))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_allocation_index_and_show(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->publishedInvoiceFor($subscription, 150);
        $payment = $this->allocatedPayment($subscription, $invoice);
        $allocation = $payment->allocations()->firstOrFail();

        $this->actingAs($user)
            ->get(route('payments.allocations.index', $payment))
            ->assertOk()
            ->assertSee('Payment Allocations');

        $this->actingAs($user)
            ->get(route('payments.allocations.show', [$payment, $allocation]))
            ->assertOk()
            ->assertSee((string) $allocation->id);
    }

    public function test_admin_can_reverse_single_allocation(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->publishedInvoiceFor($subscription, 150);
        $payment = $this->allocatedPayment($subscription, $invoice);
        $allocation = $payment->allocations()->firstOrFail();

        $this->actingAs($user)
            ->post(route('payments.allocations.reverse', [$payment, $allocation]), [
                'reversal_reason' => 'Allocation entered on the wrong invoice.',
            ])
            ->assertRedirect(route('payments.show', $payment));

        $this->assertDatabaseHas('payment_allocations', [
            'id' => $allocation->id,
            'status' => PaymentAllocationStatus::Reversed->value,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Published->value,
            'paid_amount' => 0,
            'balance_amount' => 150,
        ]);
    }

    public function test_admin_can_reallocate_single_allocation_to_another_invoice(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $sourceInvoice = $this->publishedInvoiceFor($subscription, 150);
        $targetInvoice = $this->publishedInvoiceFor($subscription, 150);
        $payment = $this->allocatedPayment($subscription, $sourceInvoice);
        $allocation = $payment->allocations()->firstOrFail();

        $this->actingAs($user)
            ->post(route('payments.allocations.reallocate', [$payment, $allocation]), [
                'invoice_id' => $targetInvoice->id,
                'allocated_amount' => 150,
                'notes' => 'Moved to the correct billing cycle.',
            ])
            ->assertRedirect(route('payments.show', $payment));

        $this->assertDatabaseHas('payment_allocations', [
            'id' => $allocation->id,
            'status' => PaymentAllocationStatus::Reversed->value,
        ]);

        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'invoice_id' => $targetInvoice->id,
            'allocated_amount' => 150,
            'status' => PaymentAllocationStatus::Allocated->value,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $sourceInvoice->id,
            'paid_amount' => 0,
            'balance_amount' => 150,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $targetInvoice->id,
            'paid_amount' => 150,
            'balance_amount' => 0,
            'status' => InvoiceStatus::Paid->value,
        ]);
    }
}
