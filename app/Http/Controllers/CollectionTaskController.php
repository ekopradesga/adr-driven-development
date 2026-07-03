<?php

namespace App\Http\Controllers;

use App\Http\Requests\Collector\AssignCollectionTaskRequest;
use App\Http\Requests\Collector\CancelCollectionTaskRequest;
use App\Http\Requests\Collector\CompleteCollectionTaskRequest;
use App\Http\Requests\Collector\CreateCollectionTaskInvoiceRequest;
use App\Http\Requests\Collector\FollowUpCollectionTaskRequest;
use App\Http\Requests\Collector\RecordCollectionTaskVisitRequest;
use App\Http\Requests\Collector\ResolveCollectionTaskInvoiceRequest;
use App\Http\Requests\Collector\ScheduleCollectionTaskRequest;
use App\Http\Requests\Collector\StartCollectionTaskRouteRequest;
use App\Http\Requests\Collector\StoreCollectionTaskRequest;
use App\Http\Requests\Collector\UpdateCollectionTaskRequest;
use App\Models\CollectionTask;
use App\Models\CollectionTaskInvoice;
use App\Services\Collector\CollectionTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionTaskController extends Controller
{
    public function __construct(
        private readonly CollectionTaskService $collectionTaskService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CollectionTask::class);

        return view('collection_tasks.index', $this->collectionTaskService->buildIndexData(
            $request->only('search', 'status', 'customer_id', 'employee_id')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', CollectionTask::class);

        return view('collection_tasks.create', $this->collectionTaskService->buildCreateData());
    }

    public function store(StoreCollectionTaskRequest $request): RedirectResponse
    {
        $collectionTask = $this->collectionTaskService->create($request->validated());

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Collection task created.');
    }

    public function show(CollectionTask $collectionTask): View
    {
        $this->authorize('view', $collectionTask);

        return view('collection_tasks.show', $this->collectionTaskService->findForShow($collectionTask));
    }

    public function edit(CollectionTask $collectionTask): View
    {
        $this->authorize('update', $collectionTask);

        return view('collection_tasks.edit', $this->collectionTaskService->buildEditData($collectionTask));
    }

    public function update(UpdateCollectionTaskRequest $request, CollectionTask $collectionTask): RedirectResponse
    {
        $this->collectionTaskService->update($collectionTask, $request->validated());

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Collection task updated.');
    }

    public function destroy(CollectionTask $collectionTask): RedirectResponse
    {
        $this->authorize('delete', $collectionTask);

        $this->collectionTaskService->delete($collectionTask);

        return redirect()->route('collection-tasks.index');
    }

    public function assign(AssignCollectionTaskRequest $request, CollectionTask $collectionTask): RedirectResponse
    {
        $this->collectionTaskService->assign($collectionTask, (int) $request->validated('employee_id'));

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Collection task assigned.');
    }

    public function schedule(ScheduleCollectionTaskRequest $request, CollectionTask $collectionTask): RedirectResponse
    {
        $this->collectionTaskService->schedule($collectionTask, $request->validated());

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Collection task scheduled.');
    }

    public function startRoute(StartCollectionTaskRouteRequest $request, CollectionTask $collectionTask): RedirectResponse
    {
        $this->collectionTaskService->startRoute($collectionTask);

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Route execution started.');
    }

    public function recordVisit(RecordCollectionTaskVisitRequest $request, CollectionTask $collectionTask): RedirectResponse
    {
        $this->collectionTaskService->recordVisit($collectionTask, $request->validated());

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Customer visit recorded.');
    }

    public function complete(CompleteCollectionTaskRequest $request, CollectionTask $collectionTask): RedirectResponse
    {
        $this->collectionTaskService->complete($collectionTask);

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Collection task completed.');
    }

    public function followUpRequired(FollowUpCollectionTaskRequest $request, CollectionTask $collectionTask): RedirectResponse
    {
        $this->collectionTaskService->followUpRequired($collectionTask, $request->validated('follow_up_reason'));

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Collection task marked for follow up.');
    }

    public function cancel(CancelCollectionTaskRequest $request, CollectionTask $collectionTask): RedirectResponse
    {
        $this->collectionTaskService->cancel($collectionTask, $request->validated('cancellation_reason'));

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Collection task cancelled.');
    }

    public function storeInvoice(CreateCollectionTaskInvoiceRequest $request, CollectionTask $collectionTask): RedirectResponse
    {
        $this->collectionTaskService->addInvoice($collectionTask, $request->validated());

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Collection task invoice added.');
    }

    public function resolveInvoice(ResolveCollectionTaskInvoiceRequest $request, CollectionTask $collectionTask, CollectionTaskInvoice $collectionTaskInvoice): RedirectResponse
    {
        abort_unless($collectionTaskInvoice->collection_task_id === $collectionTask->id, 404);

        $this->collectionTaskService->resolveInvoice($collectionTaskInvoice, $request->validated());

        return redirect()->route('collection-tasks.show', $collectionTask)
            ->with('success', 'Collection task invoice resolved.');
    }
}