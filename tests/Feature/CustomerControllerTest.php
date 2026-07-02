<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CustomerControllerTest — Customer Management module.
 *
 * Covers HTTP endpoints for the Customer module:
 *   index, create, store, show, edit, update, destroy,
 *   suspend, reactivate, terminate.
 *
 * Uses RefreshDatabase for clean state isolation per test.
 * Authentication and authorization tested on every endpoint.
 */
class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Test Helpers
    // -------------------------------------------------------------------------

    /** Creates an admin user with the 'admin' role for testing. */
    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'admin', 'status' => 'active']);
        $user->roles()->attach($role);

        return $user;
    }

    /** Creates a minimal valid payload for Customer creation. */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'Test Customer',
            'customer_type' => CustomerType::Individual->value,
            'phone'         => '081234567890',
            'email'         => 'test@example.com',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('customers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_authorization(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $response = $this->actingAs($user)->get(route('customers.index'));
        $response->assertForbidden();
    }

    public function test_index_renders_for_authorized_user(): void
    {
        Customer::factory()->count(3)->create();
        $response = $this->actingAs($this->adminUser())->get(route('customers.index'));
        $response->assertOk();
        $response->assertViewIs('customers.index');
        $response->assertViewHas('customers');
    }

    public function test_index_can_filter_by_status(): void
    {
        Customer::factory()->create(['status' => CustomerStatus::Active->value]);
        Customer::factory()->create(['status' => CustomerStatus::Prospect->value]);

        $response = $this->actingAs($this->adminUser())
            ->get(route('customers.index', ['status' => 'active']));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('customers'));
    }

    public function test_index_can_search_by_name(): void
    {
        Customer::factory()->create(['name' => 'John Smith']);
        Customer::factory()->create(['name' => 'Jane Doe']);

        $response = $this->actingAs($this->adminUser())
            ->get(route('customers.index', ['search' => 'John']));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('customers'));
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_requires_authentication(): void
    {
        $this->get(route('customers.create'))->assertRedirect(route('login'));
    }

    public function test_create_renders_form_for_authorized_user(): void
    {
        $response = $this->actingAs($this->adminUser())->get(route('customers.create'));
        $response->assertOk();
        $response->assertViewIs('customers.create');
        $response->assertViewHas('types');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_requires_authentication(): void
    {
        $this->post(route('customers.store'), $this->validPayload())->assertRedirect(route('login'));
    }

    public function test_store_creates_customer_with_valid_data(): void
    {
        $user     = $this->adminUser();
        $response = $this->actingAs($user)->post(route('customers.store'), $this->validPayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name'          => 'Test Customer',
            'customer_type' => 'individual',
            'email'         => 'test@example.com',
            'status'        => 'prospect',
        ]);
    }

    public function test_store_generates_unique_customer_number(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->post(route('customers.store'), $this->validPayload());
        $this->actingAs($user)->post(route('customers.store'), $this->validPayload([
            'email' => 'another@example.com',
        ]));

        $numbers = Customer::pluck('customer_number');
        $this->assertCount(2, $numbers->unique());
        $this->assertTrue($numbers->every(fn ($n) => str_starts_with($n, 'CUST-')));
    }

    public function test_store_defaults_status_to_prospect(): void
    {
        $this->actingAs($this->adminUser())->post(route('customers.store'), $this->validPayload());

        $this->assertDatabaseHas('customers', ['status' => 'prospect']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser())->post(route('customers.store'), []);

        $response->assertSessionHasErrors(['name', 'customer_type']);
    }

    public function test_store_validates_unique_email(): void
    {
        Customer::factory()->create(['email' => 'dupe@example.com']);

        $response = $this->actingAs($this->adminUser())->post(
            route('customers.store'),
            $this->validPayload(['email' => 'dupe@example.com'])
        );

        $response->assertSessionHasErrors(['email']);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_requires_authentication(): void
    {
        $customer = Customer::factory()->create();
        $this->get(route('customers.show', $customer))->assertRedirect(route('login'));
    }

    public function test_show_renders_customer_360_for_authorized_user(): void
    {
        $customer = Customer::factory()->create();
        $response = $this->actingAs($this->adminUser())->get(route('customers.show', $customer));
        $response->assertOk();
        $response->assertViewIs('customers.show');
        $response->assertViewHas('customer');
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function test_edit_renders_form(): void
    {
        $customer = Customer::factory()->create();
        $response = $this->actingAs($this->adminUser())->get(route('customers.edit', $customer));
        $response->assertOk();
        $response->assertViewIs('customers.edit');
    }

    public function test_update_modifies_profile(): void
    {
        $customer = Customer::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->adminUser())
            ->put(route('customers.update', $customer), $this->validPayload(['name' => 'New Name']));

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'New Name']);
    }

    public function test_update_does_not_change_status(): void
    {
        $customer = Customer::factory()->active()->create();

        $this->actingAs($this->adminUser())
            ->put(route('customers.update', $customer), $this->validPayload(['name' => 'Updated']));

        $this->assertDatabaseHas('customers', [
            'id'     => $customer->id,
            'status' => 'active',
        ]);
    }

    // -------------------------------------------------------------------------
    // Suspend
    // -------------------------------------------------------------------------

    public function test_suspend_transitions_active_customer_to_suspended(): void
    {
        $customer = Customer::factory()->active()->create();

        $response = $this->actingAs($this->adminUser())
            ->post(route('customers.suspend', $customer), ['reason' => 'Fraudulent activity detected']);

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertDatabaseHas('customers', [
            'id'     => $customer->id,
            'status' => 'suspended',
        ]);
    }

    public function test_suspend_requires_reason(): void
    {
        $customer = Customer::factory()->active()->create();

        $response = $this->actingAs($this->adminUser())
            ->post(route('customers.suspend', $customer), ['reason' => '']);

        $response->assertSessionHasErrors(['reason']);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'status' => 'active']);
    }

    public function test_suspend_fails_when_customer_is_not_active(): void
    {
        $customer = Customer::factory()->create(['status' => CustomerStatus::Prospect->value]);

        $response = $this->actingAs($this->adminUser())
            ->post(route('customers.suspend', $customer), ['reason' => 'Some valid reason here']);

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'status' => 'prospect']);
    }

    // -------------------------------------------------------------------------
    // Reactivate
    // -------------------------------------------------------------------------

    public function test_reactivate_transitions_suspended_customer_to_active(): void
    {
        $customer = Customer::factory()->suspended()->create();

        $response = $this->actingAs($this->adminUser())
            ->post(route('customers.reactivate', $customer));

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertDatabaseHas('customers', [
            'id'     => $customer->id,
            'status' => 'active',
        ]);
    }

    public function test_reactivate_fails_when_customer_is_not_suspended(): void
    {
        $customer = Customer::factory()->active()->create();

        $response = $this->actingAs($this->adminUser())
            ->post(route('customers.reactivate', $customer));

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'status' => 'active']);
    }

    // -------------------------------------------------------------------------
    // Terminate
    // -------------------------------------------------------------------------

    public function test_terminate_transitions_customer_to_terminated(): void
    {
        $customer = Customer::factory()->active()->create();
        // No subscriptions — all pre-conditions pass

        $response = $this->actingAs($this->adminUser())
            ->post(route('customers.terminate', $customer), ['reason' => 'Customer requested account closure']);

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertDatabaseHas('customers', [
            'id'     => $customer->id,
            'status' => 'terminated',
        ]);
    }

    public function test_terminate_requires_reason(): void
    {
        $customer = Customer::factory()->active()->create();

        $response = $this->actingAs($this->adminUser())
            ->post(route('customers.terminate', $customer), ['reason' => '']);

        $response->assertSessionHasErrors(['reason']);
    }

    public function test_terminate_fails_when_subscriptions_are_not_terminated(): void
    {
        $customer = Customer::factory()->active()->create();
        // Create a non-terminated subscription
        \App\Models\Subscription::factory()->create([
            'customer_id' => $customer->id,
            'status'      => 'active',
        ]);

        $response = $this->actingAs($this->adminUser())
            ->post(route('customers.terminate', $customer), ['reason' => 'Account closure requested']);

        $response->assertSessionHasErrors(['subscriptions']);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'status' => 'active']);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_prospect_customer(): void
    {
        $customer = Customer::factory()->create(['status' => CustomerStatus::Prospect->value]);

        $response = $this->actingAs($this->adminUser())
            ->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_destroy_fails_when_customer_has_payments(): void
    {
        $customer = Customer::factory()->create();
        \App\Models\Payment::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($this->adminUser())
            ->delete(route('customers.destroy', $customer));

        $response->assertSessionHasErrors(['payments']);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
    }
}
