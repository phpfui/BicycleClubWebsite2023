<?php

namespace App\Model;

class Session extends \PHPFUI\Session
	{
	final public const int DEBUG_BAR = 4;

	private static ?\App\Record\Member $signedInMember = null;

	private static ?\App\Record\Membership $signedInMembership = null;

	public static function addPhotoToAlbum(int $photoId) : void
		{
		$_SESSION['photoAlbum'][] = $photoId;
		}

	public static function clearPhotoAlbum() : void
		{
		unset($_SESSION['photoAlbum']);
		}

	public static function cut(string $type, int $id, bool $add = true) : void
		{
		if ($add)
			{
			$_SESSION['cuts'][$type][$id] = true;
			}
		else
			{
			unset($_SESSION['cuts'][$type][$id]);
			}
		}

	public static function destroy() : void
		{
		foreach ($_SESSION as $key => $value)
			{
			unset($_SESSION[$key]);
			}
		$params = \session_get_cookie_params();
		\setcookie(\session_name(), '', ['expires' => 0, 'path' => (string)$params['path'], 'domain' => (string)$params['domain'], 'secure' => $params['secure'], 'httponly' => isset($params['httponly'])]); // @phpstan-ignore isset.offset
		\session_destroy();
		}

	public static function expires() : int
		{
		return (int)$_SESSION['expires'];
		}

	/**
	 * @return array<\App\Record\CartItem>
	 */
	public static function getCartItems() : array
		{
		$post = $_SESSION['cartItems'] ?? [];
		$items = [];

		foreach ($post as $item)
			 {
			 $cartItem = new \App\Record\CartItem();
			 $cartItem->setFrom($item);
			 $items[] = $cartItem;
			 }

		return $items;
		}

	public static function getCustomerNumber() : int
		{
		return $_SESSION['customerNumber'] ?? 0;
		}

	/**
	 * @return array<int,int>
	 */
	public static function getCuts(string $type) : array
		{
		return $_SESSION['cuts'][$type] ?? [];
		}

	public static function getDebugging(int $flags = 0) : int
		{
		$debug = $_SESSION['debugging'] ?? 0;

		if ($flags)
			{
			return $debug & $flags;
			}

		return $debug;
		}

	/**
	 * @return array<int,int>
	 */
	public static function getPhotoAlbum() : array
		{
		return $_SESSION['photoAlbum'] ?? [];
		}

	/**
	 * @return array<string,string>
	 */
	public static function getSignedInMember() : array
		{
		$memberTable = new \App\Table\Member();

		return $memberTable->getMembership(self::signedInMemberId());
		}

	public static function hasExpired() : bool
		{
		return ! empty($_SESSION['expires']) && $_SESSION['expires'] < \App\Tools\Date::todayString();
		}

	public static function isSignedIn() : bool
		{
		return ! empty($_SESSION['membershipId']);
		}

	public static function registerMember(\App\Record\Member $member) : void
		{
		if ($member->loaded())
			{
			$_SESSION['customerNumber'] = $_SESSION['memberId'] = $member->memberId;
			$_SESSION['membershipId'] = $member->membershipId;
			$_SESSION['acceptedWaiver'] = $member->acceptedWaiver;
			$_SESSION['expires'] = $member->membership->expires;
			}
		}

	public static function reloadPermissions() : void
		{
		// next page load will reload current userPermissions
		unset($_SESSION['userPermissions']);
		}

	/**
	 * @param array<\App\Record\CartItem> $cartItems
	 */
	public static function saveCartItems(array $cartItems) : void
		{
		$items = [];

		foreach ($cartItems as $cartItem)
			{
			$items[] = $cartItem->toArray();
			}

		if ($items)
			{
			$_SESSION['cartItems'] = $items;
			}
		else
			{
			unset($_SESSION['cartItems']);
			}
		}

	public static function setCustomerNumber(int $number) : void
		{
		$_SESSION['customerNumber'] = $number;
		}

	public static function setDebugging(int $debug) : void
		{
		if ($debug)
			{
			$_SESSION['debugging'] = $debug;
			}
		else
			{
			unset($_SESSION['debugging']);
			}
		}

	public static function setSignedInMemberId(int $memberId) : void
		{
		$_SESSION['memberId'] = $memberId;
		}

	public static function signedInMemberId() : int
		{
		return ! empty($_SESSION['memberId']) ? $_SESSION['memberId'] : 0;
		}

	public static function signedInMemberRecord() : \App\Record\Member
		{
		return self::$signedInMember ?: self::$signedInMember = new \App\Record\Member(self::signedInMemberId());
		}

	public static function signedInMembershipId() : int
		{
		return ! empty($_SESSION['membershipId']) ? $_SESSION['membershipId'] : 0;
		}

	public static function signedInMembershipRecord() : \App\Record\Membership
		{
		return self::$signedInMembership ?: self::$signedInMembership = new \App\Record\Membership(self::signedInMembershipId());
		}

	public static function signedWaiver() : bool
		{
		return isset($_SESSION['acceptedWaiver']) ? ($_SESSION['acceptedWaiver'] > \date('Y-m-d H:i:s', \time() - (365 * 86400))) : false;
		}

	public static function signWaiver() : string
		{
		return $_SESSION['acceptedWaiver'] = \date('Y-m-d H:i:s');
		}

	public static function unregisterMember() : void
		{
		$_SESSION['acceptedWaiver'] = $_SESSION['expires'] = $_SESSION['memberId'] = $_SESSION['membershipId'] = 0;
		unset($_SESSION['userPermissions'], $_SESSION['photoAlbum'], $_SESSION['cuts']);
		}
	}
