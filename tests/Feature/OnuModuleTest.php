<?php

namespace Tests\Feature;

use App\Enums\OnuStatus;
use App\Models\Olt;
use App\Models\Onu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnuModuleTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'super-admin', 'status' => 'active']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_onu_index_requires_authentication(): void
    {
        $this->get(route('onus.index'))->assertRedirect(route('login')); 
    }

    public function test_admin_can_create_onu_in_unprovisioned_state(): void
    {
        $user = $this->adminUser();
        $olt = Olt::factory()->create(['status' => 'active']);

        $this->actingAs($user)
            ->post(route('onus.store'), [
                'olt_id' => $olt->id,
                'onu_sn' => 'ONU-HQ-01',
                'onu_index' => 1,
                'pon_port' => 'PON-1',
                'model' => 'ONU-01',
            ])
            ->assertRedirect();

        $onu = Onu::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('onus', [
            'id' => $onu->id,
            'status' => OnuStatus::Unprovisioned->value,
        ]);
    }

    public function test_admin_can_activate_mark_offline_suspend_and_retire_onu(): void
    {
        $user = $this->adminUser();
        $onu = Onu::factory()->create(['status' => OnuStatus::Unprovisioned->value]);

        $this->actingAs($user)
            ->post(route('onus.activate', $onu))
            ->assertRedirect();

        $this->assertDatabaseHas('onus', [
            'id' => $onu->id,
            'status' => OnuStatus::Active->value,
        ]);

        $this->actingAs($user)
            ->post(route('onus.offline', $onu))
            ->assertRedirect();

        $this->assertDatabaseHas('onus', [
            'id' => $onu->id,
            'status' => OnuStatus::Offline->value,
        ]);

        $this->actingAs($user)
            ->post(route('onus.suspend', $onu))
            ->assertRedirect();

        $this->assertDatabaseHas('onus', [
            'id' => $onu->id,
            'status' => OnuStatus::Suspended->value,
        ]);

        $this->actingAs($user)
            ->post(route('onus.retire', $onu))
            ->assertRedirect();

        $this->assertDatabaseHas('onus', [
            'id' => $onu->id,
            'status' => OnuStatus::Retired->value,
        ]);
    }
}