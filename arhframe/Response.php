<?php
package('arhframe');
/**
*
*/
class Response
{
	const HTTP_CONTINUE = 100;
	const HTTP_SWITCHING_PROTOCOLS = 101;
	const HTTP_PROCESSING = 102;
	const HTTP_OK = 200;
	const HTTP_CREATED = 201;
	const HTTP_ACCEPTED = 202;
	const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
	const HTTP_NO_CONTENT = 204;
	const HTTP_RESET_CONTENT = 205;
	const HTTP_PARTIAL_CONTENT = 206;
	const HTTP_MULTI_STATUS = 207;
	const HTTP_ALREADY_REPORTED = 208;
	const HTTP_IM_USED = 226;
	const HTTP_MULTIPLE_CHOICES = 300;
	const HTTP_MOVED_PERMANENTLY = 301;
	const HTTP_FOUND = 302;
	const HTTP_SEE_OTHER = 303;
	const HTTP_NOT_MODIFIED = 304;
	const HTTP_USE_PROXY = 305;
	const HTTP_RESERVED = 306;
	const HTTP_TEMPORARY_REDIRECT = 307;
	const HTTP_PERMANENTLY_REDIRECT = 308;
	const HTTP_BAD_REQUEST = 400;
	const HTTP_UNAUTHORIZED = 401;
	const HTTP_PAYMENT_REQUIRED = 402;
	const HTTP_FORBIDDEN = 403;
	const HTTP_NOT_FOUND = 404;
	const HTTP_METHOD_NOT_ALLOWED = 405;
	const HTTP_NOT_ACCEPTABLE = 406;
	const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
	const HTTP_REQUEST_TIMEOUT = 408;
	const HTTP_CONFLICT = 409;
	const HTTP_GONE = 410;
	const HTTP_LENGTH_REQUIRED = 411;
	const HTTP_PRECONDITION_FAILED = 412;
	const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
	const HTTP_REQUEST_URI_TOO_LONG = 414;
	const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
	const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const HTTP_EXPECTATION_FAILED = 417;
	const HTTP_I_AM_A_TEAPOT = 418;
	const HTTP_UNPROCESSABLE_ENTITY = 422;
	const HTTP_LOCKED = 423;
	const HTTP_FAILED_DEPENDENCY = 424;
	const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;
	const HTTP_UPGRADE_REQUIRED = 426;
	const HTTP_PRECONDITION_REQUIRED = 428;
	const HTTP_TOO_MANY_REQUESTS = 429;
	const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
	const HTTP_INTERNAL_SERVER_ERROR = 500;
	const HTTP_NOT_IMPLEMENTED = 501;
	const HTTP_BAD_GATEWAY = 502;
	const HTTP_SERVICE_UNAVAILABLE = 503;
	const HTTP_GATEWAY_TIMEOUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;
	const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;
	const HTTP_INSUFFICIENT_STORAGE = 507;
	const HTTP_LOOP_DETECTED = 508;
	const HTTP_NOT_EXTENDED = 510;
	const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

	public static $statusTexts = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			208 => 'Already Reported',
			226 => 'IM Used',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Reserved',
			307 => 'Temporary Redirect',
			308 => 'Permanent Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			425 => 'Reserved for WebDAV advanced collections expired proposal',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates (Experimental)',
			507 => 'Insufficient Storage',
			508 => 'Loop Detected',
			510 => 'Not Extended',
			511 => 'Network Authentication Required'
	);

	private $content;
	private $cacheControl = 'Cache-Control: ';
	private $cacheControlArg = array();
	private $expiration = null;
	private $request;
	private $statusCode;
	private $protocoleVersion;
	private $statusText;
	const PRIVATECACHE = 'private';
	const PUBLICCACHE = 'public';
	function __construct($content=null, $statusCode = 200){
		$this->content = $content;

		$this->setStatusCode($statusCode);
		$this->protocoleVersion = '1.0';
		$this->request = BeanLoader::getInstance()->getBean('arhframe.request');
		$this->setCacheControlFromConfig();
	}
	private function setCacheControlFromConfig(){
		$cacheControl = (array)Config::getInstance()->httpcache;
		if(!empty($cacheControl['no-cache'])){
			$this->setNoCache();
		}
		if(!empty($cacheControl['no-store'])){
			$this->setNoStore();
		}
		if(!empty($cacheControl['no-transform'])){
			$this->setNoTransform();
		}
		if(!empty($cacheControl['must-revalidate'])){
			$this->setMustRevalidate();
		}
		if(!empty($cacheControl['proxy-revalidate'])){
			$this->setProxyRevalidate();
		}
		if(!empty($cacheControl['only-if-cached'])){
			$this->setOnlyIfCached();
		}
		if(!empty($cacheControl['visibility'])){
			$this->setVisibility($cacheControl['visibility']);
		}
		if(!empty($cacheControl['max-stale'])){
			$this->setMaxStale($cacheControl['max-stale']);
		}
		if(!empty($cacheControl['proxy-max-age'])){
			$this->setProxyMaxAge($cacheControl['proxy-max-age']);
		}
		if(!empty($cacheControl['max-age'])){
			$this->setMaxAge($cacheControl['max-age']);
		}
		if(!empty($cacheControl['min-fresh'])){
			$this->setMinFresh($cacheControl['min-fresh']);
		}
		if(!empty($cacheControl['expiration'])){
			$this->setExpiration($cacheControl['expiration']);
		}
	}
	public function setContent($content){
		$this->content = $content;
	}
	public function getContent(){
		$this->createHeader();
		return $this->content;
	}
	public function getReponseCode(){
		return http_response_code();
	}
	public function setResponseCode($code){
		http_response_code($code);
	}

	public function setStatusCode($statusCode){
		$this->statusCode = $statusCode;
		$this->statusCode = (int) $statusCode;
		if ($this->statusCode < 100 || $this->statusCode >= 600) {
			throw new ArhframeException(sprintf('The HTTP status code "%s" is not valid.', $this->statusCode));
		}

		if (null === $text) {
			$this->statusText = isset(self::$statusTexts[$statusCode]) ? self::$statusTexts[$statusCode] : '';

			return $this;
		}

		if (false === $text) {
			$this->statusText = '';

			return $this;
		}

		$this->statusText = $text;

		return $this;
	}
	public function getStatusCode(){
		return $this->statusCode;
	}
	public function getHtmlPage(){
		return new \Wa72\HtmlPageDom\HtmlPage($this->content);
	}
	public function addHeader($header, $replace=null, $http_response_code=null){
		header($header, $replace, $http_response_code);
	}
	public function createHeader(){
		$this->addHeader(sprintf('HTTP/%s %s %s', $this->protocoleVersion, $this->statusCode, $this->statusText), true, $this->statusCode);

		$nbCacheControlArg = count($this->cacheControlArg);
		if($nbCacheControlArg == 0){
			return;
		}
		$i=0;
		$header = $this->cacheControl;
		foreach ($this->cacheControlArg as $arg){
			if($i>0){
				$header .= ', ';
			}
			$header .= $arg;
			$i++;
		}
		$this->addHeader($header, true);
		if(empty($this->expiration)){
			return;
		}
		$this->addHeader($this->expiration, true);
	}
	public function noCacheControl(){
		$this->addHeader("Cache-Control: no-cache, must-revalidate");
		$this->addHeader("Pragma: no-cache");
		$this->addHeader("Expires: ". date('r', strtotime('-1 days')));
	}
	public function addArgCacheControl($key, $value, $remove=false){
		if($remove){
			unset($this->cacheControlArg[$key]);
			return;
		}
		$this->cacheControlArg[$key] = $value;
	}
	public function setNoCache($remove=false){
		$this->addArgCacheControl('no-cache', 'no-cache', $remove);
	}
	public function setNoStore($remove=false){
		$this->addArgCacheControl('no-store', 'no-store', $remove);
	}
	public function setVisibilityPrivate($remove=false){
		$this->setVisibility(Response::PRIVATECACHE, $remove);
	}
	public function setVisibilityPublic($remove=false){
		$this->setVisibility(Response::PUBLICCACHE, $remove);
	}
	public function setVisibility($visibility, $remove=false){
		if($visibility!=Response::PUBLICCACHE && $visibility!=Response::PRIVATECACHE){
			return;
		}
		$this->addArgCacheControl('visibility', $visibility, $remove);
	}
	public function setNoTransform($remove=false){
		$this->addArgCacheControl('no-transform', 'no-transform', $remove);
	}
	private function formatAge($age){
		if(is_numeric($age)){
			return $age;
		}
		$age = (strtotime($age)-time());
		if($age<0 || empty($age)){
			throw new ArhframeException("Age must be in strtotime format or numeric and >0");
		}
		return $age;
	}
	public function setMaxAge($age, $remove=false){
		try {
			$age = $this->formatAge($age);
		} catch (ArhframeException $e) {
			throw new ArhframeException("cache control max age problem ". $e->getMessage());
		}

		$this->addArgCacheControl('max-age', 'max-age='. $age, $remove);
	}
	public function setMaxStale($age, $remove=false){
	try {
			$age = $this->formatAge($age);
		} catch (ArhframeException $e) {
			throw new ArhframeException("cache control max stale problem ". $e->getMessage());
		}
		$this->addArgCacheControl('max-stale', 'max-stale='. $age, $remove);
	}
	public function setProxyMaxAge($age, $remove=false){
	try {
			$age = $this->formatAge($age);
		} catch (ArhframeException $e) {
			throw new ArhframeException("cache control proxy max age problem ". $e->getMessage());
		}
		$this->addArgCacheControl('proxy-max-age', 's-maxage='. $age, $remove);
	}
	public function setMustRevalidate($remove=false){
		$this->addArgCacheControl('must-revalidate', 'must-revalidate', $remove);
	}
	public function setProxyRevalidate($remove=false){
		$this->addArgCacheControl('proxy-revalidate', 'proxy-revalidate', $remove);
	}
	public function setOnlyIfCached($remove=false){
		$this->addArgCacheControl('only-if-cached', 'only-if-cached', $remove);
	}

	public function setMinFresh($age, $remove=false){
		try {
			$age = $this->formatAge($age);
		} catch (ArhframeException $e) {
			throw new ArhframeException("cache control min fresh problem ". $e->getMessage());
		}
		$this->addArgCacheControl('min-fresh', 'min-fresh='. $age, $remove);
	}
	public function setExpiration($age, $remove=false){
		try {
			$age = $this->formatAge($age);
		} catch (ArhframeException $e) {
			throw new ArhframeException("cache control expiration problem ". $e->getMessage());
		}
		if($remove){
			$this->expiration = null;
			return;
		}
		$age = $age+time();
		$this->expiration = "Expires: ". date('r', $age);
	}
	public function getProtocoleVersion() {
		return $this->protocoleVersion;
	}
	public function setProtocoleVersion($protocoleVersion) {
		if($protocoleVersion!='1.0' && $protocoleVersion!='1.1'){
			return;
		}
		$this->protocoleVersion = $protocoleVersion;
		return $this;
	}

}