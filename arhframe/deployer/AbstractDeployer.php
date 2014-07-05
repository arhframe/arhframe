<?php
package('arhframe.deployer');
abstract class AbstractDeployer
{
	
	private $path;	
	private $ftp;
	function __construct($host, $port, $user, $password, $path){
		
	}
	public abstract function deploy($files);
}