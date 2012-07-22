<?php
/**
 * Security functions
 * 
 * a number of functions to test values.
 * 
 * @author James Hayward <jhayward1980@gmail.com>
 * @version 1.0
 */

define("ERROR_TEXT", 'must only be letters, numbers and \'.,?!"%£&()-/');
define("ERROR_EMAIL", 'must be a valid email address');
define("ERROR_NUM", 'must only be numbers, brackets and spaces');
define("ERROR_DATE", 'must be of the format dd/mm/yyyy');
 
/**
 * isText
 *
 * Checks that the input is normal text
 */
function isText($input, $allowNull = false) {
	if ($input == "" and $allowNull == true) {
		return true;
	}
	elseif ($input == "") {
		return false;
	}
	elseif (preg_match("/^[\w\s\'\.\,\?\!\%\£\&\(\)\"\-\/\+\=\<\>]+$/", $input)) {
		return true;
	}
	else {
		return false;
	}
}

function isEmail($input) {
	if ($input == "") {
		return false;
	}
	elseif (preg_match("/^[a-zA-Z0-9\.-_]+\@[a-zA-Z0-9\.-]+\.[a-zA-Z\.]+$/i", $input)) {
		return true;
	}
	else {
		return false;
	}
}

function isNumber($input, $allowNull = false) {
	if ($input == "" and $allowNull == true) {
		return true;
	}
	elseif ($input == "") {
		return false;
	}
	elseif (preg_match("/^[\d\(\)\+\. ]+$/", $input)) {
		return true;
	}
	else {
		return false;
	}
}

function isDate($input, $allowNull = false) {
	if ($input == "" and $allowNull == true) {
		return true;
	}
	elseif ($input == "") {
		return false;
	}
	elseif (preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $input)) {
		return true;
	}
	else {
		return false;
	}
}

?>
