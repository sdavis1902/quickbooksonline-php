<?php
namespace sdavis1902\QboLaravel;

use Session;

class Auth {
	private $server;
	private $tempc;
	private $tc;

    public function __construct(){
        $this->server = new \Wheniwork\OAuth1\Client\Server\Intuit([
            'identifier'   => 'qyprdHfIYfNesgmDEFbBf2xh5kwXiz',
            'secret'       => 'utKbBj79C12hJ63Ra6td7Gkqu9Xl4qONY9nrF4w3',
            'callback_uri' => 'http://packagebase.sdhub.ca/test/bob2',
        ]);

		$this->tempc = Session::has('temporary_credentials') ? Session::get('temporary_credentials') : null;
		$this->tc = Session::has('token_credentials') ? Session::get('token_credentials') : null;
    }

	public function connect(){
		$temporaryCredentials = $this->server->getTemporaryCredentials();
        Session::put('temporary_credentials', $temporaryCredentials);
        $this->server->authorize($temporaryCredentials);
	}

	public function check(){

	}

	public function handleCallback($request){
		if( $request->has('oauth_token') && $request->has('oauth_verifier') ){

           // Retrieve the temporary credentials we saved before
            $temporaryCredentials = $request->session()->get('temporary_credentials');

            // We will now retrieve token credentials from the server
            $tokenCredentials = $this->server->getTokenCredentials($temporaryCredentials, $request->input('oauth_token'), $request->input('oauth_verifier'));
			$request->session()->put('token_credentials', $tokenCredentials);
        }
	}

	public function getUser(){
        $user = $this->server->getUserDetails($this->tc);
		return $user;
	}

}
