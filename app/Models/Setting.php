<?php
class Setting
{
    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache === null) {
            $row = Database::query('SELECT * FROM settings WHERE setting_id = 1')->fetch();
            self::$cache = $row ?: [
                'store_name' => 'Car Rental Store', 'contact_email' => null, 'phone' => null, 'address' => null,
                'currency' => 'USD', 'tax_rate_percent' => '0.00', 'late_fee_multiplier' => '1.50', 'default_deposit' => '0.00',
            ];
        }
        return self::$cache;
    }

    public static function save(array $d): void
    {
        Database::query(
            'INSERT INTO settings (setting_id, store_name, contact_email, phone, address, currency, tax_rate_percent, late_fee_multiplier, default_deposit)
             VALUES (1, :store_name, :contact_email, :phone, :address, :currency, :tax_rate_percent, :late_fee_multiplier, :default_deposit)
             ON DUPLICATE KEY UPDATE store_name = VALUES(store_name), contact_email = VALUES(contact_email), phone = VALUES(phone),
               address = VALUES(address), currency = VALUES(currency), tax_rate_percent = VALUES(tax_rate_percent),
               late_fee_multiplier = VALUES(late_fee_multiplier), default_deposit = VALUES(default_deposit)',
            [
                ':store_name' => $d['store_name'], ':contact_email' => $d['contact_email'], ':phone' => $d['phone'],
                ':address' => $d['address'], ':currency' => $d['currency'], ':tax_rate_percent' => $d['tax_rate_percent'],
                ':late_fee_multiplier' => $d['late_fee_multiplier'], ':default_deposit' => $d['default_deposit'],
            ]
        );
        self::$cache = null;
    }
}