<?php
package('arhframe.deployer');
/**
* 
*/
class SFtpDeployer extends AbstractDeployer
{
	private $path;	
	private $sftp;
	function __construct($host, $port, $user, $password, $path){
		$this->sftp = new Net_SFTP($host, $port);
		if (!$this->sftp->login($user, $password)) {
		    throw new Exception("logging failed");
		}
		$this->path = $path;
	}
	public function deploy($files){
		foreach ($files as $file) {
			$this->sftp->mkdir($this->path . dirname($file), -1, true);
			$this->sftp->put($this->path . $file, __DIR__ .'/../..'. $file, NET_SFTP_LOCAL_FILE);
		}
	}

}