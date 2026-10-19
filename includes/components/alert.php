<?php
/*
 * Alert: success | error | info | warning.
 * $message is escaped; pass $html = true only for markup you built yourself.
 */
function alert(string $type, string $message, array $opts = []): string
{
    $icons = ['success' => 'success', 'error' => 'alert', 'info' => 'info', 'warning' => 'alert'];
    $role  = $type === 'error' ? 'alert' : 'status';
    $body  = !empty($opts['html']) ? $message : e($message);
    $title = isset($opts['title']) ? '<p class="alert__title">' . e($opts['title']) . '</p>' : '';
    $id    = isset($opts['id']) ? ' id="' . e($opts['id']) . '" tabindex="-1"' : '';
    $dismiss = '';
    if ($type === 'info' && !empty($opts['dismissible'])) {
        $dismiss = '<button type="button" class="alert__dismiss" data-dismiss-alert aria-label="Dismiss message">' . icon('close') . '</button>';
    }
    return '<div class="alert alert--' . e($type) . '" role="' . $role . '"' . $id . '>'
        . icon($icons[$type] ?? 'info', 'alert__icon')
        . '<div class="alert__body">' . $title . '<div>' . $body . '</div></div>'
        . $dismiss . '</div>';
}

/* Error summary shown above a form: each error links to its field. */
function error_summary(array $errors): string
{
    if (!$errors) {
        return '';
    }
    $items = '';
    foreach ($errors as $field => $message) {
        $items .= '<li><a href="#' . e($field) . '">' . e($message) . '</a></li>';
    }
    $count = count($errors);
    $title = $count === 1 ? 'There is 1 problem to fix' : "There are $count problems to fix";
    return alert('error', '<ul class="error-summary">' . $items . '</ul>', ['html' => true, 'title' => $title, 'id' => 'error-summary']);
}
