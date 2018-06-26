<?php


namespace Exponea\Exponea\Observer\Checkout;
use Magento\Framework\Event\ObserverInterface;
use Exponea\Exponea\Models\Tracking;

class CartAddProductComplete implements ObserverInterface 
{
  protected $connector;
  protected $tracker;
  protected $helper;

  public function __construct(
    \Exponea\Exponea\Helpers\Helper $_helper,
    \Exponea\Exponea\Models\Tracking $_tracking
  ) {
    $this->tracker = $_tracking;
    $this->helper = $_helper;
  }

  public function execute(\Magento\Framework\Event\Observer $observer) {
    $event = $observer->getEvent();
    $this->tracker->postCartUpdateInfo(
      $this->helper->customerEventEncapsule(
        $this->helper->getProductData(
          $event,
          'add'
        ),
        $this->helper::CART_UPDATE
      )
    );
  }
}