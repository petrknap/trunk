<?php

namespace PetrKnap\Symfony\Order\Service;

use PetrKnap\Symfony\Order\Model\Item;

interface ItemProvider
{
    /**
     * @param mixed $id
     * @return Item
     */
    public function provideItem($id);
}
