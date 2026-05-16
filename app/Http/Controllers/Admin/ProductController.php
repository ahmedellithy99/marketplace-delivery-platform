<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductStoreRequest;
use App\Http\Requests\Admin\ProductUpdateRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Admin\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Products/Index', [
            'products' => $this->productService->getProducts($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'stores' => $this->productService->getStoreOptions(),
            'categories' => $this->productService->getCategoryOptions(),
        ]);
    }

    public function store(ProductStoreRequest $request): RedirectResponse
    {
        $this->productService->createProduct(
            $request->validated(),
            $request->file('images', [])
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $this->productService->getProduct($product),
            'stores' => $this->productService->getStoreOptions(),
            'categories' => $this->productService->getCategoryOptions(),
        ]);
    }

    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        $this->productService->updateProduct(
            $product,
            $request->validated(),
            $request->file('images', [])
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->deleteProduct($product);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function toggleAvailability(Product $product): RedirectResponse
    {
        $this->productService->toggleAvailability($product);

        return redirect()->back()
            ->with('success', 'Product availability updated.');
    }

    public function storeVariant(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->productService->addVariant($product, $validated);

        return redirect()->back()
            ->with('success', 'Variant added successfully.');
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->productService->updateVariant($variant, $validated);

        return redirect()->back()
            ->with('success', 'Variant updated successfully.');
    }

    public function destroyVariant(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->productService->removeVariant($variant);

        return redirect()->back()
            ->with('success', 'Variant removed successfully.');
    }
}
