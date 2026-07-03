<?php

namespace Tests\Feature;

use App\Enums\ClusterStatus;
use App\Enums\ServiceAreaStatus;
use App\Models\Cluster;
use App\Models\Employee;
use App\Models\Role;
use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceAreaModuleTest extends TestCase
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

    public function test_admin_can_create_and_activate_cluster(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->post(route('clusters.store'), [
                'name' => 'Central Operations',
                'code' => 'CLU-CEN-01',
            ])
            ->assertRedirect();

        $cluster = Cluster::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('clusters', [
            'id' => $cluster->id,
            'status' => ClusterStatus::Planned->value,
        ]);

        $this->actingAs($user)
            ->post(route('clusters.activate', $cluster))
            ->assertRedirect();

        $this->assertDatabaseHas('clusters', [
            'id' => $cluster->id,
            'status' => ClusterStatus::Active->value,
        ]);
    }

    public function test_admin_can_create_activate_assign_and_archive_service_area(): void
    {
        $user = $this->adminUser();
        $cluster = Cluster::factory()->active()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('service-areas.store'), [
                'cluster_id' => $cluster->id,
                'name' => 'North Branch',
                'code' => 'SAR-NTH-01',
                'level' => 'branch',
                'status' => ServiceAreaStatus::Draft->value,
            ])
            ->assertRedirect();

        $serviceArea = ServiceArea::query()->latest('id')->firstOrFail();

        $this->actingAs($user)
            ->post(route('service-areas.activate', $serviceArea))
            ->assertRedirect();

        $this->assertDatabaseHas('service_areas', [
            'id' => $serviceArea->id,
            'status' => ServiceAreaStatus::Active->value,
        ]);

        $this->actingAs($user)
            ->post(route('service-areas.employees.assign', $serviceArea), [
                'employee_id' => $employee->id,
                'is_primary' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('employee_service_area', [
            'employee_id' => $employee->id,
            'service_area_id' => $serviceArea->id,
            'is_primary' => 1,
        ]);

        $this->actingAs($user)
            ->delete(route('service-areas.employees.remove', [$serviceArea, $employee]))
            ->assertRedirect();

        $this->assertDatabaseMissing('employee_service_area', [
            'employee_id' => $employee->id,
            'service_area_id' => $serviceArea->id,
        ]);

        $this->actingAs($user)
            ->post(route('service-areas.archive', $serviceArea))
            ->assertRedirect();

        $this->assertDatabaseHas('service_areas', [
            'id' => $serviceArea->id,
            'status' => ServiceAreaStatus::Archived->value,
        ]);
    }

    public function test_admin_can_merge_service_area_after_dependencies_are_clear(): void
    {
        $user = $this->adminUser();
        $cluster = Cluster::factory()->active()->create();
        $source = ServiceArea::factory()->active()->create(['cluster_id' => $cluster->id]);
        $destination = ServiceArea::factory()->active()->create(['cluster_id' => $cluster->id]);

        $this->actingAs($user)
            ->post(route('service-areas.merge', $source), [
                'merged_into_service_area_id' => $destination->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_areas', [
            'id' => $source->id,
            'status' => ServiceAreaStatus::Merged->value,
            'merged_into_service_area_id' => $destination->id,
        ]);
    }
}