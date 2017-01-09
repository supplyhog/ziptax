<?php

namespace supplyhog;

/**
 * A PHP wrapper for the zip-tax.com API.
 *
 * @package ZipTax
 * @version 2.0.0
 * @link https://github.com/supplyhog/ziptax
 * @author Vu Tran <vu@vu-tran.com>
 * @website http://vu-tran.com/
 */
class ZipTax
{

	/**
	 * Success Code returned via rCode.
	 */
	const SUCCESS_CODE = 100;

	/**
	 * Error Codes returned via rCode with error response.
	 */
	const ERROR_CODES = [
		101 => 'Invalid Key',
		102 => 'Invalid State',
		103 => 'Invalid City',
		104 => 'Invalid Postal Code',
		105 => 'Invalid Format',
	];

	/**
	 * @access protected
	 * @var string
	 */
	protected $_key = '';

	/**
	 * @access protected
	 * @var string
	 */
	protected $_endpoint = 'http://api.zip-tax.com';

	/**
	 * @access protected
	 * @var string
	 */
	protected $_action = 'request';

	/**
	 * @access protected
	 * @var string
	 */
	protected $_version = 'v20';

	/**
	 * @access protected
	 * @var string
	 */
	protected $_format = 'JSON';

	/**
	 * @access protected
	 * @var array|bool
	 */
	protected $_lastRequest = false;

	/**
	 * Instantiates a new instance
	 *
	 * @param string $key | Your assigned Zip-Tax API key
	 * @param string $format | JSON or XML If any other string is passed, it will default to JSON.
	 */
	public function __construct($key, $format = 'JSON')
	{
		$this->_key = $key;

		$format = strtoupper($format);
		if(in_array($format, ['JSON', 'XML'])) {
			$this->_format = $format;
		}
	}

	/**
	 * Runs a curl request to the API
	 *
	 * @access protected
	 * @param array $params
	 * @link http://www.zip-tax.com/documentation
	 * @return array | boolean
	 * @throws \Exception
	 */
	protected function _call($params)
	{
		$params = array_map('trim', $params);
		$queryString = http_build_query($params);
		$requestEndpoint = sprintf('%s/%s/%s?%s', $this->_endpoint, $this->_action, $this->_version, $queryString);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $requestEndpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		$info = curl_getinfo($ch);
		$err = curl_error($ch);
		curl_close($ch);

		/**
		 * Test that a valid response was returned.
		 * If not, an Exception is thrown with the correct error information.
		 */
		if ($res === false || $info['http_code'] != 200) {
			$errMsg = 'No data returned. http_code: ' . $info['http_code'];
			if ($err) {
				$errMsg .= " $err";
			}
			throw new \Exception($errMsg);
		} else {
			$rsp = json_decode($res, true);

			//  Only set _lastRequest if data is returned.
			$this->_lastRequest = empty($rsp['results']) ? false : $rsp;

			// If a response error code is returned, throw an exception with the code information.
			if (array_key_exists($rsp['rCode'], self::ERROR_CODES)) {
				throw new \Exception(self::ERROR_CODES[$rsp['rCode']]);
			}
		}
		return $this->_lastRequest;
	}

	/**
	 * Request the API for a rate by a given postal code, city, and state.
	 * Example Return:
	 * [
	 *  'version" => 'v20',
	 *  'rCode' => 100,
	 *  'results' => [
	 *      'geoPostalCode' => '90210',
	 *      'geoCity' => 'BEVERLY HILLS',
	 *      'geoCounty' => 'LOS ANGELES',
	 *      'geoState' => 'CA',
	 *      'taxSales' => 0.087499998509884,
	 *      'taxUse' => 0.087499998509884,
	 *      'txbService' => 'N',
	 *      'stateSalesTax' => 0.059999998658895,
	 *      'stateUseTax' => 0.059999998658895,
	 *      'citySalesTax' => 0,
	 *      'cityUseTax' => 0,
	 *      'cityTaxCode => '',
	 *      'countySalesTax' => 0.0024999999441206,
	 *      'countyUseTax' => 0.0024999999441206,
	 *      'countyTaxCode => '19',
	 *      'districtSalesTax' => 0.025000000372529,
	 *      'districtUseTax' => 0.025000000372529
	 *  ]
	 * ]
	 *
	 * @access public
	 * @param string $postalCode | The 5 digit postal code. NOTE: Include leading zeros
	 * @param string $city | The full city name
	 * @param string $state | The 2 letter state code. Example: TN for Tennessee
	 * @return array | boolean
	 * @throws \Exception
	 */
	public function request($postalCode, $city = null, $state = null)
	{
		$params = [
			'key' => $this->_key,
			'postalcode' => $postalCode,
			'format' => $this->_format
		];

		if ($city) {
			$params['city'] = $city;
		}

		if ($state) {
			if (strlen($state) > 2) {
				throw new \Exception('The state must be a two character state code.  Example: TN for Tennessee');
			}

			$params['state'] = $state;
		}

		try {
			return $this->_call($params);
		} catch (\Exception $e) {
			throw $e;
		}
	}

}

?>
