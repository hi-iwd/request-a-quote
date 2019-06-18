<?php

namespace IWD\CartToQuote\Model\Request;

/**
 * Class Status
 * @package IWD\CartToQuote\Model\Request
 */
class Status
{
    /**
     * Config xPath for save statuses
     */
    const STATUSES_PATH = 'iwd_cart_to_quote/statuses/statuses';

    /**
     * @return array
     */
    public static function getNativeStatuses()
    {
        return array(
            1 => [
                'id' => 1,
                'name' => 'Requested',
                'color' => 'df87e0',
                'code' => 'requested',
                'enable' => 1,
                'native' => 1,
            ],
            2 => [
                'id' => 2,
                'name' => 'Approved',
                'code' => 'approved',
                'color' => '8bc34a',
                'enable' => 1,
                'native' => 1,
            ],
            3 => [
                'id' => 3,
                'name' => 'Rejected',
                'code' => 'rejected',
                'color' => 'fb6a5f',
                'enable' => 1,
                'native' => 1,
            ],
            4 => [
                'id' => 4,
                'name' => 'Closed',
                'code' => 'closed',
                'color' => 'c4c4c4',
                'enable' => 1,
                'native' => 1,
            ],
            5 => [
                'id' => 5,
                'name' => 'Expired',
                'code' => 'expired',
                'color' => '52c5f9',
                'enable' => 1,
                'native' => 1,
            ]
        );
    }
}
