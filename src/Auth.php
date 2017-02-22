<?php
namespace sdavis1902\QboPhp;

class Auth extends Qbo{
	public function connect(){
		$temporaryCredentials = $this->server->getTemporaryCredentials();
        // Session::put('qbo_temporary_credentials', $temporaryCredentials);
        $_SESSION['qbo_temporary_credentials'] =  serialize($temporaryCredentials);
        $this->server->authorize($temporaryCredentials);
	}

	public function check(){
		if( !$this->tc ) return false;

		return true;
	}

	public function handleCallback(){
		if( $_GET['oauth_token'] && $_GET['oauth_verifier'] && $_GET['realmId'] ){

           // Retrieve the temporary credentials we saved before
            $temporaryCredentials = $this->tempc;

			if(!$temporaryCredentials) return false;

            // We will now retrieve token credentials from the server
            $tokenCredentials = $this->server->getTokenCredentials($temporaryCredentials, $_GET['oauth_token'], $_GET['oauth_verifier']);
			$_SESSION['qbo_token_credentials'] = serialize($tokenCredentials);
			$_SESSION['qbo_realm_id'] = $_GET['realmId'];
        }
	}
}
