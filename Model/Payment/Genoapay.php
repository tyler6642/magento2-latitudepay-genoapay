<?php
namespace Latitude\Payment\Model\Payment;

use \Latitude\Payment\Helper\Config;
/**
 * Class Genoapay
 * @package Latitude\Payment\Model\Payment
 */
class Genoapay extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'genoapay';

    protected $_supportedCurrencyCodes = array('NZD');

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    protected $curllHelper;

    protected $storeManager;

    protected $_canRefund = true;

    protected $_canRefundInvoicePartial = true;

    const PAYMENT_AUTHRIZATION_TOKEN    = 'latitude_atuthorization_token';

    const PAYMENT_PURCHASE_TOKEN        = 'latitude_purchase_token';
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \Latitude\Payment\Helper\Curl $curlHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Latitude\Payment\Helper\Curl $curlHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlBuilder  = $urlBuilder;
        $this->curllHelper  = $curlHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canUseForCurrency($currencyCode)
    {
        $paymentCurrency =$this->getConfigData('currency');
        $currency = (isset($paymentCurrency) ? $paymentCurrency : $currencyCode);
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        if ($currentCurrencyCode == $currency) {
            return true;
        }
        return false;
    }
    /**
     * Checkout redirect URL getter for onepage checkout
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->urlBuilder->getUrl('latitude/latitude/start');
    }

    /**
     * Refund a capture transaction
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this;
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

        $purchaseToken = $payment->getParentTransactionId();

        if ($purchaseToken) {
            $order = $payment->getOrder();
            $refundDetails =  array(
                                  "amount" =>  $amount,
                                  "currency" => $order->getOrderCurrencyCode()
                               );
            $requestbody = [];
            $requestbody['amount']    = $refundDetails;
            $requestbody['reason']    = 'size not correct';
            $requestbody['reference'] = $order->getIncrementId();
            $storeId    = $order->getStore()->getId();
            $methodCode = $order->getPayment()->getMethod();
            $this->curllHelper->createRefund($requestbody, $purchaseToken ,$storeId,$methodCode);
            //  $this->_importRefundResultToPayment($api, $payment, $canRefundMore);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t issue a refund transaction because there is no capture transaction.')
            );
        }
        return $this;
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)(int)$this->_scopeConfig->getValue(
                Config::GENOAPAY_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            ) && (bool)(int)$this->getConfigData('active', $storeId);
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        //@TODO:
        //1. check if API crediential coorect.
        //2. check currency is correct again
        //3. check quote amount is correct.
        return $this->isActive();
    }

    /**
     * To check if the payment method is available for the default country
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        return 'NZ' === $country ? true : false;
    }



}


