<?php
namespace sdavis1902\QboLaravel;

use Session;

class Auth extends Qbo{
	public function connect(){
		$temporaryCredentials = $this->server->getTemporaryCredentials();
        Session::put('qbo_temporary_credentials', $temporaryCredentials);
        $this->server->authorize($temporaryCredentials);
	}

	public function check(){
		if( !$this->tc ) return false;

		return true;
	}

	public function handleCallback($request){
		if( $request->has('oauth_token') && $request->has('oauth_verifier') && $request->has('realmId') ){

           // Retrieve the temporary credentials we saved before
            $temporaryCredentials = $request->session()->get('qbo_temporary_credentials');

            // We will now retrieve token credentials from the server
            $tokenCredentials = $this->server->getTokenCredentials($temporaryCredentials, $request->input('oauth_token'), $request->input('oauth_verifier'));
			$request->session()->put('qbo_token_credentials', $tokenCredentials);
			$request->session()->put('qbo_realm_id', $request->input('realmId'));
        }
	}
}
