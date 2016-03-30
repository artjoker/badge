<?php

  namespace Slim;


  class Mailer
  {

    function __construct ()
    {
      require PATH_CORE . "PHPMailer" . DS . "PHPMailerAutoload.php";
    }

    /**
     * [send description]
     * @param  string $to      reciever email
     * @param  string $subject email subject
     * @param  string $body    email html body
     * @param  array  $ph      [description]
     * @return [type]          [description]
     */
    function send ($to, $subject, $body, $ph = array())
    {
      // replace ph in body
      if (0 < count($ph)) {
        foreach ($ph as $key => $value) {
          $subject = str_replace("{" . $key . "}", $value, $subject);
          $body    = str_replace("{" . $key . "}", $value, $body);
        }
      }
      file_put_contents(PATH_CACHE . "email.html", $body);
      $mail          = new \PHPMailer();
      $mail->charSet = "UTF-8";

      if (MAIL_TYPE == "sendmail")
        $mail->IsSendmail();
      else {
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth  = true;
        if (MAIL_SECURE != '') $mail->SMTPSecure = MAIL_SECURE;
        $mail->Host     = MAIL_HOST;
        $mail->Port     = MAIL_PORT;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
      }
      
      $mail->Subject  = $subject;
      $mail->SetFrom(MAIL_USER);

      $mail->AddAddress($to);
      $mail->MsgHTML($body);
      if (!$mail->Send())
        echo $mail->ErrorInfo;
    }
  }
