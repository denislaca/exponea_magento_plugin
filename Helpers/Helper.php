<?php
namespace Exponea\Exponea\Helpers; 

class Helper extends \Magento\Framework\App\Helper\AbstractHelper { 
  protected $scopeConfig; 
  protected $cookieManager;
  protected $customerSession;
  protected $customer;
  protected $coupon;

  const ENABLE = 'exponea_exponea/general/enable'; 
  const PUBLIC_TOKEN = 'exponea_exponea/general/public_token'; 
  const PRIVATE_TOKEN = 'exponea_exponea/general/private_token'; 
  const PROJECT_TOKEN = 'exponea_exponea/general/project_token';
  const ENABLE_USER_ID = 'exponea_exponea/general/enable_data_id';
  const CATALOG_NAME = 'exponea_exponea/general/catalog_name';
  const COOKIE_NAME = '__exponea_etc__';
  const CART_UPDATE = 'cart_update';
  const PURCHASE = 'purchase';
  const ENDPOINT = 'exponea_exponea/general/endpoint';

  public function __construct(
    \Magento\Framework\App\Helper\Context $context, 
    \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig,
    \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
    \Magento\Customer\Model\Session $_customerSession,
    \Magento\Customer\Model\Customer $_customer,
    \Magento\Checkout\Block\Cart\Coupon $_coupon
  ) { 
    parent::__construct($context); 
    $this->scopeConfig = $_scopeConfig;
    $this->cookieManager = $_cookieManager;
    $this->customerSession = $_customerSession;
    $this->customer = $_customer;
    $this->coupon = $_coupon;
  }

  public function getCookie() {
    return $this->cookieManager->getCookie(self::COOKIE_NAME);
  }

  public function getEnable() {
    return $this->scopeConfig->getValue(self::ENABLE);
  }

  public function getPublicToken() {
    return trim($this->scopeConfig->getValue(self::PUBLIC_TOKEN));
  }

  public function getPrivateToken() {
    return trim($this->scopeConfig->getValue(self::PRIVATE_TOKEN));
  }

  public function getProjectToken() {
    return trim($this->scopeConfig->getValue(self::PROJECT_TOKEN));
  }

  public function getEnableUserId() {
    return $this->scopeConfig->getValue(self::ENABLE_USER_ID);
  }

  public function getEndpoint() {
    return $this->scopeConfig->getValue(self::ENDPOINT);
  }

  public function getCatalogName() {
    return $this->scopeConfig->getValue(self::CATALOG_NAME);
  }

  public function getCustomerByEmail($email) {
    $customer = $this->customer;
    $customer->setWebsiteId(null);
    $customer->loadByEmail($email);

    return $customer->getId() ? $customer : null;
  }

  public function getCustomerById($id) {
    $customer = $this->customer;
    $customer->setWebsiteId(null);
    $customer->load($id);

    return $customer->getId() ? $customer : null;
  }

  public function getCustomerIds($customer) {
    $cookie = $this->getCookie();
    if ((!$customer || !$customer->getEmail()) && !$cookie) {
      return null;
    }
    if (!$customer || !$customer->getEmail()) {
      return [
        'cookie' => $cookie
      ];
    }
    return [
      'registered' => trim($customer->getEmail()),
      'cookie' => $cookie
    ];
  }

  public function getPurchasedItemsData($allItems) {
    $purchasedItems = array();
    $purchasedItemsCodes = array();
    $amountOfPuchasedItems = 0;

    foreach ($allItems as &$item) {
      $amountOfPuchasedItems = $amountOfPuchasedItems + $item->getQtyOrdered();
      array_push($purchasedItems, $item->getData()['product_id']);
      array_push($purchasedItemsCodes, $item->getData()['sku']);
    }
    
    return [
      'total_quantity' => $amountOfPuchasedItems,
      'product_ids' => $purchasedItems,
      'product_codes' => $purchasedItemsCodes
    ];
  }

  public function customerEventEncapsule($data, $type) {
    return [
      'customer_ids' => $data['customer_ids'],
      'event_type' => $type,
      'properties' => $data['properties'],
    ];
  }

  public function customerEncapsule($data) {
    return [
      'customer_ids' => $data['customer_ids'],
      'properties' => $data['properties'],
    ];
  }

  public function batchEncapsule($data, $type) {
    return [
      'name' => $type,
      'data' => $data,
    ];
  }

  public function getCartData($cart, $action) {
    $cartData = $cart->getData();
    return [
      'properties' => [
        'action' => $action,
        'domain' => $cartData['remote_ip'],
        'total_quantity' => $cartData['items_qty'],
        'total_price' => $cartData['grand_total'],
        'total_price_without_tax' => $cartData['base_subtotal'],
        'total_price_local_currency' => $cartData['quote_currency_code']
      ],
      'customer_ids' => $this->getCustomerIds($cart->getCustomer()),
    ];
  }

  public function getProductData($product, $action) {
    $productData = $product->getProduct()->getData();
    return [
      'properties' => [
        'action' => $action,
        'product_id' => $productData['entity_id'],
        'product_title' => trim($productData['name']),
        'total_quantity' => $productData['qty'],
        'total_price' => bcadd($productData['price'], '0', 2),
      ],
      'customer_ids' => $this->getCustomerIds($this->customerSession->getCustomer()),
    ];
  }

  public function getCustomerData($customer) {
    return [
      'properties' => [
        'first_name' => $customer->getFirstname(),
        'last_name' => $customer->getLastname(),
        'email' => $customer->getEmail(),
      ],
      'customer_ids' => $this->getCustomerIds($customer),
    ];
  }

  public function getPaymentData($payment, $invoice) {
    $paymentData = $payment->getData();
    $shippingAddressData = $payment->getShippingAddress()->getData();
    $invoiceData = $invoice->getData();
    $purchasedItems = $this->getPurchasedItemsData($payment->getAllItems());

    return [
      'properties' => [
        'purchase_id' => $payment->getRealOrderId(),
        'purchase_status' => 'success',
        'product_ids' => $purchasedItems['product_ids'],
        'purchase_source_type' => 'store',
        'local_currency' => $paymentData['order_currency_code'],
        'total_price_without_tax' => $paymentData['subtotal'],
        'total_quantity' => $paymentData['total_qty'],
        'shipping_cost' => $paymentData['base_shipping_amount'],
        'shipping_country' => $shippingAddressData['country_id'],
        'shipping_city' => trim($shippingAddressData['city']),
        'delivery_type' => $payment->getShippingMethod(),
        'payment_method' => $invoiceData['method'],
        'tax_percentage' => (($paymentData['subtotal_incl_tax'] / ($paymentData['subtotal'] == 0 ?: 1 )) - 1) < 0 ?: 0,
        'tax_value' => $paymentData['subtotal_incl_tax'] - $paymentData['subtotal'],
      ],
      'customer_ids' => $this->getCustomerIds(
        $this->getCustomerById(
          $paymentData['customer_id']
        )
      ),
    ];
  }

  public function getOrderData($order) {
    $shippingAddressData = $order->getShippingAddress()->getData();
    $orderData = $order->getData();
    $purchasedItems = $this->getPurchasedItemsData($order->getAllItems());
    // TODO:
    // It seems like carrier code is missing from shipping method
    // Delivery Company: order -> getShippingMethod(true) -> carrierCode

    return [
      'properties' => [
        'purchase_id' => $order->getRealOrderId(),
        'purchase_status' => 'order',
        'product_ids' => $purchasedItems['product_ids'],
        'purchase_source_type' => 'store',
        'local_currency' => $orderData['order_currency_code'],
        'total_price_without_tax' => $orderData['subtotal'],
        'total_quantity' => $purchasedItems['total_quantity'],
        'shipping_cost' => $orderData['base_shipping_amount'],
        'shipping_country' => $shippingAddressData['country_id'],
        'shipping_city' => $shippingAddressData['city'],
        'delivery_type' => $order->getShippingMethod(),
        // TODO: currently not working as intended - needs review
        'voucher_code' => $this->coupon->getCouponCode(),
        'voucher_value' => $this->coupon->getAmount(),
        'tax_percentage' => (($order->getSubtotalInclTax() / ($order->getSubtotal() == 0 ?: 1) ) - 1) < 0 ?: 0,
        'tax_value' => $order->getSubtotalInclTax() - $order->getSubtotal(),
      ],
      'customer_ids' => $this->getCustomerIds(
        $this->getCustomerByEmail(
          $orderData['customer_email']
        )
      ),
    ];
  }
}

