<?php

namespace Tests\Feature;

use App\Enums\PackageStatus;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageModuleTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'super-admin', 'status' => 'active']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_package_index_requires_authentication(): void
    {
        $this->get(route('packages.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_package_in_draft_state(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->post(route('packages.store'), [
                'package_code' => 'PKG-FTTH-01',
                'name' => 'FTTH 50 Mbps',
                'downstream_kbps' => 50000,
                'upstream_kbps' => 25000,
                'contention_ratio' => 1,
                'monthly_price' => 350000,
                'setup_fee' => 150000,
                'billing_cycle_type' => 'monthly',
            ])
            ->assertRedirect();

        $package = Package::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => PackageStatus::Draft->value,
        ]);
    }

    public function test_admin_can_activate_deprecate_and_retire_package(): void
    {
        $user = $this->adminUser();
        $package = Package::factory()->create(['status' => PackageStatus::Draft->value]);

        $this->actingAs($user)
            ->post(route('packages.activate', $package))
            ->assertRedirect();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => PackageStatus::Active->value,
        ]);

        $this->actingAs($user)
            ->post(route('packages.deprecate', $package))
            ->assertRedirect();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => PackageStatus::Deprecated->value,
        ]);

        $this->actingAs($user)
            ->post(route('packages.retire', $package))
            ->assertRedirect();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => PackageStatus::Retired->value,
        ]);
    }

    public function test_package_retirement_is_blocked_when_active_subscription_exists(): void
    {
        $user = $this->adminUser();
        $package = Package::factory()->active()->create();
        $customer = Customer::factory()->active()->create();

        Subscription::factory()->active()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($user)
            ->post(route('packages.retire', $package))
            ->assertSessionHasErrors('package');

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => PackageStatus::Active->value,
        ]);
    }
}
