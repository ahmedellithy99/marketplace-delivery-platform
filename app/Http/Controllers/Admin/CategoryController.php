<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use App\Models\Category;
use App\Services\Admin\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => $this->categoryService->getCategories($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Create', [
            'parentCategories' => $this->categoryService->getParentOptions(),
        ]);
    }

    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        $this->categoryService->createCategory(
            $request->validated(),
            $request->file('image')
        );

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم إنشاء القسم بنجاح.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $this->categoryService->getCategory($category),
            'parentCategories' => $this->categoryService->getParentOptions($category->id),
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        $this->categoryService->updateCategory(
            $category,
            $request->validated(),
            $request->file('image')
        );

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم تحديث القسم بنجاح.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->categoryService->deleteCategory($category);

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم حذف القسم بنجاح.');
    }
}
