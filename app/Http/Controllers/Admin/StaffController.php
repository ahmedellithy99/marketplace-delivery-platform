<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffStoreRequest;
use App\Http\Requests\Admin\StaffUpdateRequest;
use App\Models\User;
use App\Services\Admin\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function __construct(
        protected StaffService $staffService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Staff/Index', [
            'staff' => $this->staffService->getStaff($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Staff/Create');
    }

    public function store(StaffStoreRequest $request): RedirectResponse
    {
        $this->staffService->createStaff($request->validated());

        return redirect()->route('admin.staff.index')
            ->with('success', 'تم إضافة الموظف بنجاح.');
    }

    public function edit(User $staff): Response
    {
        return Inertia::render('Admin/Staff/Edit', [
            'staffMember' => $staff,
        ]);
    }

    public function update(StaffUpdateRequest $request, User $staff): RedirectResponse
    {
        $this->staffService->updateStaff($staff, $request->validated());

        return redirect()->route('admin.staff.index')
            ->with('success', 'تم تحديث بيانات الموظف بنجاح.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $this->staffService->deleteStaff($staff);

        return redirect()->route('admin.staff.index')
            ->with('success', 'تم حذف الموظف بنجاح.');
    }
}
