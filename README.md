<h1 align="center">Unfinalize</h1>

<p align="center">
Unleash the freedom lost with open source PHP packages marking classes and methods as final.
</p>

<p align="center">
<a href="https://github.com/stevebauman/unfinalize/actions" target="_blank">
<img src="https://img.shields.io/github/actions/workflow/status/stevebauman/unfinalize/run-tests.yml?branch=master&style=flat-square"/>
</a>

<a href="https://packagist.org/packages/stevebauman/unfinalize" target="_blank">
<img src="https://img.shields.io/packagist/v/stevebauman/purify.svg?style=flat-square"/>
</a>

<a href="https://packagist.org/packages/stevebauman/unfinalize" target="_blank">
<img src="https://img.shields.io/packagist/dt/stevebauman/purify.svg?style=flat-square"/>
</a>

<a href="https://packagist.org/packages/stevebauman/unfinalize" target="_blank">
<img src="https://img.shields.io/packagist/l/stevebauman/purify.svg?style=flat-square"/>
</a>

---

Unfinalize uses PHP CS Fixer to permanently remove `final` keywords from classes and methods from composer vendor packages on `composer update`:

```diff
- final class Foo
+ class Foo
{
-   final public function bar()
+   public function bar()
    {
        // ...
    }
}
```

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
  "scripts": {
    "post-update-cmd": [
      "@php artisan unfinalize:run"
    ]
  }
}
```

Then, run `composer update`.

### Options

#### `--mark-final`

If you would like final classes and methods to be marked with a `@final` doc block, you may add the `--mark-final` option to the unfinalize command:

```json
{
  "scripts": {
    "post-update-cmd": [
      "@php artisan unfinalize:run --mark-final"
    ]
  }
}
```

Which will produce:

**Before**:

```php
final class Foo
{
    final public function bar()
    {
        // ...
    }
}
```

**After**:

```php
/**
 * @final
 */
class Foo
{
    /**
     * @final
     */
    public function bar()
    {
        // ...
    }
}
```

#### `--dry`

Execute a dry run to see what files will be modified by PHP CS Fixer.
