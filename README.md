# Unfinalize

Permanently remove final keywords from composer vendor packages.

## Installation

```bash
composer require stevebauman/unfinalize
```

## Usage

Inside your `composer.json` file, add the vendor packages you want to remove the final keywords from inside:

```json
{
    "unfinalize": [
        "vendor/package"
    ]
}
```

Add the unfinalize command to your `composer.json` so it runs on `composer update`:

```json
{
  // ...
  "scripts": {
    "post-update-cmd": [
      "@php artisan unfinalize:run"
    ]
  }
}
```

Then, run `composer update`.
