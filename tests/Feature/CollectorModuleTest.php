<?php

namespace Tests\Feature;

use App\Enums\CollectionTaskInvoiceStatus;
use App\Enums\CollectionTaskStatus;
use App\Models\CollectionTask;
use App\Models\CollectionTaskInvoice;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectorModuleTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::factory()->create(['slug' => 'super-admin', 'status' => 'active']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_collection_task_index_requires_authentication(): void
    {
        $this->get(route('collection-tasks.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_collection_task_and_progress_it_through_the_workflow(): void
    {
        $user = $this->adminUser();
        $customer = Customer::factory()->active()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('collection-tasks.store'), [
                'customer_id' => $customer->id,
                'notes' => 'Initial collection visit.',
            ])
            ->assertRedirect();

        $task = CollectionTask::query()->latest('id')->firstOrFail();

        $this->assertSame(CollectionTaskStatus::WaitingAssignment->value, $task->status->value);

        $this->actingAs($user)
            ->post(route('collection-tasks.assign', $task), [
                'employee_id' => $employee->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('collection_tasks', [
            'id' => $task->id,
            'employee_id' => $employee->id,
            'status' => CollectionTaskStatus::Assigned->value,
        ]);

        $this->actingAs($user)
            ->post(route('collection-tasks.schedule', $task), [
                'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('collection_tasks', [
            'id' => $task->id,
            'status' => CollectionTaskStatus::Scheduled->value,
        ]);

        $this->actingAs($user)->post(route('collection-tasks.start-route', $task))->assertRedirect();
        $this->assertDatabaseHas('collection_tasks', [
            'id' => $task->id,
            'status' => CollectionTaskStatus::OnRoute->value,
        ]);

        $this->actingAs($user)
            ->post(route('collection-tasks.record-visit', $task), [
                'payment_collected_amount' => 125000,
                'payment_submission_reference' => 'SUB-001',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('collection_tasks', [
            'id' => $task->id,
            'status' => CollectionTaskStatus::CustomerVisited->value,
            'payment_submission_reference' => 'SUB-001',
        ]);

        $this->actingAs($user)->post(route('collection-tasks.complete', $task))->assertRedirect();
        $this->assertDatabaseHas('collection_tasks', [
            'id' => $task->id,
            'status' => CollectionTaskStatus::Completed->value,
        ]);
    }

    public function test_admin_can_add_and_resolve_collection_task_invoice(): void
    {
        $user = $this->adminUser();
        $customer = Customer::factory()->active()->create();
        $task = CollectionTask::factory()->create(['customer_id' => $customer->id]);
        $invoice = Invoice::factory()->published()->create(['customer_id' => $customer->id]);

        $this->actingAs($user)
            ->post(route('collection-tasks.invoices.store', $task), [
                'invoice_id' => $invoice->id,
                'inclusion_reason' => 'Outstanding balance follow-up.',
            ])
            ->assertRedirect();

        $collectionTaskInvoice = CollectionTaskInvoice::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('collection_task_invoices', [
            'id' => $collectionTaskInvoice->id,
            'collection_task_id' => $task->id,
            'invoice_id' => $invoice->id,
            'status' => CollectionTaskInvoiceStatus::Created->value,
        ]);

        $this->actingAs($user)
            ->post(route('collection-tasks.invoices.resolve', [$task, $collectionTaskInvoice]), [
                'resolution_outcome' => 'settled',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('collection_task_invoices', [
            'id' => $collectionTaskInvoice->id,
            'status' => CollectionTaskInvoiceStatus::Resolved->value,
            'resolution_outcome' => 'settled',
        ]);
    }
}