<?php

namespace IWD\CartToQuote\Block\Adminhtml\Quotes;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Backend\Helper\Data;

/**
 * Class Edit
 * @package IWD\SalesRep\Block\Adminhtml\User\Edit\Tab
 */
class Edit extends Extended
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * SalesRepCustomers constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        DefaultLocator $currencyLocator,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->currencyLocator = $currencyLocator;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $quote = $this->registry->registry('requested_quote');
        $quoteCollection = $quote->getItemsCollection()
            ->addFieldToFilter('parent_item_id', array('null' => true));

        $this->setCollection($quoteCollection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $currencyCode = $this->currencyLocator->getDefaultCurrency($this->_request);

        $this->addColumn(
            'item_id',
            [
                'header' => __('ID'),
                'type' => 'text',
                'name' => 'is_assigned',
                'align' => 'center',
                'index' => 'item_id',
                'filter' => false,
                'header_css_class' => 'col-select data-grid-multicheck-cell',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Product Name'),
                'index' => 'name',
                'filter' => false,
                'header_css_class' => 'iwd-c2q-col-name',
                'column_css_class' => 'iwd-c2q-col-name'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'filter' => false,
                'header_css_class' => 'iwd-c2q-col-attr-sku',
                'column_css_class' => 'iwd-c2q-col-attr-sku'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Original Price'),
                'index' => 'price',
                'type' => 'price',
                'filter' => false,
                'currency_code' => $currencyCode,
                'renderer' => '\Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price',
                'header_css_class' => 'iwd-c2q-col-attr-price',
                'column_css_class' => 'iwd-c2q-col-attr-price'
            ]
        );
        $this->addColumn(
            'custom_price',
            [
                'header' => __('Custom Price'),
                'type' => 'price',
                'sortable' => false,
                'filter' => false,
                'renderer'  => 'IWD\CartToQuote\Block\Adminhtml\Quotes\Renderer\CustomPrice',
                'header_css_class' => 'iwd-c2q-col-attr-customprice',
                'column_css_class' => 'iwd-c2q-col-attr-customprice'
            ]
        );
        $this->addColumn(
            'qty',
            [
                'header' => __('QTY'),
                'index' => 'qty',
                'type' => 'int',
                'sortable' => false,
                'filter' => false,
                'renderer'  => 'IWD\CartToQuote\Block\Adminhtml\Quotes\Renderer\CustomQty',
                'header_css_class' => 'iwd-c2q-col-attr-qty',
                'column_css_class' => 'iwd-c2q-col-attr-qty'
            ]
        );
        $this->addColumn(
            'row_total',
            [
                'header' => __('Total Price'),
                'index' => 'row_total',
                'type' => 'price',
                'filter' => false,
                'currency_code' => $currencyCode,
                'renderer' => '\Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price',
                'header_css_class' => 'iwd-c2q-col-attr-qty',
                'column_css_class' => 'iwd-c2q-col-attr-qty'
            ]
        );

        return parent::_prepareColumns();
    }
}
