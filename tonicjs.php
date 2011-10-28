<?php 
class TonicjsException extends Exception {
  protected $result;

  public function getResult() {
    return $this->result;
  }
  
  public function __toString() {
    $str = $this->getType() . ': ';
    if ($this->code != 0) {
      $str .= $this->code . ': ';
    }
    return $str . $this->message;
  }
  
}

class Tonicjs {
  const VERSION = '0.5.0';
  protected $apiKey;
  protected $apiSecret;
  protected $accessToken = null;

  public static $CURL_OPTS = array(
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_USERAGENT      => "tonicjs-php-{VERSION}",
  );

  public function __construct($config) {
    $this->setApiKey($config['apiKey']);
    $this->setApiSecret($config['secret']);
  }
  
  public function setApiKey($apiKey) {
    $this->apiKey = $apiKey;
    return $this;
  }
  public function getApiKey() {
    return $this->apiKey;
  }
  public function setApiSecret($apiSecret) {
    $this->apiSecret = $apiSecret;
    return $this;
  }
  public function getApiSecret() {
    return $this->apiSecret;
  }

  public function makeRequest($url, $params, $ch = null) {
    $params = array_merge($params, array(
					 'apiKey' => $this->getApiKey(),
					 'apiSecret' => $this->getApiSecret()
					 ));
    
    
    $curlOptions = array();
    $queryString = utf8_encode(http_build_query($params, '', '&'));
    $curlOptions += array(
			  CURLOPT_POST => true,
			  CURLOPT_POSTFIELDS => $queryString,
			  CURLOPT_URL => $url,
			  CURLOPT_PORT => 9292,
			  CURLOPT_USERAGENT => "TONICAPI",
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_RETURNTRANSFER => true
			  );

    $response = $this->doCurlCall($curlOptions);    
    $response = json_decode($response['response']);
    if ($response->status == "success") {
      return $response;
    } else {
      return $response;
    }
  }
  
  public function setTagValue($tonicTagID, $value) {
    $tmp = $this->makeRequest("http://192.168.56.101:9292/api/field/set", array(
		'id' => $tonicTagID, 'value' => $value));
    return $tmp->content;
  }
  
  public function deleteTag($tonicTagID) {
    $tmp = $this->makeRequest("http://192.168.56.101:9292/api/field/delete", array('id' => $tonicTagID));
    return $tmp->content;
  }

  public function getTag($tonicTagID) {
    $tmp = $this->makeRequest("http://192.168.56.101:9292/api/field/get", array('id' => $tonicTagID));
    return $tmp;
  }
  
  public function getTagValue($tonicTagID) {
    $tmp = $this->getTag($tonicTagID);
    return $tmp->content;
  }

  public function getTagJSON($tonicTagID) {
    $tmp = $this->getTag($tonicTagID);
    return json_decode($tmp->content);
  }
  
  public function getTags(array $tagIds) {
    $tmp = $this->makeRequest("http://192.168.56.101:9292/api/fields/get", array('ids' => $tagIds));
    return $tmp;
  }

  protected function doCurlCall(array $curlOptions) {
    $curl = curl_init();
    curl_setopt_array($curl, $curlOptions);
    
    $response = curl_exec($curl);
    $headers = curl_getinfo($curl);
    $errorNumber = curl_errno($curl);
    $errorMessage = curl_error($curl);
    
    curl_close($curl);

    return compact('response', 'headers', 'errorNumber', 'errorMessage');
  }  
}

$tonicjs = new Tonicjs(array(
			     "apiKey" => "a7b5d991-fe9e-4457-855e-c66f5c8bf0ae",
			     "secret" => "ad524e0c26abfda218188b4cf1b67ffba3360045"
			     ));
