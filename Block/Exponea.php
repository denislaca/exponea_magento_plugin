<?php
namespace Exponea\Exponea\Block;
use \Magento\Framework\View\Element\Template;

class Exponea extends Template
{
  protected $helper;
  protected $customerSession;
    
  public function __construct(
    \Magento\Framework\View\Element\Template\Context $_context,
    \Exponea\Exponea\Helpers\Helper $_helper,
    \Magento\Customer\Model\Session $_customerSession,
    array $data = []
  ) {
    parent::__construct($_context, $data);
    $this->helper = $_helper;
    $this->customerSession = $_customerSession;
  }

  public function getProjectToken() {
    return $this->helper->getProjectToken();
  }

  public function userIdEnabled() {
    return $this->helper->getEnableUserId();
  }

  public function isCustomerLoggedIn() {
    return $this->customerSession->isLoggedIn();
  }

  public function getCustomerId() {
    return $this->customerSession->getCustomer()->getId();
  }

  public function getCustomerEmail() {
    return $this->customerSession->getCustomer()->getEmail();
  }

  public function getCustomerFirstname() {
    return $this->customerSession->getCustomer()->getFirstname();
  }

  public function getCustomerLastname() {
    return $this->customerSession->getCustomer()->getLastname();
  }

  public function getEndpoint() {
    return $this->helper->getEndpoint();
  }

  protected function _toHtml() {
    if ($this->helper->getEnable() && $this->helper->getProjectToken()) {
      return parent::_toHtml();
    }
    return '';
  }
}

