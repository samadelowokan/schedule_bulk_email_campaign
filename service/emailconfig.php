<?php
require __DIR__ . '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;


function htmlContent($title, $msg)
{
    return ' 
    <html> 
    <head> 
        <title>' . $title . '</title> 
    </head> 
    <body> 
        ' . $msg . '
    </body> 
    </html>';
}

function getCredentialConfiguration($account)
{
   try {
    $accounts = [
        "Trustbook" => [
            "MAIL_HOST" => "mail.trustbook.pro",
            "MAIL_PORT" => 465,
            "MAIL_USERNAME" => "test@trustbook.pro",
            "MAIL_PASSWORD" => "5YDGt11M61KG",
            "MAIL_ENCRYPTION" => "ssl",
            "FROM_EMAIL" => "test@trustbook.pro",
            "FROM_NAME" => "TrustBook (Via TrustPilot)"
        ],	
        "Account2" => [
            "MAIL_HOST" => "",
            "MAIL_PORT" => 465,
            "MAIL_USERNAME" => "",
            "MAIL_PASSWORD" => "",
            "MAIL_ENCRYPTION" => "ssl",
            "FROM_EMAIL" => "",
            "FROM_NAME" => ""
        ]
    ];

    return (!empty($accounts[$account]) ? $accounts[$account] : null);
   } catch (\Throwable $th) {
    return null;
   }
}

function sendAllEmail($account,$toEmail, $toName, $subject, $msg)
{

  try {
    $mail = new PHPMailer(true);

    $accountConfig = getCredentialConfiguration($account);

    if(empty($accountConfig)) {
        throw new Exception('error accountConfig');
    }

    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->CharSet = 'UTF-8';
    $mail->Username   = $accountConfig['MAIL_USERNAME'];
    $mail->Password   = $accountConfig['MAIL_PASSWORD'];
    $mail->SMTPSecure = $accountConfig['MAIL_ENCRYPTION'];
    $mail->Host = $accountConfig['MAIL_HOST'];
    $mail->Port = $accountConfig['MAIL_PORT'];

    $mail->setFrom($accountConfig['FROM_EMAIL'], $accountConfig['FROM_NAME']);
    $mail->addAddress($toEmail, (!empty($toName) ? $toName : ""));

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    =  htmlContent($subject, $msg);
    $mail->AltBody =  $msg;

    $mail->send();
    return true;
  } catch (\Throwable $th) {
    throw new Exception('error');
  }
}
