<?php

namespace Tests\Feature\Services;

use App\Exceptions\CircularHierarchyException;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CategoryService();
    }

    // ─── Create Tests ──────────────────────────────────────────────────

    public function test_create_category_with_name_generates_slug(): void
    {
        $category = $this->service->createCategory(['name' => 'Beverages']);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Beverages',
            'parent_id' => null,
        ]);
        $this->assertNotEmpty($category->slug);
    }

    public function test_create_category_with_valid_parent(): void
    {
        $parent = Category::factory()->create();

        $category = $this->service->createCategory([
            'name' => 'Hot Drinks',
            'parent_id' => $parent->id,
        ]);

        $this->assertEquals($parent->id, $category->parent_id);
    }

    public function test_create_category_with_invalid_parent_throws_exception(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->createCategory([
            'name' => 'Orphan',
            'parent_id' => 9999,
        ]);
    }

    public function test_create_category_with_null_parent_creates_root_category(): void
    {
        $category = $this->service->createCategory([
            'name' => 'Root Category',
            'parent_id' => null,
        ]);

        $this->assertNull($category->parent_id);
    }

    // ─── Update Tests ──────────────────────────────────────────────────

    public function test_update_category_name(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $updated = $this->service->updateCategory($category, ['name' => 'New Name']);

        $this->assertEquals('New Name', $updated->name);
    }

    public function test_update_category_parent_to_valid_parent(): void
    {
        $parent = Category::factory()->create();
        $category = Category::factory()->create();

        $updated = $this->service->updateCategory($category, ['parent_id' => $parent->id]);

        $this->assertEquals($parent->id, $updated->parent_id);
    }

    public function test_update_category_parent_to_invalid_parent_throws_exception(): void
    {
        $category = Category::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->updateCategory($category, ['parent_id' => 9999]);
    }

    public function test_update_category_parent_to_self_throws_circular_exception(): void
    {
        $category = Category::factory()->create();

        $this->expectException(CircularHierarchyException::class);

        $this->service->updateCategory($category, ['parent_id' => $category->id]);
    }

    public function test_update_category_parent_to_child_throws_circular_exception(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->expectException(CircularHierarchyException::class);

        // Trying to make parent a child of its own child
        $this->service->updateCategory($parent, ['parent_id' => $child->id]);
    }

    public function test_update_category_parent_to_grandchild_throws_circular_exception(): void
    {
        $grandparent = Category::factory()->create();
        $parent = Category::factory()->create(['parent_id' => $grandparent->id]);
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->expectException(CircularHierarchyException::class);

        // Trying to make grandparent a child of its own grandchild
        $this->service->updateCategory($grandparent, ['parent_id' => $child->id]);
    }

    public function test_update_category_parent_to_non_descendant_succeeds(): void
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        // B is not a descendant of A, so this should work
        $updated = $this->service->updateCategory($categoryA, ['parent_id' => $categoryB->id]);

        $this->assertEquals($categoryB->id, $updated->parent_id);
    }

    // ─── Delete Tests ──────────────────────────────────────────────────

    public function test_delete_category_soft_deletes(): void
    {
        $category = Category::factory()->create();

        $this->service->deleteCategory($category);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_delete_category_reassigns_children_to_parent(): void
    {
        $grandparent = Category::factory()->create();
        $parent = Category::factory()->create(['parent_id' => $grandparent->id]);
        $child1 = Category::factory()->create(['parent_id' => $parent->id]);
        $child2 = Category::factory()->create(['parent_id' => $parent->id]);

        $this->service->deleteCategory($parent);

        // Children should now point to grandparent
        $this->assertEquals($grandparent->id, $child1->fresh()->parent_id);
        $this->assertEquals($grandparent->id, $child2->fresh()->parent_id);
    }

    public function test_delete_root_category_reassigns_children_to_null(): void
    {
        $root = Category::factory()->create(['parent_id' => null]);
        $child1 = Category::factory()->create(['parent_id' => $root->id]);
        $child2 = Category::factory()->create(['parent_id' => $root->id]);

        $this->service->deleteCategory($root);

        // Children should now be root categories (parent_id = null)
        $this->assertNull($child1->fresh()->parent_id);
        $this->assertNull($child2->fresh()->parent_id);
    }

    public function test_delete_leaf_category_with_no_children(): void
    {
        $leaf = Category::factory()->create();

        $this->service->deleteCategory($leaf);

        $this->assertSoftDeleted('categories', ['id' => $leaf->id]);
    }
}
