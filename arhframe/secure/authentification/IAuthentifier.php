<?php
package('arhframe.secure.authentification');

/**
 *
 */
interface IAuthentifier
{
    function authentificate();

    function isAuthentifier();

    function setProvider($provider);

    function getUser();

    function logOut();
}