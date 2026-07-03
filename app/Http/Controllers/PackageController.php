<?php

namespace App\Http\Controllers;

use App\Http\Requests\Package\ActivatePackageRequest;
use App\Http\Requests\Package\DeprecatePackageRequest;
use App\Http\Requests\Package\RetirePackageRequest;
use App\Http\Requests\Package\StorePackageRequest;
use App\Http\Requests\Package\UpdatePackageRequest;
use App\Models\Package;
use App\Services\Package\PackageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        private readonly PackageService $packageService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Package::class);

        return view('packages.index', $this->packageService->buildIndexData(
            $request->only('search', 'status')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Package::class);

        return view('packages.create', $this->packageService->buildCreateData());
    }

    public function store(StorePackageRequest $request): RedirectResponse
    {
        $package = $this->packageService->create($request->validated());

        return redirect()->route('packages.show', $package)
            ->with('success', 'Package created.');
    }

    public function show(Package $package): View
    {
        $this->authorize('view', $package);

        return view('packages.show', $this->packageService->findForShow($package));
    }

    public function edit(Package $package): View
    {
        $this->authorize('update', $package);

        return view('packages.edit', $this->packageService->buildEditData($package));
    }

    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        $this->packageService->update($package, $request->validated());

        return redirect()->route('packages.show', $package)
            ->with('success', 'Package updated.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $this->authorize('delete', $package);

        $this->packageService->delete($package);

        return redirect()->route('packages.index');
    }

    public function activate(ActivatePackageRequest $request, Package $package): RedirectResponse
    {
        $this->packageService->activate($package);

        return redirect()->route('packages.show', $package)
            ->with('success', 'Package activated.');
    }

    public function deprecate(DeprecatePackageRequest $request, Package $package): RedirectResponse
    {
        $this->packageService->deprecate($package);

        return redirect()->route('packages.show', $package)
            ->with('success', 'Package deprecated.');
    }

    public function retire(RetirePackageRequest $request, Package $package): RedirectResponse
    {
        $this->packageService->retire($package);

        return redirect()->route('packages.show', $package)
            ->with('success', 'Package retired.');
    }
}
