<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\ProductUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CartItemStoreRequest;
use App\Http\Requests\Customer\CartItemUpdateRequest;
use App\Models\CartItem;
use App\Services\Customer\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $cart = $this->cartService->getCart($user);

        return Inertia::render('Customer/Cart/Index', [
            'cart' => $cart,
            'cartItems' => $cart->items,
            'totals' => [
                'itemCount' => $cart->items->count(),
                'subtotal' => $cart->items->sum('total_price'),
            ],
        ]);
    }

    public function store(CartItemStoreRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        try {
            $this->cartService->addCartItem(
                $user,
                $request->validated('product_id'),
                $request->validated('variant_id'),
                $request->validated('quantity')
            );
        } catch (ModelNotFoundException) {
            return redirect()->back()->with('error', 'المنتج أو الخيار غير متاح حالياً.');
        } catch (ProductUnavailableException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'تمت إضافة المنتج إلى السلة.');
    }

    public function update(CartItemUpdateRequest $request, CartItem $cartItem): RedirectResponse
    {
        // Verify ownership (eager-load to avoid extra query on repeated access)
        $cartItem->loadMissing('cart');
        abort_unless((int) $cartItem->cart->user_id === (int) $request->user()->id, 403);

        try {
            $this->cartService->updateCartItem(
                $cartItem,
                $request->validated('quantity')
            );
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'تم تحديث الكمية.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        // Verify ownership
        $cartItem->loadMissing('cart');
        abort_unless((int) $cartItem->cart->user_id === (int) $request->user()->id, 403);

        $this->cartService->removeCartItem($cartItem);

        return redirect()->back()
            ->with('success', 'تم حذف المنتج من السلة.');
    }

    public function clear(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $cart = $this->cartService->getOrCreateCart($user);
        $this->cartService->clearCart($cart);

        return redirect()->back()
            ->with('success', 'تم تفريغ السلة.');
    }
}
