<?php

/*
  Simple PHP script to POST your static website forms to
  ------------------------------------------------------

  You need six HTML form fields to submit the form data to the PHP script. You can use hidden fields to set the EMail subject, the receiver address and the redirect URL after form submission.

  1.  subject      EMail subject.
  2.  receiver     EMail address the sumitted form will be sent to.
                   Must be in the allowed_receiver_emails list.
  3.  redirect_to  Web address for browser forwarding after form submission.
                   Must be in the allowed_redirects list.

  4.  name         Name of the user submitting the form
  5.  email        EMail of the user sumitting the form
  6.  body         Text the user wants to send via the form

  You also need a form action that points to the `php_form.php` script from this repo.

  Of course you can use the hidden fields as normal ones to, for example let the user set the EMail subject.
**/

// Configuration
//--------------------------------------------------------------------

// START setup
$config = array(
  'allowed_receiver_emails' => array('your-email@example.com')
  'allowed_redirects'       => array('https://www.example.com/contact-success.html'),
  'allowed_hosts'           => array('localhost', 'example.com'),
  'smtp_host'               => 'smtp.example.com',
  'smtp_sender'             => 'no-reply@example.com',
  'smtp_password'           => 'your_smtp_password_here',
  'smtp_username'           => 'your_smtp_username_here',
  'smtp_port'               => '587',
  'turnstile_enabled'       => false,
  'turnstile_secret'        => 'your_turnstile_secret_here',
  'turnstile_url'           => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
);
// END setup

// Abort if request isn't POST
//--------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  echo 'Invalid method (only POST works)';
  exit();
}

// If Turnstile is enabled, check the response and redirect if needed
//--------------------------------------------------------------------

if ($config['turnstile_enabled']) {

  if (!isset($_POST['cf-turnstile-response']) || empty($_POST['cf-turnstile-response'])) {
    echo 'No Turnstile response found';
    exit();
  }

  $turnstile_response = htmlspecialchars($_POST['cf-turnstile-response'], ENT_QUOTES, 'UTF-8');

  // Send the Turnstile response to the Turnstile API
  $post_fields = http_build_query([
    'secret'   => $config['turnstile_secret'],
    'response' => $turnstile_response,
    'remoteip' => $_SERVER['REMOTE_ADDR'], // Optional but recommended
  ]);

  $ch = curl_init($config['turnstile_url']);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
  $response = curl_exec($ch);

  if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    // Log the error
    error_log('Curl error: ' . $error);
    echo 'Verification failed. Please try again later.';
    exit();
  }

  curl_close($ch);

  $response_data = json_decode($response);

  // If the response is not successful, redirect to current page with error parameter
  if (!$response_data || $response_data->success != true) {
    $current_url = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
    header("Location: $current_url?error=turnstile_unsuccessful");
    exit();
  }
}

// Make sure to only accept requests from allowed domains
//--------------------------------------------------------------------

if (!isset($_SERVER['HTTP_REFERER'])) {
  echo 'No HTTP referer set';
  exit();
}

$referer = parse_url($_SERVER['HTTP_REFERER']);
$referer_host = strtolower($referer['host']);
$referer_host = str_replace('www.', '', $referer_host);

if (!in_array($referer_host, $config['allowed_hosts'])) {
  echo 'You are not allowed to use this page';
  exit();
}

// Extract form data from request and validate
//--------------------------------------------------------------------

function sanitize_input($data) {
  return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function is_valid_email($email) {
  return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitize_header($value) {
  return trim(str_replace(array("\r", "\n"), '', $value));
}

$form = array(
  'receiver'    => isset($_POST['receiver']) ? sanitize_input($_POST['receiver']) : '',
  'subject'     => isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '',
  'redirect_to' => isset($_POST['redirect_to']) ? sanitize_input($_POST['redirect_to']) : '',
  'email'       => isset($_POST['email']) ? sanitize_input($_POST['email']) : '',
  'name'        => isset($_POST['name']) ? sanitize_input($_POST['name']) : '',
  'body'        => isset($_POST['body']) ? sanitize_input($_POST['body']) : ''
);

// Validate form fields
if (empty($form['name']) || empty($form['email']) || empty($form['body']) || empty($form['receiver'])) {
  echo 'All fields are required.';
  exit();
}

// Validate email addresses
if (!is_valid_email($form['email'])) {
  echo 'Invalid email address.';
  exit();
}

if (!in_array($form['receiver'], $config['allowed_receiver_emails'])) {
  echo 'Invalid receiver email.';
  exit();
}

// Validate redirect_to
if (!in_array($form['redirect_to'], $config['allowed_redirects'])) {
  $form['redirect_to'] = '/thank-you.html'; // Default redirect
}

// Set receiver email
$receiver_email = $form['receiver'];

// Prepare email headers and message
//--------------------------------------------------------------------

$email_subject = sanitize_header($referer_host . ': ' . $form['subject']);

$email_sender_name = sanitize_header($form['name']);
$email_sender_email = $config['smtp_sender']; // Must be from your domain

$reply_to_email = $form['email']; // User's email

// Sanitize the email body
$email_body_text = $form['name'] . ' (' . $form['email'] . "):\n\n" . $form['body'];

$email_body_html = '
<html>
  <head>
    <title>' . $email_subject . '</title>
    <style>
      body {
        font-family: -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, Ubuntu;
        font-size: 16px;
      }
    </style>
  </head>
  <body>
    <h4>' . sanitize_input($form['name']) . ' (' . sanitize_input($form['email']) . '):</h4>
    <p>' . nl2br($form['body']) . '</p>
  </body>
</html>';

// Build headers
$headers = array();
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/html; charset=UTF-8';
$headers[] = 'From: ' . $email_sender_name . ' <' . $email_sender_email . '>';
$headers[] = 'Reply-To: ' . $reply_to_email;
$headers[] = 'X-Mailer: PHP/' . phpversion();

// Send the email
//--------------------------------------------------------------------

$success = mail($receiver_email, $email_subject, $email_body_html, implode("\r\n", $headers));

if ($success) {
  // Redirect to thank you page
  header('Location: ' . $form['redirect_to']);
  exit();
} else {
  echo 'Message could not be sent.';
}

?>
