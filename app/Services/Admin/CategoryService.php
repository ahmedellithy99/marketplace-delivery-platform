<?php

namespace App\Services\Admin;

use App\Exceptions\CircularHierarchyException;
use App\Filters\Admin\CategoryFilter;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /**
     * Get categories with optional filtering and pagination.
     */
    public function getCategories(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return Category::with(['parent', 'children', 'media'])
            ->filter(new CategoryFilter($request))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single category with relationships.
     */
    public function getCategory(Category $category): Category
    {
        return $category->load(['parent', 'children', 'media']);
    }

    /**
     * Get root categories for parent selection dropdowns.
     * Optionally exclude a specific category (for edit forms).
     */
    public function getParentOptions(?int $excludeId = null)
    {
        $query = Category::whereNull('parent_id');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get(['id', 'name']);
    }

    /**
     * Create a new category.
     *
     * @param array $data
     * @param UploadedFile|null $image
     * @return Category
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function createCategory(array $data, ?UploadedFile $image = null): Category
    {
        if (!empty($data['parent_id'])) {
            Category::findOrFail($data['parent_id']);
        }

        $category = Category::create([
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        if ($image) {
            $category->addMedia($image)->toMediaCollection('image');
        }

        return $category->load(['parent', 'children']);
    }

    /**
     * Update an existing category.
     *
     * @param Category $category
     * @param array $data
     * @param UploadedFile|null $image
     * @return Category
     * @throws CircularHierarchyException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateCategory(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        $newParentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $category->parent_id;

        if (!empty($newParentId)) {
            Category::findOrFail($newParentId);
            $this->detectCircularHierarchy($category, $newParentId);
        }

        $categoryData = collect($data)->except(['image'])->toArray();

        $category->update($categoryData);

        if ($image) {
            $category->addMedia($image)->toMediaCollection('image');
        }

        $category->refresh();
        $category->load(['parent', 'children']);

        return $category;
    }

    /**
     * Delete a category with child reassignment.
     *
     * Reassigns all direct children to the deleted category's parent
     * (or to root/null if no parent exists). Then soft-deletes.
     */
    public function deleteCategory(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $category->children()->update(['parent_id' => $category->parent_id]);
            $category->delete();
        });
    }

    /**
     * Detect circular hierarchy by traversing ancestors from the proposed parent.
     */
    protected function detectCircularHierarchy(Category $category, int $newParentId): void
    {
        if ((int) $category->id === $newParentId) {
            throw new CircularHierarchyException();
        }

        $currentId = $newParentId;

        while ($currentId !== null) {
            $ancestor = Category::find($currentId);

            if ($ancestor === null) {
                break;
            }

            if ($ancestor->parent_id === $category->id) {
                throw new CircularHierarchyException();
            }

            $currentId = $ancestor->parent_id;
        }
    }
}
