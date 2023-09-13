<?php

namespace Compo\Providers;

use Compo\Observer\Events\ErrorMsg\ErrorMsgEvent;
use Compo\Observer\Events\Order\OrderRatedEvent;
use Compo\Observer\Events\Order\OrderStatusChangeEvent;
use Compo\Observer\Events\User\UserMessageEvent;
use Compo\Observer\Events\User\UserRegisteredEvent;
use Compo\Observer\Events\WS\WSAddChatMessageEvent;
use Compo\Observer\Events\WS\WSChangeSpecsStatusEvent;
use Compo\Observer\Events\WS\WSPlannerMessageEvent;
use Compo\Observer\Events\WS\WSSyncOrdersMessageEvent;
use Compo\Observer\Listeners\ErrorMsg\ErrorMsgListener;
use Compo\Observer\Listeners\Order\OrderRatedListener;
use Compo\Observer\Listeners\Order\OrderStatusChangeListener;
use Compo\Observer\Listeners\User\UserMessageListener;
use Compo\Observer\Listeners\User\UserRegisteredListener;
use Compo\Observer\Listeners\WS\WSAddChatMessageListener;
use Compo\Observer\Listeners\WS\WSChangeSpecsStatusListener;
use Compo\Observer\Listeners\WS\WSPlannerMessageListener;
use Compo\Observer\Listeners\WS\WSSyncOrdersMessageListener;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;

class EventServiceProvider implements ListenerProviderInterface
{
    public function provideListeners(ListenerAcceptorInterface $acceptor)
    {
        $acceptor->addListener(OrderStatusChangeEvent::class, new OrderStatusChangeListener);
        $acceptor->addListener(UserMessageEvent::class, new UserMessageListener);
        $acceptor->addListener(ErrorMsgEvent::class, new ErrorMsgListener);
        $acceptor->addListener(UserRegisteredEvent::class, new UserRegisteredListener);
        $acceptor->addListener(WSChangeSpecsStatusEvent::class, new WSChangeSpecsStatusListener);
        $acceptor->addListener(WSAddChatMessageEvent::class, new WSAddChatMessageListener);
        $acceptor->addListener(WSPlannerMessageEvent::class, new WSPlannerMessageListener);
        $acceptor->addListener(WSSyncOrdersMessageEvent::class, new WSSyncOrdersMessageListener);
        $acceptor->addListener(OrderRatedEvent::class, new OrderRatedListener);
    }
}
