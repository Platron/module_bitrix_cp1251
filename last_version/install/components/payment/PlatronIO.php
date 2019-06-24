<?php
class PlatronIO {
	const PLATRON_URL = 'https://www.platron.ru';
	const PAYMENT_ENDPOINT = 'payment.php';

	public static function doApiRequest($scriptName, $params, $secretKey) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::PLATRON_URL . "/" . $scriptName);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec ($ch);

		if ($response === false) {
			$errorNumber = curl_errno($ch);
			$errorText = curl_error($ch);
			throw new Exception('Error while request to ' . $scriptName . ': (' . $errorNumber . ') ' . $errorText);
		}

		curl_close ($ch);

		try {
			$responseXml = new SimpleXMLElement($response);
		} catch (Exception $e) {
			throw new Exception('Error response from ' . $scriptName . ': ' . $e->getMessage());
		}

		if (!PlatronSignature::checkXml($scriptName, $responseXml, $secretKey)) {
			throw new Exception('Error response from ' . $scriptName . ': invalid response signature');
		}

		if ($responseXml->pg_status != 'ok') {
			throw new Exception('Error response from ' . $scriptName . ': ' . $responseXml->pg_error_description);
		}

		return $responseXml;
	}

	static public function getRequest ()
	{
		global $HTTP_RAW_POST_DATA;

		if (isset($_POST['pg_xml'])) {
			$inData['pg_xml'] = $_POST['pg_xml'];
		}
		elseif (isset($_POST['pg_sig'])) {
			$inData = $_POST;
		}
		elseif (isset($_GET['pg_sig'])) {
			$inData = $_GET;
		}
		elseif ( !empty($HTTP_RAW_POST_DATA) ) {
			$inData['pg_xml'] = $HTTP_RAW_POST_DATA;
		}
		elseif ( ($HTTP_RAW_POST_DATA = file_get_contents("php://input")) ) {
			// get HTTP_RAW_POST_DATA if it is not set in php.ini to always_populate_raw_post_data
			$inData['pg_xml'] = $HTTP_RAW_POST_DATA;
		}
		else 
			return null;

		return $inData;
		
	}
		
	static public function makeResponse( $strScriptName, $strShopKey, $strStatus, $strDescription, $strSalt = null)
	{
		global $APPLICATION;
		if(!$strSalt)
			$strSalt = uniqid();
		
		$APPLICATION->RestartBuffer();
		
		$arrResponse = array();
		$arrResponse['pg_salt'] = rand(21,43433);
		$arrResponse['pg_status'] = $strStatus;
		$arrResponse['pg_description'] = $strDescription;
		$arrResponse['pg_sig'] = PlatronSignature::make($strScriptName, $arrResponse, $strShopKey);

		header("Content-Type: text/xml");
		header("Pragma: no-cache");
		$strResponse = "<"."?xml version=\"1.0\" encoding=\"utf-8\"?".">\n";
		$strResponse .= "<response>";
			$strResponse .= "<pg_salt>".$arrResponse['pg_salt']."</pg_salt>";
			$strResponse .= "<pg_status>".$arrResponse['pg_status']."</pg_status>";
			$strResponse .= "<pg_description>".$arrResponse['pg_description']."</pg_description>";
			$strResponse .= "<pg_sig>".$arrResponse['pg_sig']."</pg_sig>";
		$strResponse .= "</response>";
		echo $strResponse;
		exit();
	}
	
	/**
	 *
	 * @param string $strPhone
	 * @return null|string 
	 */
	public static function checkAndConvertUserPhone ( $strPhone )
	{
		preg_match_all("/\d/", @$strPhone, $array);
		$strPhone = implode('',$array[0]);
		
		return $strPhone;
	}
	
	/**
	 * 
	 */
	private static function toDigits($str)
	{
		return preg_replace("/\D/s", "", $str);
	}

	/**
	 * 
	 */
	public static function emailIsValid($strEmail)
	{
		return preg_match('/^[\w.+-]+@[\w.-]+\.\w{2,}$/s', $strEmail);
	}

	public function createPaymentUrl($requestParameters, $receiptItems, $secretKey)
	{
		$parameters = array_merge($requestParameters, self::convertReceiptItems($receiptItems));

		$parameters['pg_sig'] = PlatronSignature::make(self::PAYMENT_ENDPOINT, $parameters, $secretKey);

		$url = self::PLATRON_URL . '/' . self::PAYMENT_ENDPOINT
			. '?' . http_build_query($parameters);

		return $url;
	}

	private static function convertReceiptItems($receiptItems)
	{
		$result = [];
		$i = 0;
		foreach ($receiptItems as $receiptItem) {
			foreach ($receitpItem->toArray() as $name => $value) {
				$result['pg_items[' . $i . '][' . $name . ']'] = $value;
			}
			$i++;
		}

		return $result;
	}
}
?>