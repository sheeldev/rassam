<?php
class smplxresponse
{

  private $endpoint = '';
  private $rawResponse = '';
  private $data = false;
  private $responseHeaders = array();
  private $originMethod = '';

  public function __construct($responseBody, $respHeaders, $endpoint, $rawResponse = '')
  {
    $this->rawResponse = $rawResponse;
    $this->data = json_decode($responseBody, true);
    $this->endpoint = $endpoint;
    $array = explode("\r\n", $respHeaders);
    foreach ($array as $h) {
      $r = explode(": ", $h);
      if (count($r) > 1) {
        $this->responseHeaders[$r[0]] = $r[1];
      }
    }
  }
  public function getRawResponse()
  {
    return $this->rawResponse;
  }
  public function getEndpoint()
  {
    return $this->endpoint;
  }
  public function isSuccess()
  {
    if (isset($this->data["status"]) && $this->data["status"] == "error") {
      return false;
    } else {
      return true;
    }
  }
  public function isFail()
  {
    return !$this->isSuccess();
  }
  public function getErrorMessage()
  {
    if ($this->isSuccess()) {
      return null;
    }

    return $this->data["message"];
  }
  public function getErrorDetails()
  {
    if ($this->isSuccess()) {
      return null;
    }
    if (isset($this->data["detail"]) && is_array($this->data["detail"])) {
      $error = '';

        if (is_array($this->data["detail"])) {
          $error = $this->data["detail"][0]["msg"];
        }
        else {
          $error = $this->data["detail"];
        }
      
      return $error;
    }
    if (isset($this->data["detail"]) && is_string($this->data["detail"])) {
      return $this->data["detail"];
    }
    return $this->data["detail"];
  }
  public function getData()
  {
    if (!$this->isSuccess()) {
      return false;
    }
    return $this->data["output"];
  }
  public function getHeaders()
  {
    return $this->responseHeaders;
  }
}

class smplx
{
  protected $sheel;
  private $config = array(
    'name' => '',
    'authEndPoint' => '',
    'tokenEndPoint' => '',
    'ApiEndPoint' => '',
    'clientID' => '',
    'clientSecret' => '',
  );


  function __construct($sheel)
  {
    $this->sheel = $sheel;
  }

  public function init_smplx($name)
  {
    $sql = $this->sheel->db->query("
				SELECT id, apigroup, name, value, tokenendpoint, authendpoint, clientid, clientsecret, params, provides
				FROM " . DB_PREFIX . "external_api
				WHERE name = '" . $name . "' AND active ='1'
				LIMIT 1
        ", 0, null, __FILE__, __LINE__);
    if ($this->sheel->db->num_rows($sql) > 0) {
      $this->sheel->db->query("
        UPDATE " . DB_PREFIX . "external_api
        SET hits = hits+1
        WHERE name = '" . $name . "' AND active ='1'
        LIMIT 1
        ", 0, null, __FILE__, __LINE__);
      $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
      $this->config['name'] = $res['name'];
      $this->config['authEndPoint'] = $res['authendpoint'];
      $this->config['tokenEndPoint'] = $res['tokenendpoint'];
      $this->config['clientID'] = $res['clientid'];
      $this->config['clientSecret'] = $res['clientsecret'];
      $this->config['ApiEndPoint'] = $res['value'] .  $res['name'] . '/';
      return true;
    } else {
      return false;
    }
  }
  private function performRequest($endpoint, $method, $payload = false, $customHeaders)
  {
    try {
      $endpoint = str_replace(" ", "%20", $endpoint);
      $endpoint = str_replace("''", "%27", $endpoint);

      if (!preg_match('/^http(s)?\:\/\//', $endpoint)) {
        if (!preg_match('/^\//', $endpoint)) {
        }

        $request = $this->config["ApiEndPoint"] . $endpoint;
      } else {
        $request = $endpoint;
      }
      $curl = curl_init($request);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
      $requestHeaders = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $this->config['clientSecret']
      );
      $requestHeaders[] = "Content-Type: application/json";
      if ($customHeaders && is_array($customHeaders)) {
        foreach ($customHeaders as $customHeader) {
          if (!preg_match('/^Authorization/i', $customHeader)) {
            $requestHeaders[] = $customHeader;
          }
        }
      }
      if (is_array($payload)) {
        $payload = json_encode($payload);
      }
      curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeaders);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curl, CURLOPT_VERBOSE, 0);
      curl_setopt($curl, CURLOPT_HEADER, 1);
      $response = curl_exec($curl);
      $rawResponse = $response;
      // get response headers
      $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
      $responseHeaders = substr($response, 0, $headerSize);
      $responseBody = substr($response, $headerSize);
      $smplxresponse = new smplxresponse($responseBody, $responseHeaders, $endpoint, $rawResponse);
      if ($smplxresponse->isSuccess()) {
        $this->sheel->db->query("
          UPDATE " . DB_PREFIX . "external_api
          SET success = success+1
          WHERE name = '" . $this->config["name"] . "' AND active ='1'
          LIMIT 1
          ", 0, null, __FILE__, __LINE__);
      } else {
        $this->sheel->db->query("
          UPDATE " . DB_PREFIX . "external_api
          SET failed = failed+1
          WHERE name = '" . $this->config["name"] . "' AND active ='1'
          LIMIT 1
          ", 0, null, __FILE__, __LINE__);
      }
      return $smplxresponse;
    } catch (Exception $e) {
      return false;
    }
  }
  public function get($payload, $extraHeaders = false)
  {
    return $this->performRequest($this->config['ApiEndPoint'], 'POST', $payload, $extraHeaders);
  }
}
?>