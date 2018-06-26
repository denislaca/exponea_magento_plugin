<?php


namespace Exponea\Exponea\Observer\Customer;
use Magento\Framework\Event\ObserverInterface;
use Exponea\Exponea\Models\Tracking;

class SaveAfterDataObject implements \Magento\Framework\Event\ObserverInterface
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
    $customerDataObject = $observer->getEvent()->getData();
    $this->tracker->postRegistrationInfo(
      $this->helper->customerEncapsule(
        $this->helper->getCustomerData(
          $customerDataObject['customer_data_object']
        )
      )
    );
  }

}