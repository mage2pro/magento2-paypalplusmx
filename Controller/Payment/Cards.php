<?php
namespace Dfe\PayPalPlusMx\Controller\Payment;

use \Magento\Framework\Json\Helper\Data;

 
class Cards extends \Magento\Framework\App\Action\Action
{
    protected $_resultJsonFactory;
    protected $_resultRawFactory;
    protected $_request;
    protected $_helper;
    protected $_objectManager;
    protected $_encryptor;
    protected $_logger;
    
    const CUSTOMER_INSTANCE_NAME =   'Magento\Customer\Model\Customer';
    const SESSION_INSTANCE_NAME  =   'Magento\Customer\Model\Session';
    /**
     * @param Context $context
     */
    public function __construct(
            \Magento\Framework\App\Action\Context $context, 
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
            \Magento\Framework\Json\Helper\Data $helper,
            \Psr\Log\LoggerInterface $logger

    ){
        $this->_resultJsonFactory = $resultJsonFactory;        
        $this->_resultRawFactory = $resultRawFactory;        
        $this->_helper = $helper;     
        $this->_objectManager = $context->getObjectManager();
        $this->_logger = $logger;
        parent::__construct($context);
    }
    /**
     * Save tokenized cards
     * @param token
     */
    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $resultRaw = $this->_resultRawFactory->create();
        $httpBadRequestCode = '400';
        $httpErrorCode = '500';
        
        try {
            $requestData = $this->_helper->jsonDecode($this->getRequest()->getContent());
        } catch (\Exception $e) {
            $resultRaw->setData($e->getMessage());
            return $resultRaw->setHttpResponseCode($httpErrorCode);
        }
        if ($this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        
        $tokenId = $requestData['token_id'];
        
        if(!$tokenId || empty($tokenId)){
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        try{
            $customer = $this->_objectManager->create(self::CUSTOMER_INSTANCE_NAME);
            $customerSession = $this->_objectManager->create(self::SESSION_INSTANCE_NAME);
            if($customerSession->isLoggedIn()){
                $customerId = $customerSession->getCustomerId();
                $customer->load($customerId);
                $customer->setCardTokenId($tokenId);
                $customer->save();
            }
        } catch (Exception $e) {
            $resultRaw->setData($e->getMessage());
            return $resultRaw->setHttpResponseCode($httpErrorCode);
        }
        
        $response = json_encode(['success' => true]);
        return $resultJson->setData($response);
    }
}