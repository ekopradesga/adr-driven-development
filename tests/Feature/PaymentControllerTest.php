<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
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

class PaymentControllerTest extends TestCase
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

    private function publishedInvoiceFor(Subscription $subscription, float $total = 150): Invoice
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

    public function test_index_requires_authentication(): void
    {
        $this->get(route('payments.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_payment_intent(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();

        $this->actingAs($user)
            ->post(route('payments.store'), [
                'customer_id' => $subscription->customer_id,
                'payment_date' => now()->toDateString(),
                'amount' => 150,
                'currency' => 'IDR',
                'method' => 'cash',
                'channel_reference' => 'REF-001',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'customer_id' => $subscription->customer_id,
            'status' => PaymentStatus::IntentCreated->value,
            'amount' => 150,
        ]);
    }

    public function test_payment_can_transition_to_received_then_validated_then_recorded(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $payment = Payment::factory()->create([
            'customer_id' => $subscription->customer_id,
            'status' => PaymentStatus::IntentCreated->value,
            'method' => 'cash',
            'amount' => 200,
        ]);

        $this->actingAs($user)->post(route('payments.receive', $payment))->assertRedirect();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => PaymentStatus::Received->value]);

        $this->actingAs($user)->post(route('payments.validate', $payment))->assertRedirect();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => PaymentStatus::Validated->value]);

        $this->actingAs($user)->post(route('payments.record', $payment))->assertRedirect();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => PaymentStatus::Recorded->value]);
    }

    public function test_recorded_payment_can_be_allocated_to_invoice(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->publishedInvoiceFor($subscription, 180);

        $payment = Payment::factory()->recorded()->create([
            'customer_id' => $subscription->customer_id,
            'amount' => 180,
            'method' => 'cash',
        ]);

        $this->actingAs($user)
            ->post(route('payments.allocate', $payment), [
                'allocations' => [
                    ['invoice_id' => $invoice->id, 'allocated_amount' => 180],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::FullyAllocated->value,
        ]);

        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'allocated_amount' => 180,
            'status' => 'allocated',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Paid->value,
            'paid_amount' => 180,
            'balance_amount' => 0,
        ]);
    }

    public function test_fully_allocated_payment_can_be_completed(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();

        $payment = Payment::factory()->create([
            'customer_id' => $subscription->customer_id,
            'status' => PaymentStatus::FullyAllocated->value,
            'method' => 'cash',
            'amount' => 250,
            'recorded_at' => now()->subMinutes(2),
        ]);

        $this->actingAs($user)
            ->post(route('payments.complete', $payment))
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Completed->value,
        ]);
    }

    public function test_completed_payment_can_be_reversed_and_invoice_reopened(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->publishedInvoiceFor($subscription, 220);

        $payment = Payment::factory()->completed()->create([
            'customer_id' => $subscription->customer_id,
            'amount' => 220,
            'method' => 'cash',
        ]);

        $this->actingAs($user)->post(route('payments.allocate', $payment), [
            'allocations' => [
                ['invoice_id' => $invoice->id, 'allocated_amount' => 220],
            ],
        ])->assertRedirect();

        $this->actingAs($user)->post(route('payments.reverse', $payment), [
            'reversal_reason' => 'Duplicate cashier posting entry.',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Reversed->value,
        ]);

        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'status' => 'reversed',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Published->value,
            'paid_amount' => 0,
            'balance_amount' => 220,
        ]);
    }
}
