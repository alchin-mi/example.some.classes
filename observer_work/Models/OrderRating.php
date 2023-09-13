<?php

namespace Compo\Models;

use Compo\Core\Models;

class OrderRating extends Models
{
    const TABLE = 'order_rating';

    public function addReview($rating, $comment)
    {
        return $this->connector->sql->insert("INSERT INTO `".self::TABLE."` (`rating`, `comment`) VALUES ($rating, '$comment');");
    }
}
