<?php


namespace Exponea\Exponea\Observer\Checkout;
use Magento\Framework\Event\ObserverInterface;
use Exponea\Exponea\Models\Tracking;

class CartSaveAfter implements ObserverInterface
{
  const ADD = 'add';
  const UPDATE = 'update';
  const DELETE = 'delete';

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
    $cart = $observer->getEvent()->getCart()->getQuote();
    $this->tracker->postCartUpdateInfo(
      $this->helper->customerEventEncapsule(
        $this->helper->getCartData(
          $cart,
          $cart->getData()['items_count'] > 0 ? self::UPDATE : self::DELETE 
        ),
        $this->helper::CART_UPDATE 
      )
    );
  }
}