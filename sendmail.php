<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (count($argv) != 8) {
	echo "Supply all parameters to script\r\n";
	echo "Usage: php sendmail.php addresses mailbody textbody domain subject from host\r\n";
	die();
}

$filename = $argv[1];
$mailfile = $argv[2];
$mailtext = $argv[3];
$domain = $argv[4];
$subject = $argv[5];
$from = $argv[6];
$host = $argv[7];

$contents = file($filename);
$mailBody = file_get_contents($mailfile);
$textBody = file_get_contents($mailtext);

foreach($contents as $line) 
{
	$token = openssl_random_pseudo_bytes(16);
	$token = bin2hex($token);
	//echo $token;

    $address = trim($line);

	if (preg_match('/^#/', $address)) {
		echo "Skip: $address\r\n";
		continue;
	}

	echo "Send to: $address\r\n";
	//continue;

	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions

	try {
		//Server settings
		$mail->SMTPDebug = 2;                                 // Enable verbose debug output
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to

		//Recipients
		$mail->setFrom("${from}@${domain}");
		$mail->addAddress($address); 
		$mail->CharSet = 'UTF-8';
		$mail->XMailer = ' ';
		$mail->MessageID = "<${token}@${host}.${domain}>";

		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $mailBody;
		$mail->AltBody = $textBody;
		$mail->send();
		echo "Message has been sent\r\n";
	} catch (Exception $e) {
		echo sprintf("Message could not be sent. Mailer Error: %s\r\n%s\r\n", $mail->ErrorInfo, $e->getMessage());
		
	}

	sleep(1);
}

echo "Done\r\n";
