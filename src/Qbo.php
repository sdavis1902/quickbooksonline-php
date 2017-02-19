<?php
namespace sdavis1902\QboPhp;

// use Session;
use Exception;

class Qbo {
	protected $server;
	protected $tempc;
	protected $tc;
	private $realm_id;
	private $client;
	private $base_url;
	protected $query_table = null;
	protected $query_select = null;
	protected $query_where = null;
	protected $query_order = null;
	protected $query_start = 0;
	protected $query_limit = 100;

    public function __construct($identifier = null, $secret = null, $callback_url = null){
        $this->server = new \sdavis1902\QboPhp\Server([
            'identifier'   => $identifier ? $identifier : env('QBO_IDENTIFIER'),
            'secret'       => $secret ? $secret : env('QBO_SECRET'),
            'callback_uri' => $callback_url ? $callback_url : env('QBO_CALLBACK_URL'),
        ]);

		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}

		// if Laravel, could use these
		//$this->tempc = Session::has('qbo_temporary_credentials') ? Session::get('qbo_temporary_credentials') : null;
		//$this->tc = Session::has('qbo_token_credentials') ? Session::get('qbo_token_credentials') : null;
		//$this->realm_id = Session::has('qbo_realm_id') ? Session::get('qbo_realm_id') : null;
		$this->tempc = isset( $_SESSION['qbo_temporary_credentials'] ) ? unserialize($_SESSION['qbo_temporary_credentials']) : null;
		$this->tc = isset( $_SESSION['qbo_token_credentials'] ) ? unserialize($_SESSION['qbo_token_credentials']) : null;
		$this->realm_id = isset( $_SESSION['qbo_realm_id'] ) ? $_SESSION['qbo_realm_id'] : null;
		$this->client = $this->server->createHttpClient();

		$this->base_url = 'https://sandbox-quickbooks.api.intuit.com/';
    }

	public function __call($method, $args){
		$class = '\\sdavis1902\\QboPhp\\'.$method;
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

	protected function call($url, $method, $args = []){
		//$url = $this->base_url . 'v3/company/'.$this->realm_id.'/query?query=select * from Employee';
		$url = $this->base_url . str_replace('{realm_id}', $this->realm_id, $url);
		$method = strtolower($method);

		$headers = $this->server->getCallHeaders($this->tc, strtoupper($method), $url);
		$headers['Accept'] = 'application/json';

		try {
			$response = $this->client->$method($url, [
				'headers' => $headers,
				'json' => $args
			]);
		}catch( \GuzzleHttp\Exception\ClientException $e ){
            $response = $e->getResponse();
			$results = json_decode($response->getBody()->getContents());

			if( isset( $results->Fault ) ){
				$message = $results->Fault->type;
				foreach( $results->Fault->Error as $error ){
					$message.= " - $error->code $error->Message: $error->Detail";
				}

				throw new Exception($message);
			}
            echo json_encode($results);
			die;
        }

		$results = json_decode($response->getBody()->getContents());

		return $results;
	}

	public function select(array $fields){
		$this->query_select = $fields;
		return $this;
	}

	public function where($field, $operator, $value){
		if( !$this->query_where ) $this->query_where = [];

		$this->query_where[] = [$field, $operator, $value];

		return $this;
	}

	// TODO
	public function whereIn(){
		return $this;
	}

	// TODO
	public function whereLike(){
		return $this;
	}

	public function order($field, $direction){
		if( !$this->query_order ) $this->query_order = [];

		$this->query_order[] = [$field, $direction];

		return $this;
	}

	public function start($start){
		$this->query_start = $start;
		return $this;
	}

	public function limit($limit){
		$this->query_limit = $limit;
		return $this;
	}

	public function getQuery(){
		if( !$this->query_table ) throw new \Exception('No query table set');

		$url = 'v3/company/{realm_id}/query?query=select '. ( $this->query_select ? join(',',$this->query_select) : '*' ) .' from '.$this->query_table;

		if( $this->query_where ){
			foreach( $this->query_where as$key => $where ){
				$url.= $key === 0 ? ' WHERE ' : ' AND ';
				$url.= $where[0].$where[1]."'$where[2]'";
			}
		}

		// order by
		if( $this->query_order ){
			foreach( $this->query_order as $order ){
				$url.= ' ORDERBY '.$order[0].' '.$order[1];
			}
		}

		if( $this->query_start ){
			$url.= ' STARTPOSITION '.$this->query_start;
		}

		if( $this->query_limit ){
			$url.= ' MAXRESULTS '.$this->query_limit;
		}

		return $url;
	}

	public function get(){
		$url = $this->getQuery();

		$results = $this->call($url, 'get');

		$results = $results->QueryResponse;
		$return = isset( $results->{$this->query_table} ) ? $results->{$this->query_table} : [];

		return $return;
	}

	public function first(){
		$results = $this->get();

		return isset( $results[0] ) ? $results[0] : false;
	}

	protected function getSyncToken($id){
		$result = $this->find($id);
		return isset( $result->SyncToken ) ? $result->SyncToken : 0;
	}

	public function totalCount(){
		$url = 'v3/company/{realm_id}/query?query=SELECT COUNT(*) FROM '.$this->query_table;

		$results = $this->call($url, 'get');
		$count = $results->QueryResponse->totalCount;

		return $count;
	}

	public function create(array $args){
		$url = 'v3/company/{realm_id}/' . strtolower($this->query_table);

		if( isset( $args['Id'] ) ) throw new Exception('Id not allowed when calling create');

		$results = $this->call($url, 'post', $args);
		$result = $results->{$this->query_table};

		return $result;
	}

	public function find($id){
		if( !$id || !is_numeric($id) ) throw new Exception('invalid customer ID');

		$url = 'v3/company/{realm_id}/'. strtolower($this->query_table) .'/' . $id;

		$results = $this->call($url, 'get');
		$return = $results->{$this->query_table};

		return $return;
	}

	public function update(array $args){
		$url = 'v3/company/{realm_id}/' . strtolower($this->query_table);

		if( !isset( $args['Id'] ) ) throw new Exception('must provide an Id in order to do update');

		$args['sparse'] = true;
		$args['SyncToken'] = $this->getSyncToken($args['Id']);

		$results = $this->call($url, 'post', $args);
		$result = $results->{$this->query_table};

		return $result;
	}

}
