<?php

namespace Tests\Feature;

use App\Enums\ClusterStatus;
use App\Enums\CustomerStatus;
use App\Models\Cluster;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClusterModuleTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'super-admin', 'status' => 'active']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_cluster_index_requires_authentication(): void
    {
        $this->get(route('clusters.index'))->assertRedirect(route('login'));
    }

    public function test_cluster_creation_always_starts_as_planned(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->post(route('clusters.store'), [
                'name' => 'East Ops',
                'code' => 'CLU-EAS-01',
                'status' => ClusterStatus::Active->value,
            ])
            ->assertRedirect();

        $cluster = Cluster::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('clusters', [
            'id' => $cluster->id,
            'status' => ClusterStatus::Planned->value,
        ]);
    }

    public function test_planned_cluster_can_be_inactivated_with_historical_customers_only(): void
    {
        $user = $this->adminUser();
        $cluster = Cluster::factory()->create(['status' => ClusterStatus::Planned->value]);

        Customer::factory()->create([
            'cluster_id' => $cluster->id,
            'status' => CustomerStatus::Terminated->value,
        ]);

        $this->actingAs($user)
            ->post(route('clusters.inactivate', $cluster))
            ->assertRedirect();

        $this->assertDatabaseHas('clusters', [
            'id' => $cluster->id,
            'status' => ClusterStatus::Inactive->value,
        ]);
    }

    public function test_cluster_inactivation_is_blocked_when_active_customer_exists(): void
    {
        $user = $this->adminUser();
        $cluster = Cluster::factory()->create(['status' => ClusterStatus::Active->value]);

        Customer::factory()->create([
            'cluster_id' => $cluster->id,
            'status' => CustomerStatus::Active->value,
        ]);

        $this->actingAs($user)
            ->post(route('clusters.inactivate', $cluster))
            ->assertSessionHasErrors('cluster');

        $this->assertDatabaseHas('clusters', [
            'id' => $cluster->id,
            'status' => ClusterStatus::Active->value,
        ]);
    }
}
