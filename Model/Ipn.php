<?php
namespace Dfe\PayPalPlusMx\Model;
use Exception;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Paypal\Model\Info;
class Ipn extends \Magento\Paypal\Model\Ipn
{
    const XML_PATH_METHOD_ACTIVE = 'payment/qbo_paypalplusmx/active';
    
    protected $_logger;
    protected $_ipnRequest;
    protected $_paymentRepository;
    protected $_orderFactory;
    protected $_scopeConfig;
    
    /**
     * Constructo method
     * 
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param Info $paypalInfo
     * @param OrderSender $orderSender
     * @param CreditmemoSender $creditmemoSender
     * @param array $data
     */
    public function __construct(
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order\Payment $paymentRepository,
        Info $paypalInfo,
        OrderSender $orderSender,
        CreditmemoSender $creditmemoSender,
        array $data = []
    ) {
        parent::__construct($configFactory, $logger, $curlFactory, $orderFactory, $paypalInfo, $orderSender, $creditmemoSender);
        $this->_logger = $logger;
        $this->_ipnRequest = $data;
        $this->_paymentRepository = $paymentRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->_orderFactory = $orderFactory;
    }
    /**
     * Load order by invoice ID
     * If not present, its a PayPal Plus payment, 
     * load by TXN ID (PayPal Plus MX does not provide invoice ID to api
     * txn_id is the only way to identify payment)
     *
     * @return \Magento\Sales\Model\Order
     * @throws Exception
     */
    protected function _getOrder()
    {
        $incrementId = $this->getRequestData('invoice');
        if($incrementId){
            $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        } else {
            $orderId = $this->getOrderIdByTxn();
            $this->_order = $this->_orderFactory->create()->load($orderId);
        }

        if (!$this->_order->getId()) {
            throw new Exception(sprintf('Wrong order ID: "%s".', $this->getRequestData('txn_id')));
        }
        return $this->_order;
    }
    /**
     * Get Order ID by txn_id since there is no invoice with PayPal Plus
     * 
     * @return int $orderId
     */
    protected function getOrderIdByTxn()
    {
        $txnId = $this->getRequestData('txn_id');        
        $payment = $this->_paymentRepository->load($txnId, 'last_trans_id');
        
        if(!$payment->getParentId()){
            //If txn_id is not found, might be a child transaction (refund), get parent txn id then
            $txnId = $this->getRequestData('parent_txn_id');
            $payment = $this->_paymentRepository->load($txnId, 'last_trans_id');
        }
        
        return $payment->getParentId();
    }

}
