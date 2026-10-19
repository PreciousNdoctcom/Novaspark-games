<?php
/*
 * Form field: label, optional helper, control, inline error.
 * $o keys: type, value, error, helper, autocomplete, optional, required,
 *          options (select: value => label), rows, attrs (extra attributes string), placeholder.
 */
function field(string $name, string $label, array $o = []): string
{
    $type     = $o['type'] ?? 'text';
    $id       = $o['id'] ?? $name;
    $value    = (string) ($o['value'] ?? '');
    $error    = $o['error'] ?? '';
    $helper   = $o['helper'] ?? '';
    $describe = [];

    $labelText = e($label) . (!empty($o['optional']) ? ' <span class="field__optional">(optional)</span>' : '');
    $html = '<div class="field' . ($error ? ' field--error' : '') . '">';
    $html .= '<label class="field__label" for="' . e($id) . '">' . $labelText . '</label>';
    if ($helper !== '') {
        $html .= '<p class="field__helper" id="' . e($id) . '-help">' . e($helper) . '</p>';
        $describe[] = $id . '-help';
    }
    if ($error) {
        $describe[] = $id . '-error';
    }

    $attrs = ' id="' . e($id) . '" name="' . e($name) . '"';
    if (!empty($o['autocomplete'])) $attrs .= ' autocomplete="' . e($o['autocomplete']) . '"';
    if (!empty($o['required']))     $attrs .= ' required';
    if (!empty($o['placeholder']))  $attrs .= ' placeholder="' . e($o['placeholder']) . '"';
    if ($describe)                  $attrs .= ' aria-describedby="' . e(implode(' ', $describe)) . '"';
    if ($error)                     $attrs .= ' aria-invalid="true"';
    if (!empty($o['attrs']))        $attrs .= ' ' . $o['attrs'];

    if ($type === 'select') {
        $html .= '<div class="field__select">';
        $html .= '<select class="control"' . $attrs . '>';
        foreach ($o['options'] ?? [] as $optValue => $optLabel) {
            $selected = ((string) $optValue === $value) ? ' selected' : '';
            $html .= '<option value="' . e($optValue) . '"' . $selected . '>' . e($optLabel) . '</option>';
        }
        $html .= '</select>' . icon('chevron', 'field__chevron') . '</div>';
    } elseif ($type === 'textarea') {
        $rows = (int) ($o['rows'] ?? 5);
        $html .= '<textarea class="control control--textarea" rows="' . $rows . '"' . $attrs . '>' . e($value) . '</textarea>';
    } elseif ($type === 'password') {
        $html .= '<div class="field__password">';
        $html .= '<input class="control" type="password"' . $attrs . '>';
        $html .= '<button type="button" class="field__toggle" data-password-toggle="' . e($id) . '" aria-controls="' . e($id) . '" aria-pressed="false">Show<span class="visually-hidden"> password</span></button>';
        $html .= '</div>';
    } else {
        $html .= '<input class="control" type="' . e($type) . '" value="' . e($value) . '"' . $attrs . '>';
    }

    $html .= '<p class="field__error" id="' . e($id) . '-error"' . ($error ? '' : ' hidden') . ' data-field-error>'
           . icon('alert') . '<span>' . e($error) . '</span></p>';
    $html .= '</div>';
    return $html;
}
