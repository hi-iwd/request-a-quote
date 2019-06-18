<?php

namespace IWD\CartToQuote\Plugin\Model;

/**
 * Class Agreement
 * @package IWD\CartToQuote\Plugin\Model
 */
class Agreement
{
    /**
     * @var \IWD\CartToQuote\Plugin\Config\Save
     */
    private $saveConfig;

    public function __construct(
        \IWD\CartToQuote\Plugin\Config\Save $saveConfig
    ) {
        $this->saveConfig = $saveConfig;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterSave($subject, $result)
    {
        $this->saveConfig->updateStoreAttributes();
        return $result;
    }
}
