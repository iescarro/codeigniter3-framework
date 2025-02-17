<?php

/**
 * CodeIgniter3
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2024, CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter3
 * @author	CodeIgniter3 Team
 * @copyright	Copyright (c) 2024, CodeIgniter3 Team (https://github.com/iescarro/codeigniter3-framework)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://github.com/iescarro/codeigniter3-framework
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') or exit('No direct script access allowed');

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require_once(ROOT_PATH . '/vendor/autoload.php');

class CI_Mail {} // HACK: We don't need this

class Mail
{
  var $mailers;

  function mailer($mailer)
  {
    $this->mailers[] = $mailer;
  }

  // static function db()
  // {
  //   $mailer = (new DbMailer());
  //   return $mailer;
  // }

  static function to($to)
  {
    $mailers = self::get_mailers();
    $obj = &get_instance();
    $default_mailer = $obj->config->item('default_mailer');
    $config = $mailers[$default_mailer];
    $mailer = ucfirst($default_mailer) . 'Mailer';
    $mailer = new $mailer();
    $mailer->settings($config['smtp_host'], $config['smtp_port'], $config['smtp_user'], $config['smtp_pass'])
      ->from($config['from'])
      ->to($to);
    return $mailer;
  }

  private static function get_mailers()
  {
    $obj = &get_instance();
    $obj->load->config('mail');
    $mailers = $obj->config->item('mailers');
    return $mailers;
  }
}

interface IMailer
{
  function send($message);
}

interface IMailMessage {}

class MailMessage implements IMailMessage
{
  var $subject;
  var $body;

  function __construct($subject = '', $body = '')
  {
    $this->subject = $subject;
    $this->body = $body;
  }
}

class SmtpMailer implements IMailer
{
  var $mail;

  function __construct()
  {
    $this->mail = new PHPMailer(true);

    $this->mail->isSMTP(); //Send using SMTP
    $this->mail->SMTPAuth = true; // Enable SMTP authentication

    $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption

    $this->mail->SMTPOptions = array(
      'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      )
    );

    // $mail->addAddress('ellen@example.com'); //Name is optional
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    // Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz'); // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); // Optional name
  }

  function settings($smtp_host, $smtp_port, $smtp_user, $smtp_pass)
  {
    // Server settings
    $this->mail->Host = $smtp_host;     // 'smtp.example.com';                     //Set the SMTP server to send through
    $this->mail->Port = $smtp_port;     // 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    $this->mail->Username = $smtp_user; // 'user@example.com';                     //SMTP username
    $this->mail->Password = $smtp_pass; // 'secret';                               //SMTP password
    return $this;
  }

  function debug()
  {
    $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
    return $this;
  }

  function html($html = true)
  {
    $this->mail->isHTML($html);
    return $this;
  }

  function from($from)
  {
    if (is_array($from)) {
      $this->mail->setFrom($from['email'], $from['alias']);
    } else {
      $this->mail->setFrom($from);
    }
    return $this;
  }

  function to($to)
  {
    // Recipients
    if (is_array($to)) {
      $this->mail->addAddress($to['email'], $to['alias']);
    } else {
      $this->mail->addAddress($to); // 'joe@example.net', 'Joe User'); // Add a recipient
    }
    return $this;
  }

  function subject($subject)
  {
    $this->mail->Subject = $subject; // 'Here is the subject';
    return $this;
  }

  function body($body)
  {
    $this->mail->Body = $body; //'This is the HTML message body <b>in bold!</b>';
    // $this->mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    return $this;
  }

  function send($message = '')
  {
    if (is_string($message)) {
      $this->subject($message)
        ->body($message);
    } else if ($message instanceof IMailMessage) {
      $this->subject($message->subject)
        ->body($message->body);
    }

    $this->mail->send();
  }
}

class DbMailer implements IMailer
{
  function send($message)
  {
    $email = array();
    if (is_string($message)) {
      $email = array(
        'job' => 'EmailJobString'
      );
    } else if ($message instanceof IMailMessage) {
      $email = array(
        'job' => 'EmailJobIMessage'
      );
    }

    $obj = &get_instance();
    $obj->load->database();
    print_pre($email);
    $obj->db->insert('jobs', $email);
  }
}
