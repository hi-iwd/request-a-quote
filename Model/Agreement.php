<?php

namespace IWD\CartToQuote\Model;

use Magento\CheckoutAgreements\Model\Agreement as CheckoutAgreement;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory as CheckoutAgreementsCollection;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Agreement
 */
class Agreement extends CheckoutAgreement
{
    /**
     * @var CheckoutAgreementsCollection
     */
    private $checkoutAgreementsCollection;

    /**
     * Agreement constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param CheckoutAgreementsCollection $checkoutAgreementsCollection
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        CheckoutAgreementsCollection $checkoutAgreementsCollection,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->checkoutAgreementsCollection = $checkoutAgreementsCollection;
    }

    /**
     * @return string
     */
    public function getCheckoutAgreementsList()
    {
        $agreements = [];
        $checkoutAgreements = $this->checkoutAgreementsCollection->create();

        $checkoutAgreements->getSelect()->join(
            ['checkout_agreement_store' => $checkoutAgreements->getTable('checkout_agreement_store')],
            'main_table.agreement_id = checkout_agreement_store.agreement_id',
            ['stores' => new \Zend_Db_Expr('group_concat(DISTINCT checkout_agreement_store.store_id SEPARATOR ",")')]
        )->group(
            'main_table.agreement_id'
        );

        $checkoutAgreements = $checkoutAgreements->getItems();
        foreach ($checkoutAgreements as $checkoutAgreement) {
            if ($checkoutAgreement->getData('is_active')) {
                $agreements[$checkoutAgreement->getData('agreement_id')] = [
                    'checkbox_text' => $checkoutAgreement->getData('checkbox_text'),
                    'stores' => $checkoutAgreement->getData('stores'),
                    'content' => $checkoutAgreement->getData('content')
                ];
            }
        }

        return json_encode($agreements);
    }
}
