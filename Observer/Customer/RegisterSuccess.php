<?php


namespace Exponea\Exponea\Observer\Customer;
use Magento\Framework\Event\ObserverInterface;
use Exponea\Exponea\Models\Tracking;

class RegisterSuccess implements ObserverInterface
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
    $customer = $observer->getEvent()->getCustomer();
    $this->tracker->postRegistrationInfo(
      $this->helper->customerEncapsule(
        $this->helper->getCustomerData(
          $customer
        )
      )
    );
  }
}