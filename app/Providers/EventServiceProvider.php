<?php

namespace App\Providers;

use App\Events\DeliveryAssigned;
use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Listeners\NotifyAdminOrderPlaced;
use App\Listeners\NotifyCustomerOrderStatusChanged;
use App\Listeners\NotifyDeliveryManAssigned;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderPlaced::class => [
            NotifyAdminOrderPlaced::class,
        ],
        OrderStatusChanged::class => [
            NotifyCustomerOrderStatusChanged::class,
        ],
        DeliveryAssigned::class => [
            NotifyDeliveryManAssigned::class,
        ],
    ];
}
