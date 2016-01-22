<?php


//configuration for send email
//this is the global configuration, you can set locally at wherever u want.


$config['protocol'] = 'smtp';
$config['smtp_host'] = 'ssl://smtp.gmail.com'; //change this
$config['smtp_user'] = 'info@corneaentertainment.com'; //email id of gmail account
$config['smtp_pass'] = 'C0rnea19)#'; //password of email id
$config['mailtype'] = 'html';
$config['charset'] = 'iso-8859-1';
$config['wordwrap'] = TRUE;
$config['newline'] = "\r\n"; //use double quotes to comply with RFC 822 standard
//$config['SMTPAuth']=TRUE;//this is optional
$config['smtp_port'] = '465';