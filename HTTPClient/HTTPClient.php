<?php

class HTTPResponse
{
	protected $_headers = array();
	protected $_data = NULL;
	
	public function __construct($data, $headers = array())
	{
		$this->_headers = $headers;
		$this->_data = $data;
	}
	
	public function headers()
	{
		return $this->_headers;
	}
	
	public function status()
	{
		return value_for_key('http_code', $this->headers());
	}
	
	public function data()
	{
		return $this->_data;
	}
}

class HTTPClient
{
	protected $_base_url = NULL;
	protected $_default_headers = array();
	
	public function __construct($base_url)
	{
		$this->_base_url = $base_url;
	}
	
	public function setDefaultHeader($key, $value)
	{
		$this->_default_headers[] = $key.':'.$value;
	}
	
	public function get($path, $parameters = array())
	{
		return $this->request('GET', $path, $parameters);
	}
	
	public function post($path, $parameters = array())
	{
		return $this->request('POST', $path, $parameters);
	}
	
	public function request($type, $path, $parameters)
	{
		
		$url = $this->_base_url . $path;
		
		$ch = curl_init();

		if ( $type == 'POST' )
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
		}
		else
		{		
			curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
			
			if ( $parameters )
			{
				$parameters = (is_array($parameters)) ? http_build_query($parameters) : $parameters;
			
				$url .= '?'.$parameters;
			}
		}
	
		if ( is_array($this->_default_headers) )
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_default_headers);
		}
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		ob_start();
		$data = curl_exec($ch);
		ob_clean();
		
		$headers = curl_getinfo($ch);

		curl_close($ch);
		
		return new HTTPResponse($data, $headers);
	}
}