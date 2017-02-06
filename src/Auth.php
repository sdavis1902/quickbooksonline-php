<?php
namespace sdavis1902\QboLaravel;

use Session;

class Auth {
	private $server;
	private $tempc;
	private $tc;

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

	public function connect(){
		$temporaryCredentials = $this->server->getTemporaryCredentials();
        Session::put('temporary_credentials', $temporaryCredentials);
        $this->server->authorize($temporaryCredentials);
	}

	public function check(){

	}

	public function handleCallback($request){
		if( $request->has('oauth_token') && $request->has('oauth_verifier') && $request->has('realmId') ){

           // Retrieve the temporary credentials we saved before
            $temporaryCredentials = $request->session()->get('temporary_credentials');

            // We will now retrieve token credentials from the server
            $tokenCredentials = $this->server->getTokenCredentials($temporaryCredentials, $request->input('oauth_token'), $request->input('oauth_verifier'));
			$request->session()->put('token_credentials', $tokenCredentials);
			$request->session()->put('realm_id', $request->input('realmId'));
        }
	}

	public function getUser(){
        $user = $this->server->getUserDetails($this->tc);
		return $user;
	}

	public function doOtherCall(){
		$url = $this->base_url . 'v3/company/'.$this->realm_id.'/query?query=select * from Employee';

		$headers = $this->server->getCallHeaders($this->tc, 'GET', $url);
		$headers['Accept'] = 'application/json';

		try {
			$response = $this->client->get($url, [
				'headers' => $headers
			]);
		}catch( \GuzzleHttp\Exception\ClientException $e ){
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            echo $responseBodyAsString;die;
        }

		$customers = json_decode($response->getBody()->getContents());
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
