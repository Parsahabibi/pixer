<?php


namespace Marvel\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Database\Models\StoreNotice;

class StoreNoticeEvent implements ShouldQueue
{


    /**
     * storeNotice
     *
     * @var StoreNotice
     */
    public $storeNotice;

    /**
     * action
     *
     * @var string
     */
    public $action;

    /**
     * Create a new event instance.
     *
     * @param StoreNotice|array $storeNotice
     * @param null|string $action
     * @return void
     */
    public function __construct(StoreNotice $storeNotice, ?string $action)
    {
        $this->storeNotice = $storeNotice;
        $this->action = $action;
    }
}
