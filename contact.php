<?php
/* Contact: posts to itself, keeps values on error, replaces the form on success. */
require __DIR__ . '/includes/helpers.php';

$topics = ['' => 'Choose a topic', 'Order question' => 'Order question', 'Game feedback' => 'Game feedback', 'Press' => 'Press', 'Other' => 'Other'];
$user = current_user();
$values = ['name' => '', 'email' => $user['email'] ?? '', 'topic' => '', 'subject' => '', 'message' => ''];
$errors = [];
$formError = '';

if (is_post()) {
    foreach ($values as $key => $_) {
        $values[$key] = post($key);
    }
    if (!csrf_check()) {
        $formError = "This form expired. We've kept what you typed; press Send again.";
    } else {
        if ($values['name'] === '') {
            $errors['name'] = 'Enter your name.';
        } elseif (mb_strlen($values['name']) > 100) {
            $errors['name'] = 'Use 100 characters or fewer for your name.';
        }
        if ($values['email'] === '') {
            $errors['email'] = 'Enter your email.';
        } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter an email like name@example.com.';
        }
        if (!isset($topics[$values['topic']])) {
            $values['topic'] = '';
        }
        if ($values['subject'] === '') {
            $errors['subject'] = 'Enter your subject.';
        } elseif (mb_strlen($values['subject']) > 150) {
            $errors['subject'] = 'Use 150 characters or fewer for the subject.';
        }
        if ($values['message'] === '') {
            $errors['message'] = 'Enter your message.';
        } elseif (mb_strlen($values['message']) > 5000) {
            $errors['message'] = 'Use 5,000 characters or fewer for your message.';
        }
    }

    if (!$errors && $formError === '') {
        $subjectLine = 'Contact form: ' . ($values['topic'] !== '' ? '[' . $values['topic'] . '] ' : '') . str_replace(["\r", "\n"], ' ', $values['subject']);
        $body = "New message from the Novaspark Games contact form\n\n"
              . 'Name: ' . $values['name'] . "\n"
              . 'Email: ' . $values['email'] . "\n"
              . 'Topic: ' . ($values['topic'] !== '' ? $values['topic'] : 'Not given') . "\n"
              . 'Subject: ' . $values['subject'] . "\n\n"
              . $values['message'] . "\n";
        send_mail(config('STUDIO_EMAIL'), $subjectLine, $body);
        flash('contact_sent', ['name' => $values['name'], 'email' => $values['email']]);
        redirect('contact.php?sent=1');
    }
}

$sent = flash('contact_sent');

$pageTitle = 'Contact';
$active = 'contact';
$pageDescription = 'Questions about an order, feedback on a game, or a press enquiry.';
require __DIR__ . '/includes/header.php';
?>
<div class="container page">
  <header class="page-head">
    <h1 class="h1">Contact us</h1>
    <p class="lead">Questions about an order, feedback on a game, or a press enquiry: we'll reply within two working days.</p>
  </header>

  <div class="split split--form">
    <div>
      <?php if ($sent): ?>
        <?= alert('success', "Message sent. Thanks, {$sent['name']}. We'll reply to {$sent['email']} within two working days.", ['id' => 'sent-message']) ?>
        <p class="mt-5"><a class="link-arrow" href="<?= e(url('contact.php')) ?>">Send another message <?= icon('arrow') ?></a></p>
      <?php else: ?>
        <form class="form" method="post" action="<?= e(url('contact.php')) ?>" data-validate>
          <?= csrf_field() ?>
          <div data-error-summary>
            <?php if ($formError): ?><?= alert('error', $formError) ?><?php endif; ?>
            <?= error_summary($errors) ?>
          </div>
          <?= field('name', 'Name', ['value' => $values['name'], 'error' => $errors['name'] ?? '', 'autocomplete' => 'name', 'required' => true, 'attrs' => 'maxlength="100"']) ?>
          <?= field('email', 'Email', ['type' => 'email', 'value' => $values['email'], 'error' => $errors['email'] ?? '', 'autocomplete' => 'email', 'required' => true, 'attrs' => 'data-label="email"']) ?>
          <?= field('topic', 'Topic', ['type' => 'select', 'value' => $values['topic'], 'options' => $topics, 'optional' => true]) ?>
          <?= field('subject', 'Subject', ['value' => $values['subject'], 'error' => $errors['subject'] ?? '', 'required' => true, 'attrs' => 'maxlength="150"']) ?>
          <?= field('message', 'Message', ['type' => 'textarea', 'rows' => 6, 'value' => $values['message'], 'error' => $errors['message'] ?? '', 'required' => true, 'attrs' => 'maxlength="5000"']) ?>
          <div><button type="submit" class="btn btn--primary" data-loading-text="Sending…">Send message</button></div>
        </form>
      <?php endif; ?>
    </div>

    <aside class="stack" aria-label="Other ways to reach us">
      <div class="info-card">
        <h2 class="info-card__title"><?= icon('clock') ?> Response time</h2>
        <p>We reply within two working days, Monday to Friday.</p>
      </div>
      <div class="info-card">
        <h2 class="info-card__title"><?= icon('mail') ?> Email</h2>
        <p><a href="mailto:<?= e(config('PUBLIC_EMAIL')) ?>"><?= e(config('PUBLIC_EMAIL')) ?></a></p>
      </div>
      <div class="info-card">
        <h2 class="info-card__title"><?= icon('bag') ?> Order questions</h2>
        <p>Your order number is in your confirmation email and on <a href="<?= e(url('my-orders.php')) ?>">My orders</a>. Include it so we can help faster.</p>
      </div>
    </aside>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
