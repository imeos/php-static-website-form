<?php

/*
  Simple PHP script to POST your static website forms to
  ------------------------------------------------------

  Required normal fields:
    name        - name of form submitter
    email       - EMail address of the form submitter, will be used as EMail reply target
    body        - the message content

  Required hidden fields
    subject     - the EMail subject
    receiver    - the EMail address the sumitted form will be sent to
    redirect_to - web address for browser forwarding after form submission

  Of course you can use the hidden fields as normal ones to, e.g. let the user set the EMail subject
**/

require 'Mail.php';      // Use the PEAR Mail package
require 'Mail/mime.php'; // Enable HTML mails


// Configuration
//--------------------------------------------------------------------

// START setup
$config = array(
  'smtp_sender'   => 'no-reply@example.com',
  'smtp_host'     => 'mail.example.com',
  'smtp_username' => 'foo',
  'smtp_password' => 'bar',
  'smtp_port'     => '587',
  'allowed_hosts' => array('localhost', 'example.com')
);
// END setup


// Abort if request isn't POST
//--------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  echo 'Invalid method (only POST works)';
  exit(); //Stop running the script
}


// Make shure to only accept requests from allowed Domains
//--------------------------------------------------------------------

$referer = parse_url($_SERVER['HTTP_REFERER']);
$referer_host = strtolower($referer['host']);
$referer_host = str_replace('www.', '', $referer_host);

if(!in_array( $referer_host, $config['allowed_hosts'])) {
  echo 'you are not allowed use this page';
  exit(); //Stop running the script
}


// Extract form data from request
//--------------------------------------------------------------------

$form = array(
  'receiver'    => htmlspecialchars($_POST['receiver']),
  'subject'     => htmlspecialchars($_POST['subject']),
  'redirect_to' => htmlspecialchars($_POST['redirect_to']),
  'email'       => htmlspecialchars($_POST['email']),
  'name'        => htmlspecialchars($_POST['name']),
  'body'        => htmlspecialchars($_POST['body'])
);


// Send mail
//--------------------------------------------------------------------

$email_sender = $form['name'].' ('.$form['email'].')';

$email_text = $email_sender . ':'
  ."\n\n\n"
  .$form['body'];

$email_html = '
  <html>
    <head>
      <title>'.$form['subject'].'</title>
      <style>
        body {
          font-family: font-family: -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, Ubuntu;
          font-size: 16px;
        }
      </style>
    </head>
    <body>
      <h4>'. $email_sender.':</h4>
      '.nl2br($form['body']).'
    </body>
  </html>';


$headers = array(
  'From' => $form['name'].'<'.$config['smtp_sender'].'>',
  'Reply-To' => $form['email'],
  'To' => $form['receiver'],
  'Subject' => $referer_host.': '.$form['subject'],
  'Content-Type'  => 'text/html; charset=UTF-8'
);

$mime_params = array(
  'text_encoding' => '7bit',
  'text_charset'  => 'UTF-8',
  'html_charset'  => 'UTF-8',
  'head_charset'  => 'UTF-8'
);

// Creating the Mime message
$mime = new Mail_mime();

// Setting the body of the email
$mime->setTXTBody($email_text);
$mime->setHTMLBody($email_html);

$body = $mime->get($mime_params);
$headers = $mime->headers($headers);

// Sending the email
$mail =& Mail::factory('smtp', array (
  'host' => $config['smtp_host'],
  'port' => $config['smtp_port'],
  'auth' => true,
  'username' => $config['smtp_username'],
  'password' => $config['smtp_password']
));
$mail->send($form['receiver'], $headers, $body);

if (PEAR::isError($mail)) {
  echo('<p>'.$mail->getMessage().'</p>');
} else {
  // Forward to to 'thanks for contacting us' page or something
  header('Location: '.$form['redirect_to']);
}

?>
