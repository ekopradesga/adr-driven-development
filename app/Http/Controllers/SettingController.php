<?php

namespace App\Http\Controllers;

use App\Enums\SettingScope;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\Settings\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Setting::class);

        $perPage = max((int) $request->integer('per_page', 25), 1);

        return view('settings.index', $this->settingService->buildIndexData(
            $request->only(['search', 'category', 'scope', 'is_active']),
            $perPage
        ));
    }

    public function edit(Setting $setting): View
    {
        $this->authorize('view', $setting);

        return view('settings.edit', $this->settingService->buildEditData($setting));
    }

    public function update(UpdateSettingRequest $request, Setting $setting): RedirectResponse
    {
        $this->authorize('update', $setting);

        $this->settingService->updateSetting(
            $setting,
            $request->validated('value'),
            $request->boolean('is_active')
        );

        return redirect()
            ->route('settings.edit', $setting)
            ->with('success', 'Setting updated successfully.');
    }
}
