<?php

namespace App\Listeners;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Marvel\Database\Models\User;
use Marvel\Enums\Permission;
use Marvel\Events\StoreNoticeEvent;
use Marvel\Notifications\StoreNoticeNotification;

class StoreNoticeListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param StoreNoticeEvent $event
     * @return void
     */
    public function handle(StoreNoticeEvent $event)
    {
        $users = User::whereHas('permissions', function (Builder $query) {
            $query->whereIn('name', [Permission::SUPER_ADMIN]);
        })->get();

        if (!empty($users)) {
            foreach ($users as $user) {
                $user->notify(new StoreNoticeNotification($event->storeNotice, $event->action));
            }
        }
    }
}
