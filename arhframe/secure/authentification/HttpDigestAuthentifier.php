<?php
package('arhframe.secure.authentification');
import('arhframe.Request');
import('arhframe.exception.*');
/**
* 
*/
class HttpBasicAuthentifier implements IAuthentifier
{
	private $authentifier;
	private $request;
	private $realm;
	function __construct(){

	}
	function authentificate(){
		$this->authentifier = false;
		if(empty($this->realm)){
			throw new ArhframeException("No realm set");
		}
		
		$isSendHttp = $this->request->getServerRequest('PHP_AUTH_USER');
		if($isSendHttp){
			$this->authentifier = true;
		}else{
			header('WWW-Authenticate: Basic realm="'. $this->realm .'"');
    		header('HTTP/1.0 401 Unauthorized');
		}
	}
	function isAuthentifier(){
		return $this->authentifier;
	}
	/*
	@Required
	 */
	public function setRequest(Request $request){
		$this->request = $request;
	}
	public function setRealm($realm){
		$this->realm = $realm;
	}
}