<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceArea\ActivateClusterRequest;
use App\Http\Requests\ServiceArea\InactivateClusterRequest;
use App\Http\Requests\ServiceArea\StoreClusterRequest;
use App\Http\Requests\ServiceArea\UpdateClusterRequest;
use App\Models\Cluster;
use App\Services\ServiceArea\ClusterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClusterController extends Controller
{
    public function __construct(
        private readonly ClusterService $clusterService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Cluster::class);

        return view('clusters.index', $this->clusterService->buildIndexData(
            $request->only('search', 'status')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Cluster::class);

        return view('clusters.create', $this->clusterService->buildCreateData());
    }

    public function store(StoreClusterRequest $request): RedirectResponse
    {
        $cluster = $this->clusterService->create($request->validated());

        return redirect()->route('clusters.show', $cluster)
            ->with('success', 'Cluster created.');
    }

    public function show(Cluster $cluster): View
    {
        $this->authorize('view', $cluster);

        return view('clusters.show', $this->clusterService->findForShow($cluster));
    }

    public function edit(Cluster $cluster): View
    {
        $this->authorize('update', $cluster);

        return view('clusters.edit', $this->clusterService->buildEditData($cluster));
    }

    public function update(UpdateClusterRequest $request, Cluster $cluster): RedirectResponse
    {
        $this->clusterService->update($cluster, $request->validated());

        return redirect()->route('clusters.show', $cluster)
            ->with('success', 'Cluster updated.');
    }

    public function destroy(Cluster $cluster): RedirectResponse
    {
        $this->authorize('delete', $cluster);

        $this->clusterService->delete($cluster);

        return redirect()->route('clusters.index');
    }

    public function activate(ActivateClusterRequest $request, Cluster $cluster): RedirectResponse
    {
        $this->clusterService->activate($cluster);

        return redirect()->route('clusters.show', $cluster)
            ->with('success', 'Cluster activated.');
    }

    public function inactivate(InactivateClusterRequest $request, Cluster $cluster): RedirectResponse
    {
        $this->clusterService->inactivate($cluster);

        return redirect()->route('clusters.show', $cluster)
            ->with('success', 'Cluster inactivated.');
    }
}