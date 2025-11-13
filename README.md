# PHP Accessibility Checker

PHP package for communicating with the accessibility audit API.

### Supported functions:

- Running a page audit (scan) – synchronously and asynchronously
- Retrieving the audit result by audit UUID (get)
- Retrieving audit history by address UUID (history)

### Setup
```shell
composer require dockcodes/a11y-checker
```

### Usage
```php
<?php
require 'vendor/autoload.php';

use Dock\A11yChecker\Client as A11yCheckerClient;

$guest = new A11yCheckerClient();

// Run scan
$result = $guest->scan('https://example.com');
echo "Audit uuid: " . $result['uuid'] . "\n";
echo "Address uuid: " . $result['address_uuid'] . "\n";

// Get audit result
$report = $guest->audit($result['uuid']);
print_r($report);

// Get history
$history = $guest->history($result['address_uuid']);
print_r($history);
```

### Method parameters
```php
scan(string $url, Language $lang = Language::EN, Device $device = Device::DESKTOP, bool $sync = false, bool $extraData = false, ?string $uniqueKey = null)

rescan(string $uuid, Language $lang = Language::EN, bool $sync = false, bool $extraData = false)

audits(string $search, int $page = 1, int $perPage = 10, Sort $sort = Sort::LAST_AUDIT_DESC, ?string $uniqueKey = null)

audit(string $uuid, Language $lang = Language::EN, bool $extraData = false)

user()

deleteAudit(string $uuid)

history(string $uuid, int $page = 1, int $perPage = 10, Sort $sort = Sort::CREATED_AT_ASC)

deleteHistory(string $uuid)

updateAuditManual(string $uuid, string $criterionId, AuditStatus $status, Device $device));
```
### 🔑 Get Your API Key

You can test as a guest, request an API key, or test the service directly on the website:

👉 https://wcag.dock.codes/contact-us/
