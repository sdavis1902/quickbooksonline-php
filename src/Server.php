<?php
namespace sdavis1902\QboPhp;

use League\OAuth1\Client\Credentials\CredentialsInterface;

class Server extends \Wheniwork\OAuth1\Client\Server\Intuit {
	public function getCallHeaders(CredentialsInterface $credentials, $method, $url, array $bodyParameters = array()){
		$header = $this->getProtocolHeader(strtoupper($method), $url, $credentials, $bodyParameters);
        $authorizationHeader = array('Authorization' => $header);
        $headers = $this->buildHttpClientHeaders($authorizationHeader);
        return $headers;
	}

	protected function getProtocolHeader($method, $uri, CredentialsInterface $credentials, array $bodyParameters = array()){
        $parameters = array_merge(
            $this->baseProtocolParameters(),
            $this->additionalProtocolParameters(),
            array(
                'oauth_token' => $credentials->getIdentifier(),
            )
        );
        $this->signature->setCredentials($credentials);
        $parameters['oauth_signature'] = $this->signature->sign(
            $uri,
            array_merge($parameters, $bodyParameters),
            $method
        );

		$return = 'OAuth   oauth_token="'.rawurlencode($parameters['oauth_token']).'", ';
		$return.= 'oauth_nonce="'.rawurlencode($parameters['oauth_nonce']).'", ';
		$return.= 'oauth_consumer_key="'.rawurlencode($parameters['oauth_consumer_key']).'", ';
		$return.= 'oauth_signature_method="'.rawurlencode($parameters['oauth_signature_method']).'", ';
		$return.= 'oauth_timestamp="'.rawurlencode($parameters['oauth_timestamp']).'", ';
		$return.= 'oauth_version="'.rawurlencode($parameters['oauth_version']).'", ';
		$return.= 'oauth_signature="'.rawurlencode($parameters['oauth_signature']).'", ';

        return $return;
    }
}
