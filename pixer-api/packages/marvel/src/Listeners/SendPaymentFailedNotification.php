<?php

namespace Marvel\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Events\PaymentFailed;
use Marvel\Notifications\PaymentFailedNotification;


class SendPaymentFailedNotification implements ShouldQueue
{

    /**
     * Handle the event.
     *
     * @param PaymentFailed $event
     * @return void
     */
    public function handle(PaymentFailed $event)
    {
        foreach ($event->order->children as $key => $child_order) {
            $vendor_id = $child_order->shop->owner_id;
            $vendor = User::findOrFail($vendor_id);
            $vendor->notify(new PaymentFailedNotification($event->order));
        }

        $customer = $event->order->customer;
        if (isset($customer)) {
            $customer->notify(new PaymentFailedNotification($event->order));
        }
    }
}
