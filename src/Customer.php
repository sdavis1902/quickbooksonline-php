<?php
namespace sdavis1902\QboPhp;

use Exception;

class Customer extends Qbo{
	public function create($args){
		$url = 'v3/company/{realm_id}/customer';

		if( isset( $args['Id'] ) ) throw new Exception('Id not allowed when calling create');

		$results = $this->call($url, 'post', $args);
		$customer = $results->Customer;

		return $customer;

	}

	public function get($id){
		if( !$id || !is_numeric($id) ) throw new Exception('invalid customer ID');

		$url = 'v3/company/{realm_id}/customer/' . $id;

		$results = $this->call($url, 'get');
		$customer = $results->Customer;

		return $customer;
	}

	public function search(){
		$url = 'v3/company/{realm_id}/query?query=select * from Customer';

		$results = $this->call($url, 'get');
		$customers = $results->QueryResponse->Customer;

		return $customers;
	}

	public function update(array $args){
		$url = 'v3/company/{realm_id}/customer';

		if( !isset( $args['Id'] ) ) throw new Exception('must provide an Id in order to do update');

		$args['sparse'] = true;
		$args['SyncToken'] = $this->getSyncToken($args['Id']);

		$results = $this->call($url, 'post', $args);
		$customer = $results->Customer;

		return $customer;
	}

	private function getSyncToken($id){
		$customer = $this->get($id);
		return isset( $customer->SyncToken ) ? $customer->SyncToken : 0;
	}
}
