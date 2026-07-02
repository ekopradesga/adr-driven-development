<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Models\Customer;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SubscriptionControllerTest — Customer Management module.
 */
class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'admin', 'status' => 'active']);
        $user->roles()->attach($role);
        return $user;
    }

    private function customer(): Customer
    {
        return Customer::factory()->create(['status' => CustomerStatus::Active->value]);
    }

    private function validPayload(Customer $customer, array $overrides = []): array
    {
        return array_merge([
            'customer_id'       => $customer->id,
            'package_id'        => 1,
            'subscription_type' => SubscriptionType::Primary->value,
            'billing_day'       => 1,
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_requires_authentication(): void
    {
        $this->get(route('subscriptions.index'))->assertRedirect(route('login'));
    }

    public function test_index_renders_for_authorized_user(): void
    {
        $response = $this->actingAs($this->adminUser())->get(route('subscriptions.index'));
        $response->assertOk()->assertViewIs('subscriptions.index');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_subscription_in_pending_status(): void
    {
        $customer = $this->customer();

        $response = $this->actingAs($this->adminUser())
            ->post(route('subscriptions.store'), $this->validPayload($customer));

        $response->assertRedirect();
        $this->assertDatabaseHas('subscriptions', [
            'customer_id'       => $customer->id,
            'status'            => 'pending',
            'subscription_type' => 'primary',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser())->post(route('subscriptions.store'), []);
        $response->assertSessionHasErrors(['customer_id', 'package_id', 'subscription_type', 'billing_day']);
    }

    // -------------------------------------------------------------------------
    // Activate
    // -------------------------------------------------------------------------

    public function test_activate_transitions_pending_to_active(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $this->actingAs($this->adminUser())
            ->post(route('subscriptions.activate', $subscription));

        $this->assertDatabaseHas('subscriptions', [
            'id'     => $subscription->id,
            'status' => 'active',
        ]);
    }

    public function test_activate_converts_prospect_customer_to_active(): void
    {
        $customer     = Customer::factory()->create(['status' => CustomerStatus::Prospect->value]);
        $subscription = Subscription::factory()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $this->actingAs($this->adminUser())
            ->post(route('subscriptions.activate', $subscription));

        $this->assertDatabaseHas('customers', [
            'id'     => $customer->id,
            'status' => 'active',
        ]);
    }

    public function test_activate_fails_when_customer_already_has_active_primary_subscription(): void
    {
        $customer = $this->customer();

        // First active primary subscription
        Subscription::factory()->active()->create([
            'customer_id'       => $customer->id,
            'package_id'        => 1,
            'subscription_type' => 'primary',
        ]);

        // Second pending primary subscription
        $pending = Subscription::factory()->create([
            'customer_id'       => $customer->id,
            'package_id'        => 1,
            'subscription_type' => 'primary',
        ]);

        $response = $this->actingAs($this->adminUser())
            ->post(route('subscriptions.activate', $pending));

        $response->assertSessionHasErrors(['customer_id']);
        $this->assertDatabaseHas('subscriptions', ['id' => $pending->id, 'status' => 'pending']);
    }

    public function test_activate_fails_when_not_in_pending_or_reactivation_pending(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->active()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $response = $this->actingAs($this->adminUser())
            ->post(route('subscriptions.activate', $subscription));

        $response->assertSessionHasErrors(['status']);
    }

    // -------------------------------------------------------------------------
    // Suspend
    // -------------------------------------------------------------------------

    public function test_suspend_transitions_active_to_suspended(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->active()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $this->actingAs($this->adminUser())
            ->post(route('subscriptions.suspend', $subscription), [
                'suspension_type'   => 'manual',
                'suspension_reason' => 'Test reason',
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'id'              => $subscription->id,
            'status'          => 'suspended',
            'suspension_type' => 'manual',
        ]);
    }

    public function test_suspend_requires_reason(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->active()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $response = $this->actingAs($this->adminUser())
            ->post(route('subscriptions.suspend', $subscription), [
                'suspension_type' => 'manual',
            ]);

        $response->assertSessionHasErrors(['suspension_reason']);
    }

    // -------------------------------------------------------------------------
    // Reactivation flow
    // -------------------------------------------------------------------------

    public function test_request_reactivation_transitions_suspended_to_reactivation_pending(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->suspended()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $this->actingAs($this->adminUser())
            ->post(route('subscriptions.request-reactivation', $subscription));

        $this->assertDatabaseHas('subscriptions', [
            'id'     => $subscription->id,
            'status' => 'reactivation_pending',
        ]);
    }

    public function test_reactivate_transitions_reactivation_pending_to_active(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->reactivationPending()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $this->actingAs($this->adminUser())
            ->post(route('subscriptions.reactivate', $subscription));

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'status' => 'active']);
    }

    // -------------------------------------------------------------------------
    // Terminate
    // -------------------------------------------------------------------------

    public function test_terminate_transitions_to_terminated(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->active()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $this->actingAs($this->adminUser())
            ->post(route('subscriptions.terminate', $subscription), [
                'reason' => 'Customer requested termination of service',
            ]);

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'status' => 'terminated']);
    }

    public function test_terminate_requires_reason(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->active()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $response = $this->actingAs($this->adminUser())
            ->post(route('subscriptions.terminate', $subscription), ['reason' => '']);

        $response->assertSessionHasErrors(['reason']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_billing_day(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->create(['customer_id' => $customer->id, 'package_id' => 1, 'billing_day' => 1]);

        $this->actingAs($this->adminUser())
            ->put(route('subscriptions.update', $subscription), [
                'package_id'        => 1,
                'subscription_type' => 'primary',
                'billing_day'       => 15,
            ]);

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'billing_day' => 15]);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_pending_subscription(): void
    {
        $customer     = $this->customer();
        $subscription = Subscription::factory()->create(['customer_id' => $customer->id, 'package_id' => 1]);

        $this->actingAs($this->adminUser())
            ->delete(route('subscriptions.destroy', $subscription));

        $this->assertSoftDeleted('subscriptions', ['id' => $subscription->id]);
    }
}
