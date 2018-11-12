<?php
namespace Exponea\Exponea\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Exponea\Exponea\Models\Tracking;

class ImportOrders extends Action {
  const MAX_REQUEST_SIZE = 49;

  protected $helper;
  protected $resultJsonFactory;
  private $tracker;

  public function __construct(
    Context $context,
    JsonFactory $_resultJsonFactory,
    \Exponea\Exponea\Helpers\Helper $_helper,
    \Exponea\Exponea\Models\Tracking $_tracking
  ) {
    $this->helper = $_helper;
    $this->resultJsonFactory = $_resultJsonFactory;
    $this->tracker = $_tracking;
    parent::__construct($context);
  }

  public function getLatestOrders() {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $orderCollection = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Collection');
    $orderCollection->addFieldToSelect(array('*'));
    return $orderCollection; 
  }

  public function getAllOrders_formated($orders){
    $allOrders = array();
    foreach ($orders as &$order) {
      array_push(
        $allOrders, 
        $this->helper->batchEncapsule(
          $this->helper->customerEventEncapsule(
            $this->helper->getOrderData($order),
            $this->helper::PURCHASE
          ),
          'customers/events'
        )
      );
    }
    return $allOrders;
  }

  public function execute() {
    $result = $this->resultJsonFactory->create();

    $enabled = $this->helper->getEnable();
    if (!$enabled) {
        return $result->setData(['success' => false, 'error' => 'Exponea is not enabled.']);
    }
    $privateToken = $this->helper->getPrivateToken();
    if (!$privateToken) {
        return $result->setData(['success' => false, 'error' => 'Private token is not configured.']);
    }
    $publicToken = $this->helper->getPublicToken();
    if (!$publicToken) {
        return $result->setData(['success' => false, 'error' => 'Public token is not configured.']);
    }
    $projectToken = $this->helper->getProjectToken();
    if (!$projectToken) {
        return $result->setData(['success' => false, 'error' => 'Project token is not configured.']);
    }
  
    $formatedOrders = $this->getAllOrders_formated(
      $this->getLatestOrders()
    );
    $requestQueue = array();
    foreach ($formatedOrders as &$order) {
      array_push($requestQueue, $order);
      if(count($requestQueue) == self::MAX_REQUEST_SIZE){
        $this->tracker->postBatch(
          ['commands' => $requestQueue]
        );
        $requestQueue = array();
      }
    }
    if(count($requestQueue) > 0){
      $this->tracker->postBatch(
        ['commands' => $requestQueue]
      );
    }

    return $result->setData(['success' => true]);

  }
}

