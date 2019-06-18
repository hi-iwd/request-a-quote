<?php

namespace IWD\CartToQuote\Controller\Sales\Guest;

/**
 * Class Reorder
 * @package IWD\CartToQuote\Controller\Sales\Guest
 */
class Reorder extends \Magento\Sales\Controller\Guest\Reorder
{
    use \IWD\CartToQuote\Traits\ReorderTrait;
}