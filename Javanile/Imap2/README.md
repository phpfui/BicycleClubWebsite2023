# PHP IMAP Drop in Replacement for PHP 8.4

A drop in PHP replacement for the imap_ functions removed from PHP 8.4. If imap_ functions are detected, then the built in functions are used. So you can install this on PHP 8.2 or 8.3 and when you upgrade to PHP 8.4, it will continue to work.

## Usage

```php
include 'vendor/phpfui/php-imap2/src/IMAPStubs.php
// continue to use imap_ functions. Example:
//$mbh = \imap_open($server, $username, $token, OP_XOAUTH2);
```

## This package is based on javanile/php-imap2

It was updated with arbor-education/php-imap2, as that seems to be the most maintained fork, as jaavanile/php-imap2 seems abandon. Since this a just a wrapper around the imap_ functions, it can easily be updated to a better IMAP library if needed.

## Unimplemented functions

The following functions were not implemented in the original source:

* function imap_get_quota(IMAP\Connection $imap, $quotaRoot)
* function imap_set_quota(IMAP\Connection $imap,string $quota_root): array|false
* function imap_get_quotaroot(IMAP\Connection $imap, $mailbox)
* function imap_getacl(IMAP\Connection $imap, $mailbox)
* Function imap_setacl(IMAP\Connection $imap, $mailbox, $userId, $rights)



