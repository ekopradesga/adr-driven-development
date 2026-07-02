<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Requests\Subscription\SuspendSubscriptionRequest;
use App\Http\Requests\Subscription\TerminateSubscriptionRequest;
use App\Http\Requests\Subscription\UpdateSubscriptionRequest;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SubscriptionController — Customer Management module.
 * Orchestration only. Zero business logic.
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Subscription::class);

        return view('subscriptions.index', $this->subscriptionService->buildIndexData(
            $request->only('search', 'status', 'customer_id')
        ));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Subscription::class);

        return view('subscriptions.create', $this->subscriptionService->buildCreateData(
            $request->only('customer_id')
        ));
    }

    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $subscription = $this->subscriptionService->create($request->validated());

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', "Subscription #{$subscription->id} created.");
    }

    public function show(Subscription $subscription): View
    {
        $this->authorize('view', $subscription);

        return view('subscriptions.show', $this->subscriptionService->findForShow($subscription));
    }

    public function edit(Subscription $subscription): View
    {
        $this->authorize('update', $subscription);

        return view('subscriptions.edit', $this->subscriptionService->buildEditData($subscription));
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $this->subscriptionService->update($subscription, $request->validated());

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription updated.');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        $this->authorize('delete', $subscription);

        $this->subscriptionService->delete($subscription);

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription deleted.');
    }

    // -------------------------------------------------------------------------
    // Lifecycle Actions
    // -------------------------------------------------------------------------

    public function activate(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('activate', $subscription);

        $this->subscriptionService->activate($subscription);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription activated.');
    }

    public function suspend(SuspendSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $this->subscriptionService->suspend(
            $subscription,
            $request->validated('suspension_type'),
            $request->validated('suspension_reason')
        );

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription suspended.');
    }

    public function requestReactivation(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('reactivate', $subscription);

        $this->subscriptionService->requestReactivation($subscription);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Reactivation requested.');
    }

    public function reactivate(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('reactivate', $subscription);

        $this->subscriptionService->reactivate($subscription);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription reactivated.');
    }

    public function terminate(TerminateSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $this->subscriptionService->terminate($subscription, $request->validated('reason'));

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription terminated.');
    }
}
