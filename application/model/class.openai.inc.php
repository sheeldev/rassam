<?php
require_once DIR_CLASSES . '/vendor/jsonschema/autoload.php';
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
class openairesponse
{

  private $schema = '';
  private $rawResponse = '';
  private $data = [];
  private $errors = [];
  private $responseHeaders = [];
  private $is_success = false;

  public function __construct($responseBody, $respHeaders, $schema, $rawResponse = '')
  {
    $this->rawResponse = $rawResponse;
    $rawdata = json_decode($responseBody, true);
    $this->schema = $schema;
    $array = explode("\r\n", $respHeaders);
    foreach ($array as $h) {
      $r = explode(": ", $h);
      if (count($r) > 1) {
        $this->responseHeaders[$r[0]] = $r[1];
      }
    }
    if (isset($rawdata["error"])) {
      $this->is_success = false;
      $this->errors = $rawdata;
    } else {
      if ($this->validateSchema(json_decode($rawdata["choices"][0]["message"]["content"], true))) {
        $this->data = json_decode($rawdata["choices"][0]["message"]["content"], true);
        $this->is_success = true;
      } else {
        $this->data = [];
        $this->is_success = false;
      }
    }
  }
  public function getRawResponse()
  {
    return $this->rawResponse;
  }
  public function isSuccess()
  {
    return $this->is_success;
  }
  public function isFail()
  {
    return !$this->is_success;
  }
  public function getErrors()
  {
    if ($this->isSuccess()) {
      return null;
    }
    return $this->errors;
  }
  public function getData()
  {
    if (!$this->isSuccess()) {
      return [];
    }
    return $this->data;
  }
  public function getRecordCount()
  {
    if (!$this->isSuccess()) {
      return 0;
    }
    return count($this->data);
  }
  public function getHeaders()
  {
    return $this->responseHeaders;
  }
  function validateSchema($data)
  {
    $schema = json_decode($this->schema);
    $validator = new Validator();
    $validator->validate($data, $schema, Constraint::CHECK_MODE_COERCE_TYPES | Constraint::CHECK_MODE_TYPE_CAST);
    if ($validator->isValid()) {
      return true;
    } else {
      $errors = [];
      foreach ($validator->getErrors() as $error) {
        $errors[] = [
            'type' => $error['property'],
            'message' => $error['message']
        ];
        $this->errors = $errors;
    }
      return false;
    }
  }
}
class openai
{
  private $prompt = null;
  protected $sheel;
  private $config = array(
    'name' => '',
    'url' => '',
    'key' => '',
    'prompt_text' => '',
    'prompt_parameters' => '',
    'prompt_context' => '',
    'response_schema' => '',
    'adminonly' => '',
    'type' => '',
    'group' => ''
  );


  function __construct($sheel)
  {
    $this->sheel = $sheel;
  }

  public function init_prompt($name, $user)
  {
    $sql = $this->sheel->db->query("
				SELECT id, varname, description, prompt_text, prompt_parameters, prompt_context, response_schema, adminonly, type, `group`
				FROM " . DB_PREFIX . "prompts
        WHERE varname = '" . $name . "'
				LIMIT 1
        ", 0, null, __FILE__, __LINE__);
    if ($this->sheel->db->num_rows($sql) > 0) {
      $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
      $this->config['name'] = $res['varname'];
      if ($res['type'] == 'chat') {
        $this->config['url'] = $this->sheel->config['openaichaturl'];
      } else if ($res['type'] == 'images') {
        $this->config['url'] = $this->sheel->config['openaiimageurl'];
      } else {
        $this->config['url'] = $this->sheel->config['openaichaturl'];
      }
      $this->config['key'] = $this->sheel->config['openaikey'];
      $this->config['prompt_text'] = $res['prompt_text'];
      $this->config['prompt_parameters'] = $res['prompt_parameters'];
      $this->config['prompt_context'] = $res['prompt_context'];
      $this->config['response_schema'] = $res['response_schema'];
      $this->config['adminonly'] = $res['adminonly'];
      $this->config['type'] = $res['type'];
      $this->config['group'] = $res['group'];
      if ($this->config['adminonly'] == 1) {
        $sqluser = $this->sheel->db->query("
                SELECT u.user_id, r.roleusertype
                FROM " . DB_PREFIX . "users u
                LEFT JOIN " . DB_PREFIX . "roles r ON (u.roleid = r.roleid)
                WHERE u.user_id = '" . $user . "' AND r.roleusertype = 'admin'
                LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        if (!$this->sheel->db->num_rows($sqluser) > 0) {
          return '{_prompt_adminonly}';
        }
      }
      return true;
    } else {
      return '{_prompt_notfound}';
    }
  }
  public function set($toconvert = []) {
    if (isset($toconvert) and is_array($toconvert)) {
      foreach ($toconvert as $search => $replace) {
        if (!empty($search)) {
          $this->config['prompt_text'] = str_replace("$search", $replace, $this->config['prompt_text']);
        }
      }
      $this->config['prompt_text'] = strip_tags($this->config['prompt_text']);
    }
  }
  public function chat()
  {
    try {
      $endpoint = $this->config['url'];
      $endpoint = str_replace(" ", "%20", $endpoint);
      $endpoint = str_replace("''", "%27", $endpoint);


      $curl = curl_init($endpoint);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
      $requestHeaders = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $this->config['key']
      );
      $data = array(
        "model" => "gpt-4o-mini",
        "store" => true,
        "stream" => false,
        'messages' => array(
          array(
            'role' => 'system',
            'content' => $this->config['prompt_context'],
          ),
          array(
            'role' => 'user',
            'content' => $this->config['prompt_text'],
          ),
        ),
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeaders);
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curl, CURLOPT_VERBOSE, 0);
      curl_setopt($curl, CURLOPT_HEADER, 1);
      $rawResponse = curl_exec($curl);
      $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
      $responseHeaders = substr($rawResponse, 0, $headerSize);
      $responseBody = substr($rawResponse, $headerSize);
      $openairesponse = new openairesponse($responseBody, $responseHeaders, $this->config['response_schema'], $rawResponse);
      return $openairesponse;

    } catch (Exception $e) {
      return false;
    }
  }
}
?>