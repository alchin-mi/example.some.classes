<?php

namespace Compo\Observer\Listeners\Order;

use Compo\Observer\Notifications\Order\OrderRatedMailNotification;
use League\Event\AbstractListener;
use League\Event\EventInterface;

class OrderRatedListener extends AbstractListener
{

    public function handle(EventInterface $event)
    {
        new OrderRatedMailNotification($event);
    }
}
