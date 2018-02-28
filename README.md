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

PHP 5.0+ enabled webserver with PEAR Mail component installed and an SMTP enabled EMail account. Most shared Webhosting accounts today provide that stuff.

Your webserver should also be reachable via HTTPS, so browsers won't complain about sending an insecure form. Again most shared Webhosters today provide that via services like Lets Encrypt.


## Installation

#### 1. Copy the files from this repository onto your PHP enabled webserver.

#### 2. Adapt the config array from the `form_mailer.php` file to your needs

#### 3. Set up your contact form:

```html
<form action="https://your-webserver.com/form_mailer.php" method="post">
  <input name="subject" type="hidden" value="Contact form submission">
  <input name="receiver" type="hidden" value="me@example.com">
  <input name="redirect_to" type="hidden" value="https://example.com/contact_thanks/">

  <input name="name" type="text" placeholder="Your name">
  <input name="email" type="text" placeholder="Your E-Mail">
  <textarea name="body" placeholder="Your message"></textarea>

  <input type="submit" value="Send">
</form>
```

You need three hidden inputs: 
1. `subject`     - the EMail subject
2. `receiver`    - the EMail address the sumitted form will be sent to
3. `redirect_to` - web address for browser forwarding after form submission

You need three normal inputs: 
1. `name`: Name of the user submitting the form
2. `email`: EMail of the user sumitting the form
2. `body`: Text the user want so send via the contact form


You also need a form action that points to the `php_form.php` script from this repo.

Of course you can use the hidden fields as normal ones to, for example let the user set the EMail subject.


#### 4. Happy submitting


## Form validation

Modern browsers have built in form validation. For example adding `required="required"` to an input, won't allow to form to be submitted if it is empty. You can evan use a regex to validate email fields. A basic one could be implemented with `<input type="email" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}">`.

Learn more: [moz://a web docs  – Form data validation](https://developer.mozilla.org/en-US/docs/Learn/HTML/Forms/Form_validation)


## ToDo

- Add ability to submit n custom inputs.
- Add option to send confirmation mail to form submitter
- Encrypt `receiver` address
