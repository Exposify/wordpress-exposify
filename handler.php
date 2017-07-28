<?php

/**
 * Blueprint class to bundle API functionality.
 */
abstract class ApiBlueprint
{
	/**
	 * API JSON result converted to an array.
	 *
	 * @var array
	 */
	protected $result = [];

	/**
	 * API JSON error converted to an array.
	 *
	 * @var array
	 */
	protected $error = [];

	/**
	 * The URL to connect with Exposify API.
	 *
	 * @var string
	 */
	protected $apiUrl = '';

	/**
	 * The secret key to connect with Exposify API.
	 *
	 * @var string
	 */
	protected $apiKey = '';

	/**
	 * Request and store data from a specific URL.
	 *
	 * @param  string  $url
	 * @return void
	 */
	protected function requestData($url)
	{
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL            => $url,
			CURLOPT_TIMEOUT        => 5
		]);
		$json = json_decode(curl_exec($curl), true);

		if (isset($json['error'])) {
			$this->error = $json['error'];
		} else if (isset($json['data'])) {
			$this->result = $json['data'];
		} else {
			$this->error = [
				'title' => 'Server Error',
				'description' => 'The server did not return valid data.'
			];
		}

		curl_close($curl);
	}

	/**
	 * Request all properties.
	 *
	 * @param  string  $searchQuery
	 * @return void
	 */
	public function requestAllProperties($searchQuery)
	{
		$url = $this->apiUrl . '?api_token=' . $this->apiKey . '&search=' . urlencode($searchQuery);
		$this->requestData($url);
	}

	/**
	 * Request a single property.
	 *
	 * @param  string  $slug
	 * @return void
	 */
	public function requestSingleProperty($slug)
	{
		$url = $this->apiUrl . '/' . $slug . '?api_token=' . $this->apiKey;
		$this->requestData($url);
	}

	/**
	 * Return the result of the finished request.
	 *
	 * @return array
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Return the error of the finished request.
	 *
	 * @return array
	 */
	public function getError()
	{
		return $this->error;
	}
}

/**
 * Class to allow access to the HTML API.
 */
class HtmlHandler extends ApiBlueprint
{
	/**
	 * Construct the class.
	 *
	 * @param  string  $apiUrl
	 * @param  string  $apiKey
	 * @return void
	 */
	public function __construct($apiUrl, $apiKey)
	{
		$this->apiUrl = $apiUrl;
		$this->apiKey = $apiKey;
	}

	/**
	 * Output the result of the HTML API request.
	 *
	 * @return void
	 */
	public function getContent()
	{
		if (!empty($this->error)) {
			http_response_code($this->error['id'] ?: 404);
			return htmlspecialchars_decode($this->error['attributes']['html']);
		} else {
			return htmlspecialchars_decode($this->result['attributes']['html']);
		}
	}

	/**
	 * Output the title of the requested property.
	 *
	 * @return void
	 */
	public function getTitle()
	{
		if (isset($this->result['attributes']['title'])) {
			return $this->result['attributes']['title'];
		}
	}

	/**
	 * Output the description of the requested property.
	 *
	 * @return void
	 */
	public function getDescription()
	{
		if (isset($this->result['attributes']['description'])) {
			return $this->result['attributes']['description'];
		}
	}
}

/**
 * Class to handle the JSON API and allow access to the HTML API.
 */
class Exposify extends ApiBlueprint
{
	/**
	 * The HtmlHandler Instance.
	 *
	 * @var HtmlHandler
	 */
	public $html = null;

	/**
	 * Construct the class and instantiate the HtmlHandler.
	 *
	 * @param  string  $apiKey
	 * @return void
	 */
	public function __construct($apiKey, $apiBaseUrl = 'https://app.exposify.de')
	{
		$this->apiUrl = $apiBaseUrl . '/api/v1/json';
		$this->apiKey = $apiKey;
		$this->html   = new HtmlHandler($apiBaseUrl . '/api/v1/html', $apiKey);
	}
}
