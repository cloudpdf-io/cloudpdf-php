<?php
namespace CloudPDF;

use \Firebase\JWT\JWT; 

class Client
{
  /** @var string */
  private $apiKey;
  /** @var string */
  private $cloudName;
  /** @var string */
  private $signingSecret;
  /** @var bool */
  private $isSigned = false;
  /** @var string */
  protected static $apiBase = 'https://api.cloudpdf.io/v2';

  public function __construct(array $config)
  {
    $this->apiKey = $config['apiKey'] ?? null;

    if(!$this->apiKey) {
      throw new \Exception('apiKey is required');
    }

    $this->cloudName = $config['cloudName'] ?? null;
    $this->signingSecret = $config['signingSecret'] ?? null;

    if($config['cloudName'] && $config['signingSecret']) {
      $this->isSigned = true;
    }
  }

  public function setSigned(bool $enabled) 
  {
    if($enabled && (!$this->cloudName || !$this->signingSecret)) {
      throw new \Exception('cloudName and signingSecret should be set');
    }

    $this->isSigned = $enabled;
  }

  private function getSignedToken(string $functionName, \ArrayObject $params, int $expiresIn) 
  {
    $payload = [
      'function' => $functionName,
      'params' => $params,
      'exp' => time() + $expiresIn
    ];

    $header = [
      'kid' => $this->cloudName
    ];

    $jwt = JWT::encode($payload, $this->signingSecret, 'HS256', null, $header);

    return $jwt;
  }

  private function factory(?string $functionName, ?\ArrayObject $params)
  {
    if($this->isSigned) {
      /** Use signed JWT token key */
      $headers = [
        'Content-Type: application/json',
        'X-Authorization: '. $this->getSignedToken($functionName, $params, 15)
      ];
    } else {
      /** Use API key */
      $headers = [
        'Content-Type: application/json', 
        'X-Authorization: ' . $this->apiKey
      ];
    }

    $client = new Api(self::$apiBase, $headers);
    return $client;
  }


  public function account()
  {
    return $this
      ->factory('APIV2GetAccount', new \ArrayObject())
      ->get('/account');
  }

  public function auth()
  {
    return $this
      ->factory('APIV2GetAuth', new \ArrayObject())
      ->get('/auth');
  }

  public function createDocument(array $params) 
  {
    return $this
      ->factory('APIV2CreateDocument', new \ArrayObject($params))
      ->post('/documents', $params);
  }

  public function getDocument(string $id) 
  {
    return $this
      ->factory('APIV2GetDocument', new \ArrayObject(['id' => $id]))
      ->get('/documents/' . $id);
  }

  public function updateDocument(string $id, array $params) 
  {
    $parameters = array_merge(['id' => $id], $params);

    return $this
      ->factory('APIV2UpdateDocument', new \ArrayObject($parameters))
      ->put('/documents/' . $id, $parameters);
  }

  public function deleteDocument(string $id) 
  {
    return $this
      ->factory('APIV2DeleteDocument', new \ArrayObject(['id' => $id]))
      ->delete('/documents/' . $id);
  }

  public function createNewFileVersion(string $id, array $params)
  {
    $parameters = array_merge(['id' => $id], $params);

    return $this
      ->factory('APIV2CreateDocumentFile', new \ArrayObject($parameters))
      ->post('/documents/' . $id . '/files', $parameters);
  }

  public function uploadDocumentFileComplete(string $id, string $fileId, array $params)
  {
    $parameters = array_merge([
      'id' => $id,
      'fileId' => $fileId
    ], $params);

    return $this
      ->factory('APIV2PatchDocumentFile', new \ArrayObject($parameters))
      ->patch('/documents/' . $id . '/files/' . $fileId, $parameters);
  }

  public function getDocumentFile(string $documentId, string $fileId) 
  {
    return $this
      ->factory('APIV2GetDocumentFile', new \ArrayObject([
        'id' => $documentId,
        'fileId' => $fileId
      ]))
      ->get('/documents/' . $documentId . '/files/' . $fileId);
  }

  /** Document TOKEN generation */
  public function getViewerToken(array $params, int $expiresIn = 60*60) {
    return $this
      ->getSignedToken('APIGetDocument', new \ArrayObject($params), $expiresIn);
  }

  /** Webhooks */
  public function createWebhook(array $params) 
  {
    return $this
      ->factory('APIV2CreateWebhook', new \ArrayObject($params))
      ->post('/webhooks', $params);
  }

  public function getWebhook(string $id) 
  {
    return $this
      ->factory('APIV2GetWebhook', new \ArrayObject(['id' => $id]))
      ->get('/webhooks/' . $id);
  }

  public function updateWebhook(string $id, array $params) 
  {
    $parameters = array_merge(['id' => $id], $params);

    return $this
      ->factory('APIV2UpdateWebhook', new \ArrayObject($parameters))
      ->put('/webhooks/'. $id, $parameters);
  }

  public function deleteWebhook(string $id) 
  {
    return $this
      ->factory('APIV2DeleteWebhook', new \ArrayObject(['id' => $id]))
      ->delete('/webhooks/' . $id);
  }

  public function listWebhooks() 
  {
    return $this
      ->factory('APIV2GetWebhooks', new \ArrayObject())
      ->get('/webhooks');
  }
}
?>