<?php

namespace Tests\Feature;

use App\Enums\FatStatus;
use App\Models\Fat;
use App\Models\Odf;
use App\Models\Onu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FatModuleTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'super-admin', 'status' => 'active']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_fat_index_requires_authentication(): void
    {
        $this->get(route('fats.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_fat_in_planned_state(): void
    {
        $user = $this->adminUser();
        $odf = Odf::factory()->create();

        $this->actingAs($user)
            ->post(route('fats.store'), [
                'odf_id' => $odf->id,
                'fat_code' => 'FAT-HQ-01',
                'name' => 'HQ FAT 01',
                'capacity_ports' => 16,
                'used_ports' => 0,
            ])
            ->assertRedirect();

        $fat = Fat::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('fats', [
            'id' => $fat->id,
            'status' => FatStatus::Planned->value,
        ]);
    }

    public function test_admin_can_activate_maintenance_and_retire_fat(): void
    {
        $user = $this->adminUser();
        $fat = Fat::factory()->create(['status' => FatStatus::Planned->value]);

        $this->actingAs($user)
            ->post(route('fats.activate', $fat))
            ->assertRedirect();

        $this->assertDatabaseHas('fats', [
            'id' => $fat->id,
            'status' => FatStatus::Active->value,
        ]);

        $this->actingAs($user)
            ->post(route('fats.maintenance', $fat))
            ->assertRedirect();

        $this->assertDatabaseHas('fats', [
            'id' => $fat->id,
            'status' => FatStatus::Maintenance->value,
        ]);

        $this->actingAs($user)
            ->post(route('fats.retire', $fat))
            ->assertRedirect();

        $this->assertDatabaseHas('fats', [
            'id' => $fat->id,
            'status' => FatStatus::Retired->value,
        ]);
    }

    public function test_fat_retirement_is_blocked_when_downstream_onu_exists(): void
    {
        $user = $this->adminUser();
        $fat = Fat::factory()->active()->create();

        Onu::factory()->active()->forFat($fat)->create();

        $this->actingAs($user)
            ->post(route('fats.retire', $fat))
            ->assertSessionHasErrors('fat');
    }

    public function test_fat_show_displays_derived_reachability(): void
    {
        $user = $this->adminUser();
        $fat = Fat::factory()->active()->create();

        Onu::factory()->active()->forFat($fat)->create();
        Onu::factory()->offline()->forFat($fat)->create();

        $this->actingAs($user)
            ->get(route('fats.show', $fat))
            ->assertOk()
            ->assertSee('Warning');
    }
}
