<?php

class JSON
{
	private $response;

	public function __construct()
	{
		$this->response = new stdClass();
	}

	public function addResponse($name, $data)
	{
	    if(is_object($data) && is_a($data, "Error")) $this->response->$name = array("error"=>$data);
        else $this->response->$name = $data;
	}

	public function send($skip_headers = false)
	{
		if(!$skip_headers) header("Content-type: application/json");
		echo json_encode($this->response);
		exit;
	}
}

?>