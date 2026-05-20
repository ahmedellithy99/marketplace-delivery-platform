<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Delivery;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ─── Admin & Super Admin (fixed credentials) ───────────────────────
        $this->call(AdminSeeder::class);

        // ─── Users ─────────────────────────────────────────────────────────

        $superAdmin = User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'phone' => '0900000001',
            'email' => 'superadmin@marketplace.test',
        ]);

        $admins = User::factory()->admin()->count(2)->create();

        $customers = User::factory()->customer()->count(5)->create();

        $deliveryMen = User::factory()->delivery()->count(3)->create();

        // ─── Store Types ───────────────────────────────────────────────────

        $storeTypes = collect([
            StoreType::create(['name' => 'Supermarket']),
            StoreType::create(['name' => 'Restaurant']),
            StoreType::create(['name' => 'Cafe']),
            StoreType::create(['name' => 'Pharmacy']),
            StoreType::create(['name' => 'Other']),
        ]);

        // ─── Stores ────────────────────────────────────────────────────────

        $stores = collect([
            Store::factory()->supermarket()->create(['name' => 'Fresh Market']),
            Store::factory()->restaurant()->create(['name' => 'Grill House']),
            Store::factory()->cafe()->create(['name' => 'Bean & Brew']),
            Store::factory()->pharmacy()->create(['name' => 'HealthPlus Pharmacy']),
            Store::factory()->restaurant()->create(['name' => 'Pizza Palace']),
        ]);

        // ─── Categories ───────────────────────────────────────────────────

        $categories = Category::factory()
            ->count(10)
            ->create();

        // Create some child categories
        $categories->take(3)->each(function (Category $category) {
            Category::factory()->child($category)->count(2)->create();
        });

        // ─── Products per Store ────────────────────────────────────────────

        $allCategories = Category::all();

        $stores->each(function (Store $store) use ($allCategories) {
            // Create products for this store across random categories
            $allCategories->random(min(4, $allCategories->count()))->each(function (Category $category) use ($store) {
                $productCount = fake()->numberBetween(2, 4);

                $products = Product::factory()
                    ->count($productCount)
                    ->create([
                        'store_id' => $store->id,
                        'category_id' => $category->id,
                    ]);

                // Give some products a discount (via discounts table)
                $products->random(min(1, $products->count()))->each(function (Product $product) {
                    $discount = \App\Models\Discount::create([
                        'name' => 'خصم ' . $product->name,
                        'type' => fake()->randomElement(['percentage', 'fixed']),
                        'value' => fake()->randomElement([10, 15, 20, 25, 30]),
                        'scope' => 'product',
                        'is_active' => true,
                    ]);
                    $product->discounts()->attach($discount->id);
                });

                // Give some products variants
                $products->random(min(2, $products->count()))->each(function (Product $product) {
                    ProductVariant::factory()
                        ->count(fake()->numberBetween(2, 3))
                        ->create(['product_id' => $product->id]);
                });
            });
        });

        // ─── Carts ─────────────────────────────────────────────────────────

        $customers->take(2)->each(function (User $customer) use ($stores) {
            $cart = Cart::factory()->create(['user_id' => $customer->id]);

            // Add 2-3 items to the cart
            $products = Product::where('is_available', true)
                ->inRandomOrder()
                ->take(fake()->numberBetween(2, 3))
                ->get();

            $products->each(function (Product $product) use ($cart) {
                CartItem::factory()->create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'unit_price' => $product->base_price ?? 20,
                    'total_price' => $product->base_price ?? 20,
                ]);
            });
        });

        // ─── Orders ────────────────────────────────────────────────────────

        // Create some orders for customers
        $customers->each(function (User $customer) use ($stores, $admins, $deliveryMen) {
            // Each customer gets 1-2 orders
            $orderCount = fake()->numberBetween(1, 2);

            for ($i = 0; $i < $orderCount; $i++) {
                $statuses = ['pending', 'accepted', 'preparing', 'onTheWay', 'delivered'];
                $status = fake()->randomElement($statuses);

                $factory = Order::factory();
                if ($status !== 'pending') {
                    $factory = $factory->{$status}();
                }

                $order = $factory->create(['user_id' => $customer->id]);

                // Create 2-4 order items from random stores
                $products = Product::inRandomOrder()->take(fake()->numberBetween(2, 4))->get();

                $products->each(function (Product $product) use ($order) {
                    $quantity = fake()->numberBetween(1, 3);
                    $price = (float) ($product->base_price ?? 20);

                    OrderItem::factory()->create([
                        'order_id' => $order->id,
                        'store_id' => $product->store_id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $price,
                        'discount_amount' => 0,
                        'total' => round($price * $quantity, 2),
                    ]);
                });

                // Assign delivery for non-pending orders
                if (in_array($status, ['accepted', 'preparing', 'onTheWay', 'delivered'])) {
                    $deliveryState = match ($status) {
                        'onTheWay' => 'pickedUp',
                        'delivered' => 'completed',
                        default => null,
                    };

                    $delivery = Delivery::factory()
                        ->when($deliveryState, fn ($factory) => $factory->{$deliveryState}())
                        ->create([
                            'order_id' => $order->id,
                            'delivery_man_id' => $deliveryMen->random()->id,
                            'assigned_by' => $admins->random()->id,
                        ]);
                }
            }
        });

        // ─── Notifications ─────────────────────────────────────────────────

        // Create some notifications for customers
        $customers->each(function (User $customer) {
            Notification::factory()
                ->count(fake()->numberBetween(2, 4))
                ->create(['user_id' => $customer->id]);
        });

        // Create notifications for delivery men
        $deliveryMen->each(function (User $deliveryMan) {
            Notification::factory()
                ->count(fake()->numberBetween(1, 3))
                ->create([
                    'user_id' => $deliveryMan->id,
                    'type' => 'delivery_assigned',
                ]);
        });

        // Create notifications for admins
        $admins->each(function (User $admin) {
            Notification::factory()
                ->count(fake()->numberBetween(2, 5))
                ->create([
                    'user_id' => $admin->id,
                    'type' => 'new_order',
                ]);
        });
    }
}
