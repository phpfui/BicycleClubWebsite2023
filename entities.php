//// Assumes input is ASCII and converts it to UTF-8
//$string = mb_convert_encoding($string, 'UTF-8', 'ASCII');
//
//// Assumes input is UTF-8 and converts to ASCII approximations
//$string = @iconv('UTF-8', 'ASCII//TRANSLIT', $string);
//
//// Removes anything that is not ASCII
//$string = preg_replace('/[^[:ascii:]]/', '', $string);
//
//Taken together, without detecting the actual incoming character encoding first, this can produce inconsistent behavior. That likely explains why quotes, apostrophes, dashes, etc. disappear “sometimes” but not always — depending on whether the content originated as ASCII, UTF-8, Word/phone smart punctuation, Windows-1252, ISO, etc.
//
//I wasn’t sure whether the intended contract for unhtmlentities() is UTF-8 output or ASCII output. UTF-8 is the modern standard, but based on the existing implementation, it’s possible ASCII normalization was intentional.
//
//So I made two replacement versions:
//
//1) UTF-8 version — preserves smart quotes, em dashes, accented characters, etc., while detecting and normalizing source encodings.
//
public static function unhtmlentities(?string $string) : string
{
    if (! $string)
    {
       return '';
    }
    $encoding = \mb_detect_encoding(
       $string,
       ['ASCII', 'UTF-8', 'Windows-1252', 'ISO-8859-1'],
       true
    );
    // Normalize everything to UTF-8
    if ($encoding && ! \in_array($encoding, ['ASCII', 'UTF-8']))
    {
       $string = \mb_convert_encoding($string, 'UTF-8', $encoding);
    }
    elseif (! $encoding)
    {
       // fallback for pasted Word/email content
       $string = \mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
    }
    return parent::unhtmlentities($string);
}


//2) ASCII version — still detects/normalizes incoming encodings first, but intentionally produces ASCII-only output at the end.

public static function unhtmlentities(?string $string) : string
{
    if (! $string)
    {
       return '';
    }
    $encoding = \mb_detect_encoding(
       $string,
       ['ASCII', 'UTF-8', 'Windows-1252', 'ISO-8859-1'],
       true
    );
    // First normalize input into UTF-8
    if ($encoding && ! \in_array($encoding, ['ASCII', 'UTF-8']))
    {
       $string = \mb_convert_encoding($string, 'UTF-8', $encoding);
    }
    elseif (! $encoding)
    {
       $string = \mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
    }
    $string = parent::unhtmlentities($string);
    // Then intentionally downgrade to ASCII
    $string = @\iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    if (false === $string)
    {
       $string = '';
    }
    return \preg_replace('/[^[:ascii:]]/', '', $string);
}

//One gotcha to be aware of: unhtmlentities() is not only used by the email path. I noticed it is also called through removeMSStyles() → cleanUserHtml(), in addition to cleanEmailHtml(). So if other downstream code historically expects ASCII behavior, changing this globally could affect non-email flows as well.
//
//Because of that, it may make sense to either:
//
//    explicitly choose one contract (UTF-8 vs ASCII), or
//    split behavior into separate UTF-8 / ASCII helper paths depending on the caller.
//
