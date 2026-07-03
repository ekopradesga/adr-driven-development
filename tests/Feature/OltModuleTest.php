<?php

namespace Tests\Feature;

use App\Enums\OltStatus;
use App\Models\Olt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OltModuleTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'super-admin', 'status' => 'active']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_olt_index_requires_authentication(): void
    {
        $this->get(route('olts.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_olt_in_planned_state(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->post(route('olts.store'), [
                'olt_code' => 'OLT-HQ-01',
                'name' => 'HQ Main OLT',
                'ip_address' => '10.10.10.10',
                'vendor' => 'Huawei',
            ])
            ->assertRedirect();

        $olt = Olt::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('olts', [
            'id' => $olt->id,
            'status' => OltStatus::Planned->value,
        ]);
    }

    public function test_admin_can_activate_move_to_maintenance_and_retire_olt(): void
    {
        $user = $this->adminUser();
        $olt = Olt::factory()->create(['status' => OltStatus::Planned->value]);

        $this->actingAs($user)
            ->post(route('olts.activate', $olt))
            ->assertRedirect();

        $this->assertDatabaseHas('olts', [
            'id' => $olt->id,
            'status' => OltStatus::Active->value,
        ]);

        $this->actingAs($user)
            ->post(route('olts.maintenance', $olt))
            ->assertRedirect();

        $this->assertDatabaseHas('olts', [
            'id' => $olt->id,
            'status' => OltStatus::Maintenance->value,
        ]);

        $this->actingAs($user)
            ->post(route('olts.retire', $olt))
            ->assertRedirect();

        $this->assertDatabaseHas('olts', [
            'id' => $olt->id,
            'status' => OltStatus::Retired->value,
        ]);
    }
}
