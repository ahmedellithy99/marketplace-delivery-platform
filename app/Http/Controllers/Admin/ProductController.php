<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductStoreRequest;
use App\Http\Requests\Admin\ProductUpdateRequest;
use App\Jobs\UploadProductImagesJob;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Admin\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
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
        $product = $this->productService->createProduct($request->validated());

        $this->dispatchProductImageUploads($product, $request->file('images', []));

        return redirect()->route('admin.products.index')
            ->with('success', 'تم إنشاء المنتج بنجاح. جاري معالجة الصور في الخلفية.');
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
        $this->productService->updateProduct($product, $request->validated());

        $this->dispatchProductImageUploads($product, $request->file('images', []));

        return redirect()->route('admin.products.index')
            ->with('success', 'تم تحديث المنتج بنجاح. جاري معالجة الصور في الخلفية.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->deleteProduct($product);

        return redirect()->route('admin.products.index')
            ->with('success', 'تم حذف المنتج بنجاح.');
    }

    protected function dispatchProductImageUploads(Product $product, array $images): void
    {
        if (empty($images)) {
            return;
        }

        $tempPaths = [];

        /** @var UploadedFile $image */
        foreach ($images as $image) {
            $tempPaths[] = $image->store('temp/product-images');
        }

        Cache::put(
            "product_images_processing_{$product->id}",
            true,
            now()->addMinutes(10),
        );

        UploadProductImagesJob::dispatch($product->id, $tempPaths);
    }

    public function toggleAvailability(Product $product): RedirectResponse
    {
        $this->productService->toggleAvailability($product);

        return redirect()->back()
            ->with('success', 'تم تحديث حالة التوفر.');
    }

    public function storeVariant(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->productService->addVariant($product, $validated);

        return redirect()->back()
            ->with('success', 'تم إضافة المتغير بنجاح.');
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->productService->updateVariant($variant, $validated);

        return redirect()->back()
            ->with('success', 'تم تحديث المتغير بنجاح.');
    }

    public function destroyVariant(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->productService->removeVariant($variant);

        return redirect()->back()
            ->with('success', 'تم حذف المتغير بنجاح.');
    }

    public function setDefaultVariant(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->productService->setDefaultVariant($product, $variant);

        return redirect()->back()
            ->with('success', 'تم تعيين المتغير الافتراضي.');
    }

    public function storeDiscount(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->productService->addProductDiscount($product, $validated);

        return redirect()->back()
            ->with('success', 'تم إضافة الخصم بنجاح.');
    }

    public function destroyDiscount(Product $product, \App\Models\Discount $discount): RedirectResponse
    {
        $this->productService->removeProductDiscount($product, $discount);

        return redirect()->back()
            ->with('success', 'تم حذف الخصم.');
    }
}
