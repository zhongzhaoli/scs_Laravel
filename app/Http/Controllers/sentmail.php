<?php
//Import PHPMailer classes into the global namespace
namespace App\Http\Controllers;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('PHPMailer/PHPMailer/Exception.php');
require_once('PHPMailer/PHPMailer/OAuth.php');
require_once('PHPMailer/PHPMailer/PHPMailer.php');
require_once('PHPMailer/PHPMailer/POP3.php');
require_once('PHPMailer/PHPMailer/SMTP.php');

Class sentmail {
    public function __construct($mes,$content) {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.qq.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = '525966315@qq.com';                 // SMTP username
            $mail->Password = 'idviyxemksgkbiic';                                 // SMTP password  You email Authorization code
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('525966315@qq.com', '云屯务集信息服务有限公司');
            $mail->addAddress($mes->email,$mes->name);     // Add a recipient
            //$mail->addAddress('ellen@example.com');               // Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = '云屯务集审核结果';
            $mail->Body    = $content;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
