<?php

namespace App\Services\Admin;

use App\Filters\Public\StoreFilter;
use App\Models\Store;
use App\Models\StoreType;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreService
{
    /**
     * Get stores with optional filtering and pagination.
     */
    public function getStores(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return Store::with(['storeType', 'media'])
            ->filter(new StoreFilter($request))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single store with relationships.
     */
    public function getStore(Store $store): Store
    {
        return $store->load(['storeType', 'media', 'products']);
    }

    /**
     * Get active store types for dropdown selection.
     */
    public function getStoreTypeOptions()
    {
        return StoreType::where('is_active', true)->get(['id', 'name']);
    }

    /**
     * Create a new store.
     *
     * @param array $data
     * @param UploadedFile|null $logo
     * @param UploadedFile|null $cover
     * @return Store
     * @throws ValidationException
     */
    public function createStore(array $data, ?UploadedFile $logo = null, ?UploadedFile $cover = null): Store
    {
        $this->validateOperatingHours($data);

        // Remove file fields from data — they're handled via MediaLibrary
        $storeData = collect($data)->except(['logo', 'cover'])->toArray();

        $store = Store::create($storeData);

        if ($logo) {
            $store->addMedia($logo)->toMediaCollection('logo');
        }

        if ($cover) {
            $store->addMedia($cover)->toMediaCollection('cover');
        }

        return $store->load(['storeType', 'media']);
    }

    /**
     * Update an existing store.
     *
     * @param Store $store
     * @param array $data
     * @param UploadedFile|null $logo
     * @param UploadedFile|null $cover
     * @return Store
     * @throws ValidationException
     */
    public function updateStore(Store $store, array $data, ?UploadedFile $logo = null, ?UploadedFile $cover = null): Store
    {
        $this->validateOperatingHours($data, $store);

        // Remove file fields from data — they're handled via MediaLibrary
        $storeData = collect($data)->except(['logo', 'cover'])->toArray();

        $store->update($storeData);

        if ($logo) {
            $store->addMedia($logo)->toMediaCollection('logo');
        }

        if ($cover) {
            $store->addMedia($cover)->toMediaCollection('cover');
        }

        $store->refresh();
        $store->load(['storeType', 'media']);

        return $store;
    }

    /**
     * Soft-delete a store and cascade to all associated products.
     */
    public function deleteStore(Store $store): void
    {
        DB::transaction(function () use ($store) {
            $store->products()->each(function ($product) {
                $product->delete();
            });

            $store->delete();
        });
    }

    /**
     * Validate that opening_time is strictly earlier than closing_time.
     */
    protected function validateOperatingHours(array $data, ?Store $store = null): void
    {
        $openingTime = $data['opening_time'] ?? ($store?->opening_time ? $store->opening_time->format('H:i') : null);
        $closingTime = $data['closing_time'] ?? ($store?->closing_time ? $store->closing_time->format('H:i') : null);

        if ($openingTime === null || $closingTime === null) {
            return;
        }

        $opening = $this->normalizeTime($openingTime);
        $closing = $this->normalizeTime($closingTime);

        if ($opening >= $closing) {
            throw ValidationException::withMessages([
                'opening_time' => ['The opening time must be earlier than the closing time.'],
            ]);
        }
    }

    /**
     * Normalize a time value to H:i string format for comparison.
     */
    protected function normalizeTime(mixed $time): string
    {
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i');
        }

        return date('H:i', strtotime($time));
    }
}
