<?php


namespace Exponea\Exponea\Models; 

class Tracking {    
  //127.0.0.1:8080/api-v1
  //https://api.exponea.com/track/v2/projects
  // const BASE_URL = 'https://api.exponea.com/track/v2/projects'; 
  private $projectToken;
  private $privateToken;
  private $publicToken;
  private $base_url;

  protected $helper;

  public function __construct(
    \Exponea\Exponea\Helpers\Helper $_helper
  ) {
    $this->helper = $_helper;
    $this->projectToken = $this->helper->getProjectToken();
    $this->privateToken = $this->helper->getPrivateToken();
    $this->publicToken = $this->helper->getPublicToken();
    $this->base_url = $this->helper->getEndpoint();
  }

  public function checkTokens() {
    return $this->projectToken && $this->privateToken && $this->publicToken && $this->base_url;
  }

  public function postOrderInfo($data) {
    if ((array_key_exists('customer_ids', $data) && !$data['customer_ids']) || !$this->checkTokens()) {
      return;
    }
    $this->_send('customers/events', $data);
  }

  public function postPurchaseInfo($data) {
    if ((array_key_exists('customer_ids', $data) && !$data['customer_ids']) || !$this->checkTokens()) {
      return;
    }
    $this->_send('customers/events', $data);
  }

  public function postCartUpdateInfo($data) {
    if ((array_key_exists('customer_ids', $data) && !$data['customer_ids']) || !$this->checkTokens()) {
      return;
    }
    $this->_send('customers/events', $data);
  }

  public function postRegistrationInfo($data) {
    if ((array_key_exists('customer_ids', $data) && !$data['customer_ids']) || !$this->checkTokens()) {
      return;
    }
    $this->_send('customers', $data);
  }

  public function postBatch($data) {
    $this->_send('batch', $data);
  }

  public function postCatalogItem($data, $catalogId, $itemId) {
    $this->_send('catalogs/'. $catalogId . '/items/' . $itemId);
  }

  public function postCreateCatalog($name) {
    return $this->_send('catalogs', $name);
  }

  private function _send($url, $json) {
    $data_string = json_encode($json);
    $ch = curl_init();   
    curl_setopt($ch, CURLOPT_URL, $this->base_url . '/' . $this->projectToken . '/' . $url);    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
      'Content-Type: application/json', 
      'authorization: Basic ' . base64_encode($this->publicToken  . ':' . $this->privateToken),                                                                    
      'Content-Length: ' . strlen($data_string))                                                                       
    );
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
  }
}

