<?php
namespace sdavis1902\QboLaravel;

use Session;

class Qbo {
	protected $server;
	protected $tempc;
	protected $tc;
	private $realm_id;
	private $client;
	private $base_url;

    public function __construct(){
        $this->server = new \sdavis1902\QboLaravel\Server([
            'identifier'   => 'qyprdHfIYfNesgmDEFbBf2xh5kwXiz',
            'secret'       => 'utKbBj79C12hJ63Ra6td7Gkqu9Xl4qONY9nrF4w3',
            'callback_uri' => 'http://packagebase.sdhub.ca/test/bob2',
        ]);

		$this->tempc = Session::has('temporary_credentials') ? Session::get('temporary_credentials') : null;
		$this->tc = Session::has('token_credentials') ? Session::get('token_credentials') : null;
		$this->realm_id = Session::has('realm_id') ? Session::get('realm_id') : null;
		$this->client = $this->server->createHttpClient();

		$this->base_url = 'https://sandbox-quickbooks.api.intuit.com/';
    }

	public function __call($method, $args){
		$class = '\\sdavis1902\\QboLaravel\\'.$method;
		$exists = class_exists($class);

		if( !$exists ){
			throw new Exception('Undefined method '.$method);
		}

		$obj = new $class($args);
		return $obj;
	}

	public function getUser(){
        $user = $this->server->getUserDetails($this->tc);
		return $user;
	}

	protected function call($url, $method){
		//$url = $this->base_url . 'v3/company/'.$this->realm_id.'/query?query=select * from Employee';
		$url = $this->base_url . str_replace('{realm_id}', $this->realm_id, $url);
		$method = strtolower($method);

		$headers = $this->server->getCallHeaders($this->tc, strtoupper($method), $url);
		$headers['Accept'] = 'application/json';

		try {
			$response = $this->client->$method($url, [
				'headers' => $headers
			]);
		}catch( \GuzzleHttp\Exception\ClientException $e ){
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            echo $responseBodyAsString;die;
        }

		$results = json_decode($response->getBody()->getContents());

		return $results;
	}

	public function doOtherCall(){
		$url = 'v3/company/{realm_id}/query?query=select * from Employee';

		$customers = $this->call($url, 'get');;
		$customers = $customers->QueryResponse->Employee;

dd($customers);
		echo '<pre>';
		var_dump($customers);die;
	}

	public function createEmployee(){

		$url = $this->base_url . 'v3/company/'.$this->realm_id.'/employee';

		$args = [
			'GivenName' => 'Scott 2',
			'FamilyName' => 'Davis'
		];

		$headers = $this->server->getCallHeaders($this->tc, 'POST', $url);
//dd($headers);
		try {
			$customer = $this->client->post($url, [
				'headers' => $headers,
				'json' => $args
			]);
		}catch( \GuzzleHttp\Exception\ClientException $e ){
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            echo $responseBodyAsString;die;
        }

		dd($customer);
	}
}
