<?php
// Collects field errors. Call fail() at the end to throw a 422 with all of them.
class Validator
{
    public array $errors = [];

    public function __construct(private array $in) {}

    private function raw(string $k): string
    {
        return isset($this->in[$k]) && is_scalar($this->in[$k]) ? trim((string)$this->in[$k]) : '';
    }

    private function missing(string $k, bool $required): void
    {
        if ($required) {
            $this->errors[$k] = 'This field is required.';
        }
    }

    public function str(string $k, bool $required = false, int $max = 255): ?string
    {
        $v = $this->raw($k);
        if ($v === '') { $this->missing($k, $required); return null; }
        if (mb_strlen($v) > $max) { $this->errors[$k] = "Maximum $max characters."; }
        return $v;
    }

    public function choice(string $k, array $allowed, bool $required = false, ?string $default = null): ?string
    {
        $v = $this->raw($k);
        if ($v === '') {
            if ($default !== null) { return $default; }
            $this->missing($k, $required);
            return null;
        }
        if (!in_array($v, $allowed, true)) { $this->errors[$k] = 'Invalid value.'; return null; }
        return $v;
    }

    public function whole(string $k, bool $required = false, int $min = 0, ?int $max = null): ?int
    {
        $v = $this->raw($k);
        if ($v === '') { $this->missing($k, $required); return null; }
        if (!preg_match('/^-?\d+$/', $v) || (int)$v < $min || ($max !== null && (int)$v > $max)) {
            $this->errors[$k] = 'Enter a whole number' . ($max !== null ? " between $min and $max." : " of at least $min.");
            return null;
        }
        return (int)$v;
    }

    public function money(string $k, bool $required = false, float $max = 99999999.99): ?float
    {
        $v = $this->raw($k);
        if ($v === '') { $this->missing($k, $required); return null; }
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $v) || (float)$v > $max) {
            $this->errors[$k] = 'Enter a valid amount (up to 2 decimals).';
            return null;
        }
        return (float)$v;
    }

    public function day(string $k, bool $required = false): ?string
    {
        $v = $this->raw($k);
        if ($v === '') { $this->missing($k, $required); return null; }
        $d = DateTime::createFromFormat('Y-m-d', $v);
        if (!$d || $d->format('Y-m-d') !== $v) { $this->errors[$k] = 'Enter a valid date.'; return null; }
        return $v;
    }

    // accepts "2026-10-26T14:30", "2026-10-26 14:30" or with seconds; returns "Y-m-d H:i:s"
    public function moment(string $k, bool $required = false): ?string
    {
        $v = $this->raw($k);
        if ($v === '') { $this->missing($k, $required); return null; }
        foreach (['Y-m-d\TH:i', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s'] as $fmt) {
            $d = DateTime::createFromFormat($fmt, $v);
            if ($d && $d->format($fmt) === $v) { return $d->format('Y-m-d H:i:s'); }
        }
        $this->errors[$k] = 'Enter a valid date and time.';
        return null;
    }

    public function email(string $k, bool $required = false): ?string
    {
        $v = $this->str($k, $required, 100);
        if ($v !== null && !filter_var($v, FILTER_VALIDATE_EMAIL)) { $this->errors[$k] = 'Enter a valid email address.'; }
        return $v;
    }

    public function phone(string $k, bool $required = false): ?string
    {
        $v = $this->str($k, $required, 20);
        if ($v !== null && !preg_match('/^\+?[0-9][0-9 ()\-]{5,18}$/', $v)) { $this->errors[$k] = 'Enter a valid phone number.'; }
        return $v;
    }

    public function flag(string $k): int
    {
        return in_array($this->raw($k), ['1', 'true', 'on'], true) ? 1 : 0;
    }

    public function fail(): void
    {
        if ($this->errors) {
            throw new ApiException('Please fix the highlighted fields.', 422, $this->errors);
        }
    }
}