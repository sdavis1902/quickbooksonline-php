<?php
namespace sdavis1902\QboPhp;

use Exception;

class Budget extends Qbo{
	protected $query_table = 'Budget';

	public function create(array $args){
		throw new Exception('no create api call available for this class');
	}

	public function find($id){
		throw new Exception('no find api call available for this class');
	}

	public function update(array $args){
		throw new Exception('no update api call available for this class');
	}
}
