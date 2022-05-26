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
  /** @var bolean */
  private $isSigned = false;
  /** @var string */
  protected static $apiBase = 'https://api.cloudpdf.io/v2';

  public function __construct(array $config)
  {
    $this->apiKey = $config['apiKey'];
    $this->cloudName = $config['cloudName'] ?? null;
    $this->signingSecret = $config['signingSecret'] ?? null;

    if($config['cloudName'] && $config['signingSecret']) {
      $this->isSigned = true;
    }
  }

  public function setSigned(bool $enabled) {
    if($enabled && (!$this->cloudName || !$this->signingSecret)) {
      throw new \Exception('cloudName and signingSecret should be set');
    }

    $this->isSigned = $enabled;
  }

  private function getSignedToken(string $functionName, \ArrayObject $params, int $expiresIn) {
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
      $headers = ['Content-Type: application/json', 'X-Authorization: ' . $this->apiKey];
    }

    $client = new Api(self::$apiBase, $headers);
    return $client;
  }


  public function account()
  {
    return $this->factory('APIV2GetAccount', new \ArrayObject())->get('/account');
  }

  public function auth()
  {
    return $this->factory('APIV2GetAuth', new \ArrayObject())->get('/auth');
  }
}
?>