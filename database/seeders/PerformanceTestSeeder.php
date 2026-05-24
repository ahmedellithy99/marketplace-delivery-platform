<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreType;
use App\Models\User;
use App\Models\Notification;
use App\Models\Delivery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Performance Test Seeder
 *
 * Generates a large dataset to stress-test the application:
 * - 50 stores with logo + cover images
 * - 2,000 products with 1-3 images each
 * - 500 customers with orders
 * - 10,000+ notifications
 *
 * Usage:
 *   php artisan migrate:fresh
 *   php artisan db:seed --class=PerformanceTestSeeder
 *
 * To also generate large placeholder images (simulating real uploads):
 *   php artisan db:seed --class=PerformanceTestSeeder
 *
 * Estimated time: 3-8 minutes depending on hardware.
 */
class PerformanceTestSeeder extends Seeder
{
    // Configuration — adjust these numbers as needed
    private int $storeCount = 50;
    private int $productsPerStore = 20; // 50 stores × 20 = 1,000 products
    private int $customerCount = 500;
    private int $ordersPerCustomer = 3;
    private int $deliveryMenCount = 20;
    private int $categoriesCount = 25;
    private int $discountPercentage = 30; // % of products with discounts

    public function run(): void
    {
        $this->command->info('🚀 Performance Test Seeder — Generating large dataset...');
        $this->command->newLine();

        // Disable model events for speed
        $startTime = microtime(true);

        // ─── 1. Users ──────────────────────────────────────────────────────
        $this->command->info('👤 Creating users...');

        $admin = User::factory()->admin()->create([
            'name' => 'مدير النظام',
            'phone' => '0100000001',
            'email' => 'admin@marketplace.test',
            'password' => bcrypt('password'),
        ]);

        $customers = User::factory()->customer()->count($this->customerCount)->create();
        $deliveryMen = User::factory()->delivery()->count($this->deliveryMenCount)->create();

        $this->command->info("   ✓ {$this->customerCount} customers, {$this->deliveryMenCount} delivery men, 1 admin");

        // ─── 2. Store Types ────────────────────────────────────────────────
        $storeTypes = collect([
            StoreType::firstOrCreate(['name' => 'سوبرماركت']),
            StoreType::firstOrCreate(['name' => 'مطعم']),
            StoreType::firstOrCreate(['name' => 'كافيه']),
            StoreType::firstOrCreate(['name' => 'صيدلية']),
            StoreType::firstOrCreate(['name' => 'ملابس']),
            StoreType::firstOrCreate(['name' => 'إلكترونيات']),
            StoreType::firstOrCreate(['name' => 'حلويات']),
        ]);

        // ─── 3. Categories ─────────────────────────────────────────────────
        $this->command->info('📂 Creating categories...');

        $categoryNames = [
            'خضروات وفواكه', 'لحوم ودواجن', 'ألبان وأجبان', 'مشروبات', 'مخبوزات',
            'حلويات', 'معلبات', 'منظفات', 'عناية شخصية', 'أدوية',
            'وجبات رئيسية', 'مقبلات', 'سلطات', 'عصائر طازجة', 'قهوة',
            'شاي', 'حلويات شرقية', 'آيس كريم', 'سناكس', 'زيوت وتوابل',
            'أرز ومكرونة', 'بقوليات', 'أسماك', 'مجمدات', 'أطفال',
        ];

        $categories = collect();
        foreach (array_slice($categoryNames, 0, $this->categoriesCount) as $name) {
            $categories->push(Category::factory()->create(['name' => $name]));
        }

        $this->command->info("   ✓ {$categories->count()} categories");

        // ─── 4. Stores with Images ─────────────────────────────────────────
        $this->command->info("🏪 Creating {$this->storeCount} stores with images...");

        $stores = collect();
        $storeBar = $this->command->getOutput()->createProgressBar($this->storeCount);
        $storeBar->start();

        for ($i = 0; $i < $this->storeCount; $i++) {
            $store = Store::factory()->create([
                'store_type_id' => $storeTypes->random()->id,
            ]);

            // Add logo and cover images from placeholder service
            $this->addStoreImages($store);

            $stores->push($store);
            $storeBar->advance();
        }

        $storeBar->finish();
        $this->command->newLine();
        $this->command->info("   ✓ {$this->storeCount} stores with logo + cover images");

        // ─── 5. Products with Images ───────────────────────────────────────
        $totalProducts = $this->storeCount * $this->productsPerStore;
        $this->command->info("📦 Creating {$totalProducts} products with images...");

        $productBar = $this->command->getOutput()->createProgressBar($totalProducts);
        $productBar->start();

        $productNames = $this->getArabicProductNames();
        $productCount = 0;

        $stores->each(function (Store $store) use ($categories, $productNames, &$productCount, $productBar) {
            // Each store gets products across 3-6 random categories
            $storeCategories = $categories->random(min(6, $categories->count()));

            $productsToCreate = $this->productsPerStore;
            $perCategory = (int) ceil($productsToCreate / $storeCategories->count());

            $storeCategories->each(function (Category $category) use ($store, $productNames, $perCategory, &$productCount, $productBar) {
                for ($i = 0; $i < $perCategory; $i++) {
                    $type = fake()->randomElement(['simple', 'simple', 'simple', 'variant', 'measured']);
                    $name = $productNames[array_rand($productNames)] . ' ' . fake()->numberBetween(1, 999);

                    $productData = [
                        'store_id' => $store->id,
                        'category_id' => $category->id,
                        'name' => $name,
                        'type' => $type,
                        'is_available' => fake()->boolean(90),
                    ];

                    if ($type === 'measured') {
                        $productData['base_price'] = fake()->randomFloat(2, 20, 500);
                        $productData['measurement_unit'] = fake()->randomElement(['kg', 'g', 'liter']);
                        $productData['min_quantity'] = 0.25;
                        $productData['max_quantity'] = 10;
                        $productData['quantity_step'] = 0.25;
                    } elseif ($type === 'simple') {
                        $productData['base_price'] = fake()->randomFloat(2, 5, 300);
                    } else {
                        $productData['base_price'] = null;
                    }

                    $product = Product::create($productData);

                    // Add 1-3 images
                    $this->addProductImages($product, fake()->numberBetween(1, 3));

                    // Add variants for variant-type products
                    if ($type === 'variant') {
                        $variantCount = fake()->numberBetween(2, 5);
                        for ($v = 0; $v < $variantCount; $v++) {
                            ProductVariant::create([
                                'product_id' => $product->id,
                                'name' => fake()->randomElement(['صغير', 'وسط', 'كبير', 'عائلي', 'جامبو']) . ' ' . ($v + 1),
                                'price' => fake()->randomFloat(2, 10, 400),
                                'is_default' => $v === 0,
                                'sort_order' => $v,
                            ]);
                        }
                    }

                    // Random discount
                    if (fake()->boolean($this->discountPercentage)) {
                        $discount = Discount::create([
                            'name' => 'خصم ' . fake()->numberBetween(5, 50) . '%',
                            'type' => fake()->randomElement(['percentage', 'fixed']),
                            'value' => fake()->randomElement([5, 10, 15, 20, 25, 30]),
                            'scope' => 'product',
                            'is_active' => true,
                            'starts_at' => now()->subDays(5),
                            'ends_at' => now()->addDays(30),
                        ]);
                        $product->discounts()->attach($discount->id);
                    }

                    $productCount++;
                    $productBar->advance();
                }
            });
        });

        $productBar->finish();
        $this->command->newLine();
        $this->command->info("   ✓ {$productCount} products with images, variants, and discounts");

        // ─── 6. Orders ─────────────────────────────────────────────────────
        $totalOrders = $this->customerCount * $this->ordersPerCustomer;
        $this->command->info("🧾 Creating {$totalOrders} orders...");

        $allProducts = Product::where('is_available', true)->pluck('id', 'store_id')->toArray();
        $productIds = Product::where('is_available', true)->get(['id', 'store_id', 'base_price', 'name']);

        $orderBar = $this->command->getOutput()->createProgressBar($totalOrders);
        $orderBar->start();

        // Batch insert orders for speed
        $customers->each(function (User $customer) use ($productIds, $deliveryMen, $admin, $orderBar) {
            for ($o = 0; $o < $this->ordersPerCustomer; $o++) {
                $status = fake()->randomElement(['pending', 'accepted', 'preparing', 'onTheWay', 'delivered', 'delivered', 'delivered']);

                $factoryState = match ($status) {
                    'accepted' => 'accepted',
                    'preparing' => 'preparing',
                    'onTheWay' => 'onTheWay',
                    'delivered' => 'delivered',
                    default => null,
                };

                $factory = Order::factory();
                if ($factoryState) {
                    $factory = $factory->{$factoryState}();
                }

                $order = $factory->create([
                    'user_id' => $customer->id,
                ]);

                // 2-6 items per order
                $orderProducts = $productIds->random(min(fake()->numberBetween(2, 6), $productIds->count()));
                $orderProducts->each(function ($product) use ($order) {
                    $qty = fake()->numberBetween(1, 4);
                    $price = (float) ($product->base_price ?? fake()->randomFloat(2, 10, 100));
                    OrderItem::create([
                        'order_id' => $order->id,
                        'store_id' => $product->store_id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'discount_amount' => 0,
                        'total' => round($price * $qty, 2),
                    ]);
                });

                // Assign delivery for non-pending orders
                if ($status !== 'pending') {
                    Delivery::factory()->create([
                        'order_id' => $order->id,
                        'delivery_man_id' => $deliveryMen->random()->id,
                        'assigned_by' => $admin->id,
                        'delivered_at' => $status === 'delivered' ? now()->subHours(fake()->numberBetween(1, 72)) : null,
                    ]);
                }

                $orderBar->advance();
            }
        });

        $orderBar->finish();
        $this->command->newLine();
        $this->command->info("   ✓ {$totalOrders} orders with items and deliveries");

        // ─── 7. Notifications ──────────────────────────────────────────────
        $this->command->info('🔔 Creating notifications...');

        $notifData = [];
        $customers->each(function (User $customer) use (&$notifData) {
            for ($n = 0; $n < fake()->numberBetween(5, 20); $n++) {
                $notifData[] = [
                    'user_id' => $customer->id,
                    'type' => fake()->randomElement(['order_status', 'new_order', 'delivery_assigned']),
                    'title' => fake()->randomElement(['تم قبول طلبك', 'طلبك في الطريق', 'تم التوصيل', 'طلب جديد']),
                    'body' => fake()->sentence(),
                    'link' => '/orders',
                    'is_read' => fake()->boolean(60),
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                    'updated_at' => now(),
                ];
            }
        });

        // Bulk insert notifications
        foreach (array_chunk($notifData, 1000) as $chunk) {
            Notification::insert($chunk);
        }

        $this->command->info("   ✓ " . count($notifData) . " notifications");

        // ─── Summary ───────────────────────────────────────────────────────
        $elapsed = round(microtime(true) - $startTime, 1);
        $this->command->newLine();
        $this->command->info("═══════════════════════════════════════════════");
        $this->command->info("✅ Performance test data seeded in {$elapsed}s");
        $this->command->info("═══════════════════════════════════════════════");
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Stores', $this->storeCount],
                ['Products', $productCount],
                ['Categories', $categories->count()],
                ['Customers', $this->customerCount],
                ['Orders', $totalOrders],
                ['Delivery Men', $this->deliveryMenCount],
                ['Notifications', count($notifData)],
            ]
        );
        $this->command->newLine();
        $this->command->info('🧪 Now test with:');
        $this->command->line('   php artisan serve');
        $this->command->line('   Login: admin@marketplace.test / password');
        $this->command->newLine();
        $this->command->info('📊 Profile queries with:');
        $this->command->line('   php artisan db:seed --class=PerformanceTestSeeder');
        $this->command->line('   Then use Laravel Debugbar or Telescope to monitor.');
    }

    /**
     * Add placeholder images to a store (logo + cover).
     * Uses picsum.photos for realistic large images.
     */
    private function addStoreImages(Store $store): void
    {
        try {
            // Generate a local placeholder image (fast, no network)
            $logoPath = $this->generatePlaceholderImage(200, 200, "S{$store->id}");
            $coverPath = $this->generatePlaceholderImage(1200, 400, "Cover{$store->id}");

            if ($logoPath) {
                $store->addMedia($logoPath)->toMediaCollection('logo');
            }
            if ($coverPath) {
                $store->addMedia($coverPath)->toMediaCollection('cover');
            }
        } catch (\Exception $e) {
            // Skip image errors silently during seeding
        }
    }

    /**
     * Add placeholder images to a product.
     */
    private function addProductImages(Product $product, int $count = 1): void
    {
        try {
            for ($i = 0; $i < $count; $i++) {
                $path = $this->generatePlaceholderImage(
                    800,
                    800,
                    "P{$product->id}-{$i}"
                );
                if ($path) {
                    $product->addMedia($path)->toMediaCollection('images');
                }
            }
        } catch (\Exception $e) {
            // Skip image errors silently during seeding
        }
    }

    /**
     * Generate a local placeholder image (GD library).
     * Creates a colored rectangle with text — simulates a real uploaded image.
     */
    private function generatePlaceholderImage(int $width, int $height, string $label): ?string
    {
        if (!extension_loaded('gd')) {
            // Fallback: create a minimal 1x1 PNG
            return $this->generateMinimalPng($label);
        }

        $image = imagecreatetruecolor($width, $height);

        // Random background color (pastel)
        $r = fake()->numberBetween(150, 240);
        $g = fake()->numberBetween(150, 240);
        $b = fake()->numberBetween(150, 240);
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $bgColor);

        // Add some visual noise (rectangles) to simulate real image weight
        for ($i = 0; $i < 20; $i++) {
            $rectColor = imagecolorallocate(
                $image,
                fake()->numberBetween(100, 255),
                fake()->numberBetween(100, 255),
                fake()->numberBetween(100, 255)
            );
            imagefilledrectangle(
                $image,
                fake()->numberBetween(0, $width),
                fake()->numberBetween(0, $height),
                fake()->numberBetween(0, $width),
                fake()->numberBetween(0, $height),
                $rectColor
            );
        }

        // Add label text
        $textColor = imagecolorallocate($image, 50, 50, 50);
        $fontSize = min($width, $height) > 200 ? 5 : 3;
        $textWidth = imagefontwidth($fontSize) * strlen($label);
        $textX = (int) (($width - $textWidth) / 2);
        $textY = (int) ($height / 2 - imagefontheight($fontSize) / 2);
        imagestring($image, $fontSize, $textX, $textY, $label, $textColor);

        // Save as JPEG (larger file size, simulates real photos)
        $dir = storage_path('app/temp-seed-images');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filename = $dir . '/' . uniqid('img_') . '.jpg';
        imagejpeg($image, $filename, 85); // Quality 85 = realistic file size
        imagedestroy($image);

        return $filename;
    }

    /**
     * Fallback: generate a minimal PNG file when GD is not available.
     */
    private function generateMinimalPng(string $label): ?string
    {
        $dir = storage_path('app/temp-seed-images');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Minimal valid PNG (1x1 pixel, red)
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg=='
        );

        $filename = $dir . '/' . uniqid('img_') . '.png';
        file_put_contents($filename, $png);

        return $filename;
    }

    /**
     * Arabic product names for realistic data.
     */
    private function getArabicProductNames(): array
    {
        return [
            'حليب طازج', 'خبز أبيض', 'خبز بلدي', 'بيض بلدي', 'جبنة بيضاء',
            'زبادي طبيعي', 'عصير برتقال', 'عصير مانجو', 'مياه معدنية', 'كولا',
            'أرز بسمتي', 'مكرونة', 'زيت زيتون', 'زيت ذرة', 'سكر',
            'شاي أخضر', 'قهوة تركي', 'نسكافيه', 'كابتشينو', 'لاتيه',
            'دجاج مشوي', 'كباب', 'شاورما', 'فلافل', 'كشري',
            'بيتزا مارجريتا', 'بيتزا بيبروني', 'برجر لحم', 'برجر دجاج', 'هوت دوج',
            'سلطة خضراء', 'سلطة سيزر', 'فتوش', 'تبولة', 'حمص',
            'كنافة', 'بسبوسة', 'أم علي', 'كيك شوكولاتة', 'تشيز كيك',
            'موز', 'تفاح', 'برتقال', 'فراولة', 'مانجو',
            'طماطم', 'خيار', 'بطاطس', 'بصل', 'ثوم',
            'لحم بقري', 'لحم ضاني', 'كبدة', 'سجق', 'بسطرمة',
            'سمك بلطي', 'جمبري', 'كاليماري', 'سمك مشوي', 'تونة',
            'شامبو', 'صابون', 'معجون أسنان', 'مناديل', 'مزيل عرق',
            'باراسيتامول', 'فيتامين سي', 'مسكن', 'كريم مرطب', 'واقي شمس',
            'آيس كريم فانيليا', 'آيس كريم شوكولاتة', 'آيس كريم فراولة', 'جيلاتي', 'سوربيه',
            'شيبسي', 'بسكويت', 'ويفر', 'شوكولاتة', 'لبان',
        ];
    }
}
