<?php

namespace Tests\Feature;

use App\Enums\WireRouterStatus;
use App\Enums\WireRouterType;
use App\Models\Role;
use App\Models\WireRouter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WireRouterModuleTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'super-admin', 'status' => 'active']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_router_index_requires_authentication(): void
    {
        $this->get(route('routers.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_router_in_planned_state(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->post(route('routers.store'), [
                'router_code' => 'RTR-HQ-01',
                'name' => 'HQ Core Router',
                'router_type' => WireRouterType::Core->value,
                'ip_address' => '10.20.30.1',
                'vendor' => 'Cisco',
            ])
            ->assertRedirect();

        $router = WireRouter::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('routers', [
            'id' => $router->id,
            'status' => WireRouterStatus::Planned->value,
        ]);
    }

    public function test_admin_can_activate_move_to_maintenance_and_retire_router(): void
    {
        $user = $this->adminUser();
        $router = WireRouter::factory()->create(['status' => WireRouterStatus::Planned->value]);

        $this->actingAs($user)
            ->post(route('routers.activate', $router))
            ->assertRedirect();

        $this->assertDatabaseHas('routers', [
            'id' => $router->id,
            'status' => WireRouterStatus::Active->value,
        ]);

        $this->actingAs($user)
            ->post(route('routers.maintenance', $router))
            ->assertRedirect();

        $this->assertDatabaseHas('routers', [
            'id' => $router->id,
            'status' => WireRouterStatus::Maintenance->value,
        ]);

        $this->actingAs($user)
            ->post(route('routers.retire', $router))
            ->assertRedirect();

        $this->assertDatabaseHas('routers', [
            'id' => $router->id,
            'status' => WireRouterStatus::Retired->value,
        ]);
    }
}