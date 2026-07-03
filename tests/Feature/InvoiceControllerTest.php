<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
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

    private function invoicePayload(Subscription $subscription, array $overrides = []): array
    {
        $issueDate = now()->toDateString();
        return array_merge([
            'customer_id' => $subscription->customer_id,
            'subscription_id' => $subscription->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'issue_date' => $issueDate,
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal_amount' => 100,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'notes' => 'Test invoice',
            'items' => [
                [
                    'description' => 'Monthly subscription',
                    'item_type' => 'subscription',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'total_amount' => 100,
                    'sort_order' => 0,
                ],
            ],
        ], $overrides);
    }

    private function createInvoiceFor(Subscription $subscription, array $overrides = []): Invoice
    {
        return Invoice::factory()->create(array_merge([
            'customer_id' => $subscription->customer_id,
            'subscription_id' => $subscription->id,
        ], $overrides));
    }

    public function test_index_requires_authentication(): void
    {
        $this->get(route('invoices.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_invoice_index(): void
    {
        $user = $this->adminUser();
        $this->actingAs($user)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('Invoices');
    }

    public function test_admin_can_create_invoice(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();

        $this->actingAs($user)
            ->post(route('invoices.store'), $this->invoicePayload($subscription))
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $subscription->customer_id,
            'subscription_id' => $subscription->id,
            'status' => InvoiceStatus::Draft->value,
        ]);
    }

    public function test_invoice_creation_requires_active_subscription(): void
    {
        $user = $this->adminUser();
        $customer = Customer::factory()->create(['status' => CustomerStatus::Active->value]);
        $package = Package::create([
            'name' => 'Basic Plan',
            'monthly_price' => 100,
            'downstream_kbps' => 1000,
            'upstream_kbps' => 500,
        ]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'status' => SubscriptionStatus::Pending->value,
            'subscription_type' => SubscriptionType::Primary->value,
        ]);

        $this->actingAs($user)
            ->post(route('invoices.store'), $this->invoicePayload($subscription))
            ->assertSessionHasErrors('subscription_id');
    }

    public function test_admin_can_view_invoice_show_page(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->createInvoiceFor($subscription, [
            'status' => InvoiceStatus::Published->value,
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    public function test_admin_can_update_draft_invoice(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->createInvoiceFor($subscription);

        $this->actingAs($user)
            ->put(route('invoices.update', $invoice), [
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'tax_amount' => 5,
                'discount_amount' => 0,
                'notes' => 'Updated notes',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'notes' => 'Updated notes',
        ]);
    }

    public function test_admin_can_publish_draft_invoice(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->createInvoiceFor($subscription, [
            'total_amount' => 100,
            'balance_amount' => 100,
        ]);
        $invoice->items()->create([
            'description' => 'Monthly subscription',
            'item_type' => 'subscription',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('invoices.publish', $invoice))
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Published->value,
        ]);
    }

    public function test_admin_can_cancel_draft_invoice(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->createInvoiceFor($subscription);

        $this->actingAs($user)
            ->post(route('invoices.cancel', $invoice), [
                'cancellation_reason' => 'Duplicate invoice generated by mistake.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Cancelled->value,
        ]);
    }

    public function test_invoice_cannot_be_published_without_items(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->createInvoiceFor($subscription, ['total_amount' => 100, 'balance_amount' => 100]);

        $this->actingAs($user)
            ->post(route('invoices.publish', $invoice))
            ->assertSessionHasErrors('items');
    }

    public function test_invoice_cannot_be_updated_after_publication(): void
    {
        $user = $this->adminUser();
        $subscription = $this->activeSubscription();
        $invoice = $this->createInvoiceFor($subscription, [
            'status' => InvoiceStatus::Published->value,
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->put(route('invoices.update', $invoice), [
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'tax_amount' => 5,
                'discount_amount' => 0,
                'notes' => 'Should fail',
            ])
            ->assertSessionHasErrors();
    }
}
