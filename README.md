<h1 align="center">Unfinalize</h1>

<p align="center">
Unleash the freedom lost with open source PHP packages marking classes and methods as <code>final</code>.
</p>

<p align="center">
<a href="https://github.com/stevebauman/unfinalize/actions" target="_blank">
<img src="https://img.shields.io/github/actions/workflow/status/stevebauman/unfinalize/run-tests.yml?branch=master&style=flat-square"/>
</a>

<a href="https://packagist.org/packages/stevebauman/unfinalize" target="_blank">
<img src="https://img.shields.io/packagist/v/stevebauman/unfinalize.svg?style=flat-square"/>
</a>

<a href="https://packagist.org/packages/stevebauman/unfinalize" target="_blank">
<img src="https://img.shields.io/packagist/dt/stevebauman/unfinalize.svg?style=flat-square"/>
</a>

<a href="https://packagist.org/packages/stevebauman/unfinalize" target="_blank">
<img src="https://img.shields.io/packagist/l/stevebauman/unfinalize.svg?style=flat-square"/>
</a>

---

Unfinalize uses [PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) to permanently remove `final` keywords from composer vendor packages:

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

- Updates to PHP files are done safely, quickly, and performant.
- Changes are stored permanently. There is no performance impact using Unfinalize.
- No additional dependencies to your application. Unfinalize and its dependencies are [compiled into a single phar file](https://github.com/stevebauman/unfinalize/blob/master/builds).

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
      "@php vendor/bin/unfinalize run"
    ]
  }
}
```

Then, run `composer update`.

### Options

#### `--annotate={annotation}`

If you would like final classes and methods to be marked with an annotation (`@{annotation}`) doc
block after unfinalizing, you may add the `--annotate` option to the unfinalize command:

> If an annotation already exists in a doc block then it will be left untouched.

```json
{
  "scripts": {
    "post-update-cmd": [
      "@php vendor/bin/unfinalize run --annotate=internal"
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
 * @internal
 */
class Foo
{
    /**
     * @internal
     */
    public function bar()
    {
        // ...
    }
}
```

#### `--properties={protected/public}`

If you would like to change the visibility of `private` properties to 
`protected` or `public`, you may add the `--properties` option to 
the unfinalize command with the new visibility to assign:

```json
{
  "scripts": {
    "post-update-cmd": [
      "@php vendor/bin/unfinalize run --properties=protected"
    ]
  }
}
```

Which will produce:

**Before**:

```php
class Foo
{
    private $bar;
}
```

**After**:

```php
class Foo
{
    public $bar;
}
```

#### `--methods={protected/public}`

If you would like to change the visibility of `private` methods to 
`protected` or `public`, you may add the `--properties` option 
to the unfinalize command with the new visibility to assign:

```json
{
  "scripts": {
    "post-update-cmd": [
      "@php vendor/bin/unfinalize run --methods=public"
    ]
  }
}
```

Which will produce:

**Before**:

```php
class Foo
{
    private function bar()
    {
    }
}
```

**After**:

```php
class Foo
{
    public function bar()
    {
    }
}
```

#### `--dry`

Execute a dry run to see what files will be modified by Unfinalize:

```bash
vendor/bin/unfinalize run --dry
```
