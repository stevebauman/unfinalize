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

Unfinalize uses PHP CS Fixer to permanently remove `final` keywords from composer vendor packages:

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

#### `--mark-final`

If you would like final classes and methods to be marked with a `@final` doc block, you may add the `--mark-final` option to the unfinalize command:

```json
{
  "scripts": {
    "post-update-cmd": [
      "@php vendor/bin/unfinalize run --mark-final"
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
