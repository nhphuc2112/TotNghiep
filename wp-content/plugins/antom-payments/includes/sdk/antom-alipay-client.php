<?php
/**
 * Antom_Alipay_Client
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/sdk/antom-signature-tool.php';
class Antom_Alipay_Client {

	const DEFULT_KEY_VERSION = 1;
	private $gatewayUrl;
	private $merchantPrivateKey;
	private $alipayPublicKey;

	public function __construct( $gatewayUrl, $merchantPrivateKey, $alipayPublicKey ) {
		$this->gatewayUrl         = $gatewayUrl;
		$this->merchantPrivateKey = $merchantPrivateKey;
		$this->alipayPublicKey    = $alipayPublicKey;
	}

	public function execute( $request ) {

		$this->checkRequestParam( $request );

		$clientId   = $request->get_client_id();
		$httpMethod = $request->get_http_method();
		$path       = $request->get_path();
		$keyVersion = $request->get_key_version();
		$reqTime    = gmdate( DATE_ISO8601 );
		$reqBody    = $request->toJson();

		$signValue     = $this->genSignValue( $httpMethod, $path, $clientId, $reqTime, $reqBody );
		$baseHeaders   = $this->buildBaseHeader( $reqTime, $clientId, $keyVersion, $signValue );
		$customHeaders = $this->buildCustomHeader();
		if ( isset( $customHeaders ) && count( $customHeaders ) > 0 ) {
			$headers = array_merge( $baseHeaders, $customHeaders );
		} else {
			$headers = $baseHeaders;
		}

		$requestUrl = $this->genRequestUrl( $path );
		$rsp        = $this->sendRequest( $requestUrl, $httpMethod, $headers, $reqBody, $clientId, $path );
		if ( ! isset( $rsp ) || null == $rsp ) {
			throw new Exception( 'Fail to get Response, Please check your plugin privateKey config or network condition.' );
		}

		$rspBody      = $rsp->getRspBody();
		$rspSignValue = $rsp->getRspSign();
		$rspTime      = $rsp->getRspTime();

		$alipayRsp = $rspBody;

		$result = $alipayRsp->result;
		if ( ! isset( $result ) ) {
			throw new Exception( 'Response data error,result field is null,rspBody:' . esc_html( $rspBody ) );
		}

		return $alipayRsp;
	}

	private function checkRequestParam( $request ) {
		if ( ! isset( $request ) ) {
			throw new Exception( "alipayRequest can't null" );
		}

		$clientId   = $request->get_client_id();
		$httpMethod = $request->get_http_method();
		$path       = $request->get_path();
		$keyVersion = $request->get_key_version();

		if ( ! isset( $this->gatewayUrl ) || trim( $this->gatewayUrl ) === '' ) {
			throw new Exception( "clientId can't null" );
		}

		if ( ! isset( $clientId ) || trim( $clientId ) === '' ) {
			throw new Exception( "clientId can't null" );
		}

		if ( ! isset( $httpMethod ) || trim( $httpMethod ) === '' ) {
			throw new Exception( "httpMethod can't null" );
		}

		if ( ! isset( $path ) || trim( $path ) === '' ) {
			throw new Exception( "path can't null" );
		}

		if ( strpos( $path, '/' ) != 0 ) {
			throw new Exception( 'path must start with /' );
		}

		if ( isset( $keyVersion ) && ! is_numeric( $keyVersion ) ) {
			throw new Exception( 'keyVersion must be numeric' );
		}
	}

	private function genSignValue( $httpMethod, $path, $clientId, $reqTime, $reqBody ) {
		try {
			$signValue = Antom_Signature_Tool::sign( $httpMethod, $path, $clientId, $reqTime, $reqBody, $this->merchantPrivateKey );
		} catch ( Exception $e ) {
			throw new Exception( esc_html( $e->getMessage() ) );
		}

		return $signValue;
	}

	private function checkRspSign( $httpMethod, $path, $clientId, $rspTime, $rspBody, $rspSignValue ) {
		try {
			$isVerify = Antom_Signature_Tool::verify( $httpMethod, $path, $clientId, $rspTime, $rspBody, $rspSignValue, $this->alipayPublicKey );
		} catch ( Exception $e ) {
			throw new Exception( esc_html( $e->getMessage() ) );
		}

		return $isVerify;
	}

	private function buildBaseHeader( $requestTime, $clientId, $keyVersion, $signValue ) {
		$baseHeader                 = array();
		$baseHeader['Content-Type'] = 'application/json; charset=UTF-8';
		$baseHeader['User-Agent']   = 'global-alipay-sdk-php';
		$baseHeader['Request-Time'] = $requestTime;
		$baseHeader['client-id']    = $clientId;

		if ( ! isset( $keyVersion ) ) {
			$keyVersion = self::DEFULT_KEY_VERSION;
		}
		$signatureHeader         = 'algorithm=RSA256,keyVersion=' . $keyVersion . ',signature=' . $signValue;
		$baseHeader['Signature'] = $signatureHeader;

		return $baseHeader;
	}

	private function genRequestUrl( $path ) {
		if ( strpos( $this->gatewayUrl, 'https://' ) != 0 ) {
			$this->gatewayUrl = 'https://' . $this->gatewayUrl;
		}

		if ( substr_compare( $this->gatewayUrl, '/', - strlen( '/' ) ) === 0 ) {
			$len              = strlen( $this->gatewayUrl );
			$this->gatewayUrl = substr( $this->gatewayUrl, 0, $len - 1 );
		}

		$requestUrl = $this->gatewayUrl . $path;

		return $requestUrl;
	}


	protected function buildCustomHeader() {
		return null;
	}

	protected function sendRequest( $request_url, $http_method, $headers, $request_body, $clientId, $path ) {
		
		$response = wp_remote_post(
			$request_url,
			array(
				'headers'   => $headers,
				'body'      => $request_body,
				'method'    => $http_method,
				'timeout'     => 30,
				// 'data_format' => 'body',
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			return null;
		}

		$rspBody = json_decode( wp_remote_retrieve_body( $response ) );

		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/sdk/antom-http-rpc-result.php';

		$headArr = $response['headers']->getAll();

		$httpRpcResult = new Antom_Http_Rpc_Result();
		$httpRpcResult->setRspBody( $rspBody );
		
		foreach ( $headArr as $headerkey=>$headerItem ){
			if(! is_array( $headerItem ) && $headerkey=='response-time'){
				$httpRpcResult->setRspTime( trim( $headerItem ) );
			}
			if(! is_array( $headerItem ) && $headerkey=='signature'){
				$signatureValue = $this->getResponseSignature( $headerItem );
				if ( isset( $signatureValue ) && null != $signatureValue ) {
					$httpRpcResult->setRspSign( $signatureValue );
				}
			}
		}

		$rspBody      = $httpRpcResult->getRspBody();
		$rspSignValue = $httpRpcResult->getRspSign();
		$rspTime      = $httpRpcResult->getRspTime();

		if ( ! isset( $rspSignValue ) || trim( $rspSignValue ) === '' || ! isset( $rspTime ) || trim( $rspTime ) === '' ) {
			return $alipayRsp;
		}

		$isVerifyPass = $this->checkRspSign( $http_method, $path, $clientId, $rspTime, wp_remote_retrieve_body( $response )  , $rspSignValue );


		if ( ! $isVerifyPass ) {
			throw new Exception( 'public key verify fail, please check your plugin public key config' );
		}

		return $httpRpcResult;
	}


	private function getResponseTime( $headerItem ) {
		
		if ( strstr( $headerItem, 'response-time' ) ) {
			$startIndex   = strpos( $headerItem, ':' ) + 1;
			$responseTime = substr( $headerItem, $startIndex );

			return $responseTime;
		}

		return null;
	}

	private function getResponseSignature( $headerItem ) {
		if ( strstr( $headerItem, 'signature' ) ) {
			$startIndex     = strrpos( $headerItem, '=' ) + 1;
			$signatureValue = substr( $headerItem, $startIndex );

			return $signatureValue;
		}

		return null;
	}
}
