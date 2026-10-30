<?php
if (!function_exists('ff')) {
    // ff(name, label, type, column class, options[required, options(for select), attrs])
    function ff(string $name, string $label, string $type = 'text', string $col = 'col-md-6', array $opt = []): void
    {
        $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
        $req   = !empty($opt['required']) ? ' required' : '';
        $star  = $req ? ' <span class="text-danger">*</span>' : '';
        $attrs = $opt['attrs'] ?? '';
        echo "<div class=\"$col\"><label class=\"form-label\" for=\"f_$name\">" . $e($label) . "$star</label>";
        if ($type === 'select') {
            echo "<select class=\"form-select\" id=\"f_$name\" name=\"$name\"$req $attrs>";
            foreach (($opt['options'] ?? []) as $val => $text) {
                echo '<option value="' . $e($val) . '">' . $e($text) . '</option>';
            }
            echo '</select>';
        } elseif ($type === 'textarea') {
            echo "<textarea class=\"form-control\" id=\"f_$name\" name=\"$name\" rows=\"2\"$req $attrs></textarea>";
        } else {
            echo "<input class=\"form-control\" type=\"$type\" id=\"f_$name\" name=\"$name\"$req $attrs>";
        }
        echo '<div class="invalid-feedback"></div></div>';
    }
}