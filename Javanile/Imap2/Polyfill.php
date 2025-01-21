<?php

/*
 * This file is part of the PHP IMAP2 package.
 *
 * (c) Francesco Bianco <bianco@javanile.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Javanile\Imap2;

class Polyfill
{
    public static function convert8bit($string)
    {
        return $string;
    }

    public static function mimeHeaderDecode($string)
    {
        return $string;
    }

    public static function mutf7ToUtf8($string)
    {
        return $string;
    }

    public static function qPrint($string)
    {
        return $string;
    }

    public static function rfc822ParseAdrList($string, $defaultHost)
    {
        $message = \ZBateson\MailMimeParser\Message::from('To: '.$string, false);

        return Functions::getAddressObjectList(
            $message->getHeader(\ZBateson\MailMimeParser\Header\HeaderConsts::TO)->getAddresses(),
            $defaultHost
        );
    }

    /**
     *
     * @param $headers
     * @param $defaultHostname
     *
     * @return mixed
     */
    public static function rfc822ParseHeaders($headers, $defaultHost = 'UNKNOWN')
    {
        $message = \ZBateson\MailMimeParser\Message::from($headers, false);

        $date = $message->getHeaderValue(\ZBateson\MailMimeParser\Header\HeaderConsts::DATE);
        $subject = $message->getHeaderValue(\ZBateson\MailMimeParser\Header\HeaderConsts::SUBJECT);

        $hasReplyTo = $message->getHeader(\ZBateson\MailMimeParser\Header\HeaderConsts::REPLY_TO) !== null;
        $hasSender = $message->getHeader(\ZBateson\MailMimeParser\Header\HeaderConsts::SENDER) !== null;

        return (object) [
            'date' => $date,
            'Date' => $date,
            'subject' => $subject,
            'Subject' => $subject,
            'message_id' => '<'.$message->getHeaderValue(\ZBateson\MailMimeParser\Header\HeaderConsts::MESSAGE_ID).'>',
            'toaddress' => $message->getHeaderValue(\ZBateson\MailMimeParser\Header\HeaderConsts::TO),
            'to' => Functions::getAddressObjectList($message->getHeader(\ZBateson\MailMimeParser\Header\HeaderConsts::TO)->getAddresses()),
            'fromaddress' => $message->getHeaderValue(\ZBateson\MailMimeParser\Header\HeaderConsts::FROM),
            'from' => Functions::getAddressObjectList($message->getHeader(\ZBateson\MailMimeParser\Header\HeaderConsts::FROM)->getAddresses()),
            'reply_toaddress' => $message->getHeaderValue($hasReplyTo ? \ZBateson\MailMimeParser\Header\HeaderConsts::REPLY_TO : \ZBateson\MailMimeParser\Header\HeaderConsts::FROM),
            'reply_to' => Functions::getAddressObjectList($message->getHeader($hasReplyTo ? \ZBateson\MailMimeParser\Header\HeaderConsts::REPLY_TO : \ZBateson\MailMimeParser\Header\HeaderConsts::FROM)->getAddresses()),
            'senderaddress' => $message->getHeaderValue($hasSender ? \ZBateson\MailMimeParser\Header\HeaderConsts::SENDER : \ZBateson\MailMimeParser\Header\HeaderConsts::FROM),
            'sender' => Functions::getAddressObjectList($message->getHeader($hasSender ? \ZBateson\MailMimeParser\Header\HeaderConsts::SENDER : \ZBateson\MailMimeParser\Header\HeaderConsts::FROM)->getAddresses()),
        ];
    }

    public static function rfc822WriteHeaders($mailbox, $hostname, $personal)
    {
        $ret = $mailbox;
        if (!empty($hostname))
        {
            $ret .= '@' . $hostname;
        }
        return $ret;
    }

    public static function utf7Decode($string)
    {
        return mb_convert_decoding($string, "UTF7-IMAP", "UTF-8");
    }

    public static function utf7Encode($string)
    {
        return mb_convert_encoding($string, "UTF-8", "UTF7-IMAP");
    }

    public static function utf8ToMutf7($string)
    {
        return $string;
    }

    public static function utf8($string)
    {
        return $string;
    }

    public static function mailCompose($envelope, $bodies)
    {
        return false;
    }
}
