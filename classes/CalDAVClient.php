<?php
/**
 * CalDav Client
 *
 * dependencies: pecl_http
 *
 *references:
 * http://blogs.nologin.es/rickyepoderi/index.php?/archives/15-Introducing-CalDAV-Part-I.html
 * https://code.google.com/p/sabredav/wiki/BuildingACalDAVClient
 *
 * Version: 0.2
*/

class CalDAVClient {

	const HTTP_BASIC = HTTP_AUTH_BASIC;
	const HTTP_DIGEST = HTTP_AUTH_DIGEST;

	//private
	private $userName;
	private $password;
	private $authType;
	private $urlData;
	private $protocol = HTTP_VERSION_1_1;

	private $depth = 0; //@todo


	/**
	 * COnstructor
	 * @param $url
	 * @param $username
	 * @param $password
	 * @param $authType
	 */
	function __construct($url, $username, $password, $authType = self::HTTP_BASIC)
	{

		$this->setCredentials($username, $password);
		$this->setUrl($url);

		$this->setAuthType($authType);
	}

	/**
	 * Sets calendar's url.
	 * @param $url
	 * @throws Exception
	 */
	public function setUrl($url)
	{
		if(empty($url))
		{
			throw new Exception('Url cannot be empty.');
		}

		$this->urlData = parse_url($url);
		$this->urlData['url'] = $url;
	}

	/**
	 * Returns calendar's url.
	 * @return mixed
	 */
	public function getUrl()
	{
		return $this->urlData['url'];
	}

	/**
	 * Sets user's credentials
	 * @param $username
	 * @param $password
	 * @throws Exception
	 */
	public function setCredentials($username, $password)
	{
		if(empty($username) || empty($password))
		{
			throw new Exception('Username and password fields cannot be empty.');
		}else
		{
			$this->userName = $username;
			$this->password = $password;
		}
	}

	/**
	 * Returns username.
	 * @return mixed
	 */
	public function getUsername()
	{
		return $this->userName;
	}

	/**
	 * Sets the protocol that will be used during the HTTP requests (0 = HTTP 1.0, 1 = HTTP 1.1)
	 * @param $protocol
	 */
	public function setProtocol($protocol)
	{
		if($protocol === 1)
		{
			$this->protocol = HTTP_VERSION_1_1;
		}else
		{
			$this->protocol = HTTP_VERSION_1_0;
		}
	}

	/**
	 * Returns the protocol used during the HTTP requests.
	 * @return int
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}

	/**
	 * Sets the HTTP auth type (BASIC or DIGEST).
	 * @param $auth
	 */
	public function setAuthType($auth)
	{
		if(defined($auth))
		{
			$this->authType = $auth;
		}else
		{
			$this->authType = self::HTTP_BASIC;
		}
	}

	/**
	 * Returns the HTTP auth type.
	 * @return mixed
	 */
	public function getAuthType()
	{
		return $this->authType;
	}


	/**
	 * Performs an OPTIONS request.
	 * @param string $relative_url
	 * @return HttpMessage
	 */
	public function doOptions($relative_url = '')
	{

		$url = $this->buildUrl($relative_url);

		$result = $this->doRequest('options', $url);
		return $result;
	}

	/**
	 * Builds the absolute URL.
	 * @param $relative_url
	 * @return string
	 */
	private function buildUrl($relative_url)
	{
		if(!empty($relative_url))
		{
			$url = $this->urlData['scheme'].'://'.$this->urlData['host'].$relative_url;
		}else
		{
			$url = $this->urlData['url'];
		}
		return $url;
	}

	/**
	 * Performs a PROPFIND request.
	 * @param string $relativeUrl
	 * @return HttpMessage
	 */
	public function doPropFind($relativeUrl = '')
	{
		$url = $this->buildUrl($relativeUrl);

		$propfind_data = '<?xml version="1.0" encoding="utf-8" ?><propfind xmlns="DAV:"><prop></prop></propfind>';

		$headers = array(
			'depth' => '0'
		);

		$options = array(
			'headers' => $headers
		);
		$result = $this->doRequest('propfind', $url, $options, $propfind_data,"text/xml; charset=UTF-8");

		return $result;
	}

	/**
	 * Performs a PUT request.
	 * @param string $relativeUrl
	 * @param $data
	 * @param string $contentType
	 * @return bool|HttpMessage
	 */
	public function doPut($relativeUrl = '', $data, $contentType = "text/calendar; charset=utf-8")
	{

		if(empty($relativeUrl))
		{
			return false;
		}

		$url = $this->buildUrl($relativeUrl);

		$headers = array(
			'If-None-Match' => '*',
		);

		$options = array(
			'headers' => $headers
		);

		$result = $this->doRequest('put', $url, $options, $data, $contentType);

		return $result;
	}


	//@todo
	public function doReport()
	{

	}

	/**
	 * Performs a DELETE request.
	 * @param $relativeUrl
	 * @return bool|HttpMessage
	 */
	public function doDelete($relativeUrl)
	{
		if(empty($relativeUrl))
		{
			return false;
		}

		$url = $this->buildUrl($relativeUrl);

		$result = $this->doRequest('delete', $url);

		return $result;
	}

	/**
	 * Performs a HTTP request.
	 * @param $method
	 * @param $url
	 * @param null $options
	 * @param null $content
	 * @param null $contentType
	 * @return HttpMessage
	 */
	public function doRequest($method, $url, $options = null, $content = null, $contentType = null)
	{
		$method = strtolower($method);

		//merge options
		$defaults = array(
			//headers => $header_array,
			'httpauth' => $this->userName.':'.$this->password,
			'httpauthtype' => $this->authType,
			'protocol' => $this->protocol);

		if(is_array($options))
		{
			$defaults = array_merge($defaults, $options);
		}

		switch($method)
		{
			case 'delete':
				$r = new httpRequest($url, HttpRequest::METH_DELETE, $defaults);
				break;
			case 'put':
				$r = new httpRequest($url, HttpRequest::METH_PUT, $defaults);
				$r->setContentType($contentType);
				$r->setPutData($content);
				break;
			case 'propfind':
				$r = new HttpRequest($url, HttpRequest::METH_PROPFIND, $defaults);
				$r->setContentType($contentType);
				$r->setBody($content);
				break;
			case 'options':
				$r = new HttpRequest($url, HttpRequest::METH_OPTIONS, $defaults);
				break;
			case 'get':
			default:
				$r = new HttpRequest($url, HttpRequest::METH_GET, $defaults);
		}

		return $r->send();
	}
}