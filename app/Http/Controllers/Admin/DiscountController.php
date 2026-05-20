<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Services\Admin\DiscountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscountController extends Controller
{
    public function __construct(
        protected DiscountService $discountService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Discounts/Index', [
            'discounts' => $this->discountService->getDiscounts($request),
        ]);
    }

    public function create(Request $request): Response
    {
        $scope = $request->get('scope', 'product');

        return Inertia::render('Admin/Discounts/Create', [
            'targetOptions' => $this->discountService->getTargetOptions($scope),
            'selectedScope' => $scope,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'scope' => ['required', 'in:product,variant,store,category'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'target_ids' => ['required', 'array', 'min:1'],
            'target_ids.*' => ['integer'],
        ]);

        $this->discountService->createDiscount($validated);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'تم إنشاء الخصم بنجاح.');
    }

    public function edit(Discount $discount): Response
    {
        return Inertia::render('Admin/Discounts/Edit', [
            'discount' => $this->discountService->getDiscount($discount),
            'targetOptions' => $this->discountService->getTargetOptions($discount->scope),
        ]);
    }

    public function update(Request $request, Discount $discount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'scope' => ['required', 'in:product,variant,store,category'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'target_ids' => ['required', 'array', 'min:1'],
            'target_ids.*' => ['integer'],
        ]);

        $this->discountService->updateDiscount($discount, $validated);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'تم تحديث الخصم بنجاح.');
    }

    public function destroy(Discount $discount): RedirectResponse
    {
        $this->discountService->deleteDiscount($discount);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'تم حذف الخصم بنجاح.');
    }

    public function toggleActive(Discount $discount): RedirectResponse
    {
        $this->discountService->toggleActive($discount);

        return redirect()->back()
            ->with('success', 'تم تحديث حالة الخصم.');
    }

    /**
     * API endpoint to get target options for a given scope (used by frontend).
     */
    public function targets(Request $request)
    {
        $scope = $request->get('scope', 'product');
        return response()->json($this->discountService->getTargetOptions($scope));
    }
}
