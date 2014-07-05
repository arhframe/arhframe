<?php
package('arhframe.secure.authentification');
/**
* 
*/
interface IAuthentifier{
	function authentificate();
	function isAuthentifier();
	function setUsers($users);
}