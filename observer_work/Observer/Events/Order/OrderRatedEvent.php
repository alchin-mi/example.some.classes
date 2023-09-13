<?php

namespace Compo\Observer\Events\Order;

use League\Event\AbstractEvent;

class OrderRatedEvent extends AbstractEvent
{
    public $id;
    public $code;
    public $rating;
    public $comment;
    public $lastId;
    public $response = [
        'status' => 'success',
        'message' => 'Спасибо за вашу оценку. Мы стараемся быть лучше для вас!',
    ];
}
