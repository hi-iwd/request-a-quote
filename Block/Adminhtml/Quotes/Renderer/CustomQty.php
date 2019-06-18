<?php

namespace IWD\CartToQuote\Block\Adminhtml\Quotes\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\DataObject;

/**
 * Class CustomQty
 * @package IWD\CartToQuote\Block\Adminhtml\Quotes\Renderer
 */
class CustomQty extends AbstractRenderer
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $quoteItemId = $row->getItemId();
        $quoteItemQty = $row->getData($this->getColumn()->getIndex()) * 1;

        $html = "<input type=\"number\" name=\"custom_quote_params[{$quoteItemId}][qty]\" value=\"{$quoteItemQty}\" min=\"1\" step=\"1\">";

        return $html;
    }
}
