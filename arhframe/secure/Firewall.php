<?php
/**
* 
*/
class Firewall
{
	private $firewalls;
	function __construct()
	{
		
	}

	/*
	@Required
	 */
	public function setFirewalls(array $firewalls){
		$this->firewalls = $firewalls;
	}
}