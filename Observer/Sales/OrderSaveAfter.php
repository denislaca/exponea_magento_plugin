<?php


namespace Exponea\Exponea\Observer\Sales;
use Magento\Framework\Event\ObserverInterface;
use Exponea\Exponea\Models\Tracking;

class OrderSaveAfter implements ObserverInterface
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
    $order = $observer->getEvent()->getOrder();
    $this->tracker->postOrderInfo(
      $trackerData = $this->helper->customerEventEncapsule(
        $this->helper->getOrderData($order),
        $this->helper::PURCHASE
      )
    );
  }
}
