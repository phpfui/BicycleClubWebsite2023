# PHPFUI/Translation [![Tests](https://github.com/phpfui/Translation/actions/workflows/tests.yml/badge.svg)](https://github.com/phpfui/Translation/actions?query=workflow%3Atests) [![Latest Packagist release](https://img.shields.io/packagist/v/phpfui/translation.svg)](https://packagist.org/packages/phpfui/translation) ![](https://img.shields.io/badge/PHPStan-level%206-brightgreen.svg?style=flat)

A simple, fast, and memory efficient translation system for PHP.

Why another translation system? Simply for speed and reduced memory usage.  Just under 500 lines of code including comments, this translation system does not have high overhead like other existing systems. Since PHP is an interpreted scripting language, speed and memory usage matter.  This library attempts to solve both issues.

## Supported Features
* Translations stored in native PHP arrays as key => value pairs.
* Chunked translations so only needed translations are loaded into memory.
* Supports third party Key Value store systems like memcached.
* Missing translation logging support for recording untranslated text.
* Parameter substitution using :name syntax using associative arrays.
* Pluralization including range support.
* Unlimited locales.
* invisible, TRANS, and RAW locales for debug support.

## Chunked Translation Support
Chunks allow you to break up translations so all translations don't end up in memory at the same time.  This is particularly useful for large projects.

Chunks are defined by any translation that starts with a period (.).  These are chunked translations:
~~~
.save
.cancel
.messages.notFound
.messages.saved
.titles.firstName
.titles.lastName
.person.address.city
.person.address.state
.person.address.postalcode
~~~
The first two examples are called base chunks stored in the **baseChunks.php** file in the locale root directory.  All base chunks are always loaded even if not requested, so they should only contain frequently used translations that would normally get loaded. The third through sixth chunks allow you to only load those chunks when required.  If your page does not need titles, then the .titles chunk will not be loaded.  The entire level of a chunk will be loaded at the same time.  So in the last three examples, the .person.address chunk will load when .person.address.city (or state, or postalcode) is accessed.

Any translation that does not start with a period (.) is considered a native language translation and is stored in the **native.php** file in local root directory.  All native chunks are loaded when any native chunk is loaded, except for the base locale, where they are never loaded as an optimization.  This means a base locale native translation will never be listed as missing.

## File Structure
You must specify a translation directory. Each locale you support will have its own directory within your translation directory.  Consult the **Tests/translations** directory for examples.

## Locale Support
Locales can be named anything your file system supports.  The locale is the name of the directory in the base translation directory.

Generally you set the base locale, which is the language of your native translations (if you are using them).  Then you set the user's locale.  Those can be the same.

#### Reserved Locales for Debugging
* **invisible** - all translations return an empty string.
* **TRANS** - all translations return the literal string TRANS.
* **RAW** - returns the actual text passed in to be translated.  No other processing is done on it.
* **''** or empty string locale - not translated but fully processed for variable substitution and pluralization.

## Fallback Locale Support
Too keep things as simple as possible, this library does not support falling back to a base locale if a translation is missing.  This can be accomplished by preprocessing each locale. Any missing translation can be filled in with a translation from another locale.  This library does not provide any support for managing translation files, as that is best left up to the developers if something custom is required. Use the **var_export** function to write translation files if you automate the process.  This will insure your translations are parseable by PHP.

## Usage
```php
namespace PHPFUI\Translation;
// specify where the translations are located
Translator::setTranslationDirectory(__DIR__ . '/trans');
// set the base locale, ie. the language of any native translations (unchunked)
Translator::setBaseLocale('EN-us');
// set the user's locale
Translator::setLocale('ES-us');
// get the ES-us version of 'red'
$translated = Translator::trans('red');
// get chunked version of colors.
$translated = Translator::trans('.colors.red');
```

## Parameters
You can pass parameters to substitute in the translation by passing an associate array as the second parameter:
```php
// translate with parameters and pluralized
$translated = Translator::trans('.messages.recordsFound', ['count' => $found]);
```
Parameters in the translations should start with a colon (:).  A simple str_replace is used to translation the variables, so beware :name and :names  will probably not be replaced in the way you might expect. It is best to use unique names where possible.

## Pluralization
This library supports pluralization via different sections separated by the vertical bar (|) character.  If you need '|' in your text use the HTML entity **&amp;verbar;**

The **count** variable (:count in the translated text) is used for determining the number of items to pluralize for.  It is not required to use :count in the translation, but you can if you desire.

 * A count of 0 or less will select the first section.
 * A count higher than the last section will return the last section.
 * Other counts will select the number section, so 1 would return the 'one' section in the string 'zero|one|two'
 * You can use the [first,last] notation at the start of a section to specify a matching range for the section.
 * &ast; is a wild card for matching any count.  For example "[0]There are no brands|[1,9]There are under ten brands|[10,99]There are under 100 brands|[*]There are hundreds of brands"
 * Combine with **count** parameter - "No records found|One record found|:count records found"

## Full Class Documentation
[PHPFUI/InstaDoc](http://phpfui.com/?n=PHPFUI%5CTranslation&c=Translator)

## License
PHPFUI/Translation is distributed under the MIT License.

### PHP Versions
This library only supports **modern** versions of PHP which still receive security updates. While we would love to support PHP from the late Ming Dynasty, the advantages of modern PHP versions far out weigh quaint notions of backward compatibility. Time to upgrade.
