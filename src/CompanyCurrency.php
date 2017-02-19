<?php
namespace sdavis1902\QboPhp;

use Exception;

class CompanyCurrency extends Qbo{
	protected $query_table = 'CompanyCurrency';

	public function delete($id){
		$this->update([
			'Id'     => $id,
			'Active' => false
		]);
	}
}
