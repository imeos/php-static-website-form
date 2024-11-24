# Simple PHP script to POST your static website forms to

You can use this simple PHP script on your server to POST your contact forms to and forward the form data to your Inbox, instead of relying on services like formspree to do so for you.

```
╭──────────────╮  ╭──────────────╮  ╭──────────────╮  ╭──────────────╮
│ ◎ ○ ○ ░░░░░░░│  │ ◎ ○ ○ ░░░░░░░│  │ ◎ ○ ○ ░░░░░░░│  │ ◎ ○ ○ ░░░░░░░│
├──────────────┤  ├──────────────┤  ├──────────────┤  ├──────────────┤
│              │  │              │  │              │  │              │
│    Static    │  │    Static    │  │              │  │    Static    │
│    Site 1    │  │    Site 2    │  │      …       │  │    Site n    │
│              │  │              │  │              │  │              │
└──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘
        │                 │                 │                 │
        └─────────────────┴───────┬─────────┴─────────────────┘
                                  │
                                  ▼
                           ┌────────────┐
                           │@@@@@@@@@@@@│
                           │@ PHP Form @│
                           │@  Mailer  @│
                           │@@@@@@@@@@@@│
                           └────────────┘
                                  │
        ┌────────────┬────────────┼────────────┬────────────┐
        │            │            │            │            │
        ▼            ▼            ▼            ▼            ▼
  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
  ├──────────┤ ├──────────┤ ├──────────┤ ├──────────┤ ├──────────┤
  │ Inbox 1  │ │ Inbox 2  │ │ Inbox 3  │ │    …     │ │ Inbox n  │
  │          │ │          │ │          │ │          │ │          │
  └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘
```


## Requirements

- PHP 5.6+ enabled web server.
- An SMTP-enabled email account for sending emails.
- cURL extension enabled in PHP for Cloudflare Turnstile verification (optional).

Most shared web hosting providers offer these features by default.


## Installation

### 1. Copy the Script to Your Server

Download the `form_mailer.php` script and upload it to your PHP-enabled web server.

### 2. Configure the Script

Open the `form_mailer.php` file and locate the configuration section:

```php
$config = array(
  'allowed_receiver_emails' => array('your-email@example.com'),
  'allowed_redirects'       => array('https://www.example.com/contact-success.html'),
  'allowed_hosts'           => array('localhost', 'example.com'),
  'smtp_sender'             => 'no-reply@example.com',       // Sender email address (must be from your domain)
  'turnstile_enabled'       => false,                        // Set to true if using Turnstile
  'turnstile_secret'        => 'your_turnstile_secret_here', // Your Turnstile secret key
  'turnstile_url'           => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
);
```

**Configuration Options:**

- `allowed_receiver_emails`: An array of email addresses that are allowed to receive form submissions. Replace 'your-email@example.com' with your actual email address.
- `allowed_redirects`: An array of URLs that users can be redirected to after form submission. Ensure these URLs are on your domain.
- `allowed_hosts`: An array of domains allowed to submit forms to this script. This should include your website's domain.
- `smtp_sender`: The email address that will appear in the From header of the emails sent by this script. It should be an email address from your domain.
- Cloudflare Turnstile Configuration:
  - `turnstile_enabled`: Set this to true if you want to enable Cloudflare Turnstile for bot protection.
  - `turnstile_secret`: Your secret key for Cloudflare Turnstile. Obtain this from your Cloudflare dashboard.
  - `turnstile_url`: The verification URL for Turnstile. The default is correct; change only if instructed by Cloudflare.

### 3. Set Up Your Contact Form

Create a contact form on your website that submits to the form_mailer.php script.

Example Form:

```html
<form action="https://your-server.com/form_mailer.php" method="post">
  <!-- Hidden fields -->
  <input name="subject" type="hidden" value="Contact Form Submission">
  <input name="redirect_to" type="hidden" value="https://www.example.com/contact-success.html">

  <!-- Visible fields -->
  <input name="name" type="text" placeholder="Your Name" required>
  <input name="email" type="email" placeholder="Your Email" required>
  <textarea name="body" placeholder="Your Message" required></textarea>

  <!-- Cloudflare Turnstile widget (if enabled) -->
  <!-- Replace 'your-site-key' with your actual site key from Cloudflare -->
  <div class="cf-turnstile" data-sitekey="your-site-key"></div>

  <input type="submit" value="Send">
</form>

<!-- Include the Turnstile script (if Turnstile is enabled) -->
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
```

You need six HTML form fields to submit the form data to the PHP script.

You can use hidden fields to set the EMail subject, the receiver address and the redirect URL after form submission.

1. `subject`     EMail subject.
2. `receiver`    EMail address the sumitted form will be sent to.
3. `redirect_to` Web address for browser forwarding after form submission.
4. `name`:       Name of the user submitting the form
5. `email`:      EMail of the user sumitting the form
6. `body`:       Text the user wants to send via the form

You also need a form action that points to the `php_form.php` script from this repo.

Of course you can use the hidden fields as normal ones to, for example let the user set the EMail subject via a HTML select tag.

### 4. Happy submitting


## Form validation

Modern browsers have built in form validation. For example adding `required="required"` to an input, won't allow to form to be submitted if it is empty. You can evan use a regex to validate email fields. A basic one could be implemented with `<input type="email" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}">`.

Learn more: [moz://a web docs  – Form data validation](https://developer.mozilla.org/en-US/docs/Learn/HTML/Forms/Form_validation)
