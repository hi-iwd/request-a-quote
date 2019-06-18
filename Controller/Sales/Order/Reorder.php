<?php

namespace IWD\CartToQuote\Controller\Sales\Order;

/**
 * Class Reorder
 * @package IWD\CartToQuote\Controller\Sales\Order
 */
class Reorder extends \Magento\Sales\Controller\Order\Reorder
{
    use \IWD\CartToQuote\Traits\ReorderTrait;
}