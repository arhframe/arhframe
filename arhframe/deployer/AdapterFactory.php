<?php
use League\Flysystem\Filesystem;
/**
* 
*/
class AdapterFactory
{
	private $arrayConfig;
	private $instance;
	function __construct($type, $arrayConfig){
		$this->arrayConfig = $arrayConfig;
		$type = strtolower($type);
		switch ($type) {
			case 'sftp':
				$this->instanciateSftp();
				break;
			case 'ftp':
				$this->instanciateFtp();
				break;
			case 'zip':
				$this->instanciateZip();
				break;
			default:
				$this->instanciateLocal();
				break;
		}
	}
	private function instanciateSftp(){
		$this->instance = new League\Flysystem\Adapter\Sftp($this->arrayConfig);
	}
	private function instanciateFtp(){
		$this->instance = new League\Flysystem\Adapter\Ftp($this->arrayConfig);
	}
	private function instanciateLocal(){
		$this->instance = new League\Flysystem\Adapter\Local($this->arrayConfig['root']);
	}
	private function instanciateZip(){
		$this->instance = new League\Flysystem\Adapter\Zip($this->arrayConfig['root'].'/deploy.zip');
	}
	public function getInstance(){
		
		return $this->instance;
	}
}