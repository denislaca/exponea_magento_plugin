<?php


namespace Exponea\Exponea\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Exponea\Exponea\Models\Tracking;

class ImportCatalog extends Action {
  const MAX_REQUEST_SIZE = 49;

  protected $helper;
  protected $resultJsonFactory;
  private $tracker;
  protected $productCollectionFactory;

  public function __construct(
    Context $context,
    JsonFactory $_resultJsonFactory,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $_productCollectionFactory,
    \Exponea\Exponea\Helpers\Helper $_helper,
    \Exponea\Exponea\Models\Tracking $_tracking
  ) {
    $this->helper = $_helper;
    $this->resultJsonFactory = $_resultJsonFactory;
    $this->tracker = $_tracking;
    $this->productCollectionFactory = $_productCollectionFactory;
    parent::__construct($context);
  }

  public function getProducts() {
    $products = array();
    $collection = $this->productCollectionFactory->create()->addAttributeToSelect('*')->load();
    foreach ($collection as &$product) {
      array_push($products, $product->getData());
    }

    return $products;
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

    $catalogName = $this->helper->getCatalogName();
    if (!$catalogName) {
      return $result->setData(['success' => false, 'error' => 'Catalog name is not configured.']);
    }
    $catalogId = $this->tracking->postCreateCatalog( [ 'name' => $catalogName ] );


    $products = $this->getProducts();
    $requestQueue = array();
    foreach ($products as &$product) {
      array_push($requestQueue, $product);
      if (count($requestQueue) == self::MAX_REQUEST_SIZE) {
        // $this->tracker->postBatch(
          // ['commands' => $requestQueue]
        // );
        $requestQueue = array();
      }
    }
    if (count($requestQueue) > 0){
      // $this->tracker->postBatch(
        // ['commands' => $requestQueue]
      // );
    }


    return $result->setData(['success' => true]);
  }
}