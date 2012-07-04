<?php

require 'Mail.php';

function mailing($emails, $subject, $mensaje){

	if( is_string( $emails ) ) $emails = array($emails);
    $emails[] = 'gafeman@gmail.com';

	$apiKey = '';
	$secretKey = '';

	$headers = array ('From' => 'Betabeers <contacto@betabeers.com>',
			  'Subject' => $subject,'Content-type' => 'text/html; charset=UTF-8');
	
	$smtp = Mail::factory('smtp',
			      array ('host' => 'ssl://in.mailjet.com',
	 		      	     'port' => 465,
	 		             'auth' => true,
	 		             'username' => $apiKey,
	 		             'password' => $secretKey));
	 		             
	return $smtp->send($emails, $headers, $mensaje );
}