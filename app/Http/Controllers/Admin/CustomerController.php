<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Customers/Index', [
            'customers' => $this->customerService->getCustomers($request),
        ]);
    }

    public function trash(Request $request): Response
    {
        return Inertia::render('Admin/Customers/Trash', [
            'customers' => $this->customerService->getTrashedCustomers($request),
        ]);
    }

    public function show(Request $request, User $customer): Response
    {
        $customer->loadCount('orders');

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
            'orders' => $this->customerService->getCustomerOrders($customer, $request),
        ]);
    }

    public function destroy(User $customer): RedirectResponse
    {
        $this->customerService->deleteCustomer($customer);

        return redirect()->route('admin.customers.index')
            ->with('success', 'تم حذف العميل بنجاح.');
    }

    public function restore(int $customer): RedirectResponse
    {
        $this->customerService->restoreCustomer($customer);

        return redirect()->route('admin.customers.trash')
            ->with('success', 'تم استعادة العميل بنجاح.');
    }

    public function forceDestroy(int $customer): RedirectResponse
    {
        $this->customerService->forceDeleteCustomer($customer);

        return redirect()->route('admin.customers.trash')
            ->with('success', 'تم حذف العميل نهائياً.');
    }
}
