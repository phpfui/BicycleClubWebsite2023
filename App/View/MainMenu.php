<?php

namespace App\View;

class MainMenu extends \PHPFUI\AccordionMenu
	{
	private string $activeMenu = '';

	private string $currentMenu = '';

	private array $theMenu = [];

	public function __construct(private readonly \App\Model\PermissionBase $permissions, private string $activeLink = '')
		{
		parent::__construct();
		$parts = \explode('/', $this->activeLink);
		$this->activeMenu = $parts[1];

		if ($menu = $this->addTopMenu('Rides', 'Rides'))
			{
			$this->addSub($menu, '/Rides/edit/0', 'Add A Ride');
			$this->addSub($menu, '/Rides/addByCueSheet', 'Add CueSheet Ride');
			$this->addSub($menu, '/Rides/past', 'Past Rides');
			$this->addSub($menu, '/Rides/myPast', 'My Past Rides');
			$this->addSub($menu, '/Rides/attendance', 'Ride Attendance');
			$this->addSub($menu, '/Rides/schedule', 'Ride Schedule');
			$this->addSub($menu, '/Rides/statistics', 'Ride Statistics');
			$this->addSub($menu, '/Rides/search', 'Search Rides');
			$this->addSub($menu, '/Rides/myCategoryRides', 'Rides In My Category');
			$this->addSub($menu, '/Rides/csv', 'Download Rides.csv');
			}

		if ($menu = $this->addTopMenu('Newsletter', 'Newsletters'))
			{
			$this->addSub($menu, '/Newsletter/all', 'Newsletters');
			$this->addSub($menu, '/Newsletter/upload', 'Add A Newsletter');
			$this->addSub($menu, '/Newsletter/settings', 'Newsletter Settings');
			}

		if ($menu = $this->addTopMenu('News', 'News'))
			{
			$this->addSub($menu, '/News/latest', 'Latest News');
			$this->addSub($menu, '/News/board', 'Board Minutes');
			}

		if ($menu = $this->addTopMenu('Photo', 'Photos'))
			{
			$this->addSub($menu, '/Photo/browse', 'Browse Photos');
			$this->addSub($menu, '/Photo/myPhotos', 'My Photos');
			$this->addSub($menu, '/Photo/taggers', 'Top Taggers');
			$this->addSub($menu, '/Photo/inPhotos', 'In Photos');
			$this->addSub($menu, '/Photo/search', 'Find Photos');
			$this->addSub($menu, '/Photo/mostTagged', 'Most Tagged');
			}

		if ($menu = $this->addTopMenu('Forums', 'Forums'))
			{
			$this->addSub($menu, '/Forums/manage', 'Manage Forums');
			$this->addSub($menu, '/Forums/my', 'My Forums');

			$forumTable = new \App\Table\Forum();
			$forumTable->addOrderBy('name');

			foreach ($forumTable->getRecordCursor() as $forum)
				{
				if ($permissions->isAuthorized($forum->name, 'Forums'))
					{
					$this->addSub($menu, '/Forums/home/' . $forum->forumId, $forum->name);
					}
				}
			}

		if ($menu = $this->addTopMenu('CueSheets', 'Cue Sheets'))
			{
			$this->addSub($menu, '/CueSheets/addCue', 'Add A New Cue Sheet');
			$this->addSub($menu, '/CueSheets/pending', 'Approve Cue Sheets');
			$this->addSub($menu, '/CueSheets/notes', 'Cue Sheet Notes');
			$configMenu = $this->addMenu($menu, '/CueSheets/configure', 'Cue Sheet Configuration');
			$this->addSub($configMenu, '/CueSheets/acceptEmail', 'Accept Cue Sheet Email');
			$this->addSub($configMenu, '/CueSheets/rejectEmail', 'Reject Cue Sheet Email');
			$this->addSub($configMenu, '/CueSheets/coordinator', 'Cue Sheet Coordinator');
			$this->addSub($menu, '/CueSheets/find', 'Find A Cue Sheet');
			$this->addSub($menu, '/CueSheets/my', 'My Cue Sheets');
			$this->addSub($menu, '/CueSheets/recent', 'Recent Cue Sheets');
			$this->addSub($menu, '/CueSheets/statistics', 'Cue Sheet Statistics');
			$this->addSub($menu, '/CueSheets/templates', 'Cue Sheet Templates');
			$this->addSub($menu, '/CueSheets/merge', 'Merge Cue Sheets');
			}

		$settingTable = new \App\Table\Setting();

		if ($menu = $this->addTopMenu('RWGPS', 'Ride With GPS'))
			{
			$this->addSub($menu, '/RWGPS/find', 'Search RWGPS');
			$this->addSub($menu, '/RWGPS/settings', 'RideWithGPS Settings');
			$this->addSub($menu, '/RWGPS/stats', 'RWGPS Stats');
			$this->addSub($menu, '/RWGPS/upcoming', 'Upcoming RWGPS');
			$this->addSub($menu, '/RWGPS/addUpdate', 'Add / Update RWGPS');
			$url = $settingTable->value('RideWithGPSURL');

			if ($url)
				{
				$this->addSub($menu, $url, 'Club RWGPS Library');
				}
			}

		if ($menu = $this->addTopMenu('Events', 'Events'))
			{
			$this->addSub($menu, '/Events/edit/0', 'Add Event');
			$this->addSub($menu, '/Events/my', 'My Events');
			$this->addSub($menu, '/Events/messages', 'Manage Messages');
			$this->addSub($menu, '/Events/manage/All', 'Manage All Events');
			$this->addSub($menu, '/Events/manage/My', 'Manage My Events');
			$this->addSub($menu, '/Events/upcoming', 'Upcoming Events');
			}

		if ($menu = $this->addTopMenu('Leaders', 'Leaders'))
			{
			$this->addSub($menu, '/Leaders/crashReport', 'Crash Report');
			$this->addSub($menu, '/Leaders/pending', 'Approve Pending Leaders');
			$this->addSub($menu, '/Leaders/apply', 'Become A Ride Leader');
			$this->addSub($menu, '/Leaders/email', 'Email All Leaders');
			$this->addSub($menu, '/Leaders/report', 'Leader Report');
			$this->addSub($menu, '/Leaders/pastRides', 'My Past Leads');
			$this->addSub($menu, '/Leaders/myRides', 'My Upcoming Leads');
			$this->addSub($menu, '/Leaders/show', 'Leaders By Name');
			$this->addSub($menu, '/Leaders/unreported', 'My Unreported Leads');
			$this->addSub($menu, '/Leaders/allUnreported', 'All Unreported Rides');
			$this->addSub($menu, '/Leaders/assistantLeads', 'My Assistant Leads');
			$this->addSub($menu, '/Leaders/minorWaiver', 'Minor Waiver');
			$this->addSub($menu, '/Leaders/nonMemberWaiver', 'Non Member Waiver');

			$configMenu = $this->addMenu($menu, '/Leaders/configure', 'Leader Configuration');
			$this->addSub($configMenu, '/Leaders/settings', 'Ride Settings');
	//		$this->addSub($configMenu, '/Leaders/pace/0', 'All Pace');
			$this->addSub($configMenu, '/Leaders/categories', 'Edit Categories');
			$this->addSub($configMenu, '/Leaders/coordinators', 'Ride Coordinators');
			$this->addSub($configMenu, '/Leaders/newLeader', 'New Leader Email');
			$this->addSub($configMenu, '/Leaders/newRiderEmail', 'New Rider Email');
			$this->addSub($configMenu, '/Leaders/rideStatus', 'Request Ride Status Email');
			$this->addSub($configMenu, '/Leaders/waitListEmail', 'Wait List Email');
			$this->addSub($configMenu, '/Leaders/movePace', 'Move Pace');
			}

		if ($menu = $this->addTopMenu('Locations', 'Locations'))
			{
			$this->addSub($menu, '/Locations/locations', 'Start Locations');
			$this->addSub($menu, '/Locations/merge', 'Merge Start Locations');
			$this->addSub($menu, '/Locations/new', 'Add Start Location');
			}

		if ($menu = $this->addTopMenu('Membership', 'Membership'))
			{
			$this->addSub($menu, '/Membership/editMembership/0', 'Add New Membership');
			$this->addSub($menu, '/Membership/audit', 'Membership Audit');
			$this->addSub($menu, '/Membership/statistics', 'Club Statistics');
			$this->addSub($menu, '/Membership/find', 'Find Members');
			$this->addSub($menu, '/Membership/myInfo', 'My Info');
			$this->addSub($menu, '/Membership/myNotifications', 'My Notifications');
			$this->addSub($menu, '/Membership/roster', 'Club Roster');
			$this->addSub($menu, '/Membership/rosterReport', 'Roster Report');
			$this->addSub($menu, '/Membership/password', 'Change My Password');
			$this->addSub($menu, '/Membership/combineMemberships', 'Combine Memberships');
			$this->addSub($menu, '/Membership/combineMembers', 'Combine Members');
			$this->addSub($menu, '/Membership/card', 'Membership Card');
			$this->addSub($menu, '/Membership/emailAll', 'Email All Members');
			$this->addSub($menu, '/Membership/subscriptions', 'Update Subscriptions');
			$this->addSub($menu, '/Membership/minor', 'Print Minor Release');
			$this->addSub($menu, '/Membership/configure', 'Membership Configuration');
			$this->addSub($menu, '/Membership/qrCodes', 'Membership QR Codes');
			$this->addSub($menu, '/Membership/mom/' . \App\Tools\Date::year(\App\Tools\Date::today()), 'Member Of The Month');
			$this->addSub($menu, '/Membership/emails', 'Membership Emails');
			$this->addSub($menu, '/Membership/confirm', 'Membership Confirm');
			$this->addSub($menu, '/Membership/newMembers', 'New Members');
			$this->addSub($menu, '/Membership/csv', 'Download CSV');
			$this->addSub($menu, '/Membership/recent', 'Recent Sign Ins');
	//		$this->addSub($menu, '/Membership/Subscription', 'Manage My Subscription');
			$this->addSub($menu, '/Membership/renew', 'Renew My Membership');
			}

		if ($menu = $this->addTopMenu('GA', $settingTable->value('generalAdmissionName', 'General Admission')))
			{
			$this->addSub($menu, '/GA/manage', 'Manage Dates');
			$this->addSub($menu, '/GA/editRider/0', 'Add Registration');
			$this->addSub($menu, '/GA/email', 'Email Registrants');
			$this->addSub($menu, '/GA/labels', 'Mailing Labels');
			$this->addSub($menu, '/GA/landingPageEditor', 'Landing Page Editor');
			$this->addSub($menu, '/GA/register', 'Register');
			$this->addSub($menu, '/GA/find', 'Find Registrants');
			$this->addSub($menu, '/GA/download', 'Download Registrants');
			}

		if ($menu = $this->addTopMenu('Volunteer', 'Volunteer'))
			{
			$this->addSub($menu, '/Volunteer/myJobs', 'My Assignments');
			$this->addSub($menu, '/Volunteer/events', 'Volunteer Events');
			$this->addSub($menu, '/Volunteer/myPoints', 'My Points');
			$this->addSub($menu, '/Volunteer/pointHistory', 'Point History');
			$this->addSub($menu, '/Volunteer/points', 'Outstanding Volunteer Points');
			$this->addSub($menu, '/Volunteer/pointsReport', 'Volunteer Points Report');
			$this->addSub($menu, '/Volunteer/pointsSettings', 'Volunteer Points Settings');
			$this->addSub($menu, '/Volunteer/historyReport', 'Volunteer History Report');
			}

		if ($menu = $this->addTopMenu('Video', 'Videos'))
			{
			$this->addSub($menu, '/Video/addVideo', 'Add Video');
			$this->addSub($menu, '/Video/types', 'Video Types');
			$this->addSub($menu, '/Video/search', 'Find Videos');
			$this->addSub($menu, '/Video/all', 'All Videos');
			}

		if ($menu = $this->addTopMenu('Store', 'Store'))
			{
			$this->addSub($menu, '/Store/addItem', 'Add Store Item');
			$this->addSub($menu, '/Store/DiscountCodes/list', 'Discount Codes');
			$this->addSub($menu, '/Store/shop', 'Shop');
			$this->addSub($menu, '/Store/inventory', 'Manage Inventory');
			$this->addSub($menu, '/Store/inventoryReport', 'Inventory Report');
			$this->addSub($menu, '/Store/invoiceReport', 'Invoice Report');
			$this->addSub($menu, '/Store/email', 'Email Buyers');
			$this->addSub($menu, '/Store/find', 'Find Invoice');
			$this->addSub($menu, '/Store/unshipped', 'Unshipped Invoices');
			$this->addSub($menu, '/Store/cart', 'My Cart');
			$this->addSub($menu, '/Store/checkout', 'Check Out');
			$this->addSub($menu, '/Store/myOrders', 'My Completed Orders');
			$this->addSub($menu, '/Store/configuration', 'Store Configuration');
			$this->addSub($menu, '/Store/Options/list', 'Store Options');
			$this->addSub($menu, '/Store/Orders/list', 'Store Orders');
			$this->addSub($menu, '/Store/myUnpaid', 'My Unpaid Invoices');
			}

		if ($menu = $this->addTopMenu('File', 'Files'))
			{
			$this->addSub($menu, '/File/browse', 'Browse Files');
			$this->addSub($menu, '/File/myFiles', 'My Files');
			$this->addSub($menu, '/File/search', 'Find Files');
			}

		if ($menu = $this->addTopMenu('Content', 'Content'))
			{
			$this->addSub($menu, '/Content/newStory', 'Add Content');
			$this->addSub($menu, '/Content/SlideShow/list', 'Slide Shows');
			$this->addSub($menu, '/Content/recent', 'Recent Content');
			$this->addSub($menu, '/Content/search', 'Search Content');
			$this->addSub($menu, '/Content/categories', 'Content By Category');
			$this->addSub($menu, '/Content/orphan', 'Show Orphan Content');
			$this->addSub($menu, '/Content/purge', 'Purge Expired Content');
			}

		if ($menu = $this->addTopMenu('Polls', 'Polls'))
			{
			$this->addSub($menu, '/Polls/edit/0', 'Add Poll');
			$this->addSub($menu, '/Polls/past', 'Past Polls');
			$this->addSub($menu, '/Polls/current', 'Current Polls');
			$this->addSub($menu, '/Polls/future', 'Future Polls');
			$this->addSub($menu, '/Polls/myVotes', 'My Votes');
			$this->addSub($menu, '/Polls/myMembershipVotes', 'My Membership Votes');
			}

		$settingTable = new \App\Table\Setting();

		$calendarName = $settingTable->value('calendarName');

		if (! empty($calendarName))
			{
			if ($menu = $this->addTopMenu('Calendar', 'Calendar'))
				{
				$this->addSub($menu, '/Calendar/notes', 'Calendar Notes');
				$this->addSub($menu, '/Calendar/addEvent', 'Add Calendar Event');
				$configMenu = $this->addMenu($menu, '/Calendar/configure', 'Calendar Configuration');
				$this->addSub($configMenu, '/Calendar/acceptEmail', 'Accept Calendar Email');
				$this->addSub($configMenu, '/Calendar/rejectEmail', 'Reject Calendar Email');
				$this->addSub($configMenu, '/Calendar/thankYouEmail', 'Thank You Calendar Email');
				$this->addSub($configMenu, '/Calendar/coordinator', 'Calendar Coordinator');
				$this->addSub($menu, '/Calendar/events', $calendarName);
				$this->addSub($menu, '/Calendar/pending', 'Pending Calendar Events');
				$this->addSub($menu, '/Calendar/rejected', 'Rejected Calendar Events');
				}
			}

		if ($menu = $this->addTopMenu('Finance', 'Finances'))
			{
			$this->addSub($menu, '/Finance/store', 'Store Payment Summary');
			$this->addSub($menu, '/Finance/invoice', 'Invoice Summary');
			$this->addSub($menu, '/Finance/checksReceived', 'Print Checks Received');
			$this->addSub($menu, '/Finance/maintenance', 'Check Maintenance');
			$this->addSub($menu, '/Finance/payPal', 'PayPal Settings');
			$this->addSub($menu, '/Finance/importTaxTable', 'Import Tax Table');
			$this->addSub($menu, '/Finance/tax', 'Taxes Collected');
			$this->addSub($menu, '/Finance/payPalTerms', 'PayPal Terms and Conditions');
			$this->addSub($menu, '/Finance/checksNotReceived', 'Unreceived Checks');
			$this->addSub($menu, '/Finance/missingInvoices', 'Missing Invoices');
			}

		if ($menu = $this->addTopMenu('Banners', 'Banners'))
			{
			$this->addSub($menu, '/Banners/addBanner', 'Add Banner');
			$this->addSub($menu, '/Banners/allBanners', 'All Banners');
			$this->addSub($menu, '/Banners/pending', 'Pending Banners');
			$this->addSub($menu, '/Banners/past', 'Past Banners');
			$this->addSub($menu, '/Banners/current', 'Current Banners');
			$this->addSub($menu, '/Banners/active', 'Active Banners');
			$this->addSub($menu, '/Banners/settings', 'Banner Settings');
			}

		if ($menu = $this->addTopMenu('SignInSheets', 'Sign In Sheets'))
			{
			$this->addSub($menu, '/SignInSheets/pending', 'Pending Sign In Sheets');
			$this->addSub($menu, '/SignInSheets/find', 'Search Sign In Sheets');
			$this->addSub($menu, '/SignInSheets/my', 'My Sign In Sheets');
			$this->addSub($menu, '/SignInSheets/rejectEmail', 'Reject Sign In Sheet Email');
			$this->addSub($menu, '/SignInSheets/settings', 'Sign In Sheets Configuration');
			$this->addSub($menu, '/SignInSheets/tips', 'Sign In Sheets Tips');
			$this->addSub($menu, '/SignInSheets/acceptEmail', 'Accept Sign In Sheet Email');
			}

		if ($menu = $this->addTopMenu('Admin', 'Administration'))
			{
			$this->addSub($menu, '/Admin/bikeShopAreas', 'Bike Shop Areas');
			$this->addSub($menu, '/Admin/bikeShopList', 'Bike Shop Maintenance');
			$this->addSub($menu, '/Admin/board', 'Board Members');
			$this->addSub($menu, '/Admin/myPermissions', 'My Permissions');
			$this->addSub($menu, '/Admin/images', 'System Images');
			$this->addSub($menu, '/Admin/permissions', 'Permissions');
			$this->addSub($menu, '/Admin/publicPage', 'Public Pages');
			$this->addSub($menu, '/Admin/permissionGroups', 'Permission Groups');
			$this->addSub($menu, '/Admin/clubEmails', 'Club Email Addresses');
			$this->addSub($menu, '/Admin/emailQueue', 'Email Queue');
			$this->addSub($menu, '/Admin/editWaiver', 'Waiver Editor');
			$this->addSub($menu, '/Admin/journalQueue', 'Journal Queue');
			$this->addSub($menu, '/Admin/blackList', 'Email Blacklist');
			$this->addSub($menu, '/Admin/config', 'Site Configuration');
			$this->addSub($menu, '/Admin/files', 'Manage Files');
			$this->addSub($menu, '/Admin/permissionGroupAssignment', 'Permission Group Assignments');
			$this->addSub($menu, '/Admin/passwordPolicy', 'Password Policy');
			}

		if ($menu = $this->addTopMenu('System', 'System'))
			{
			$this->addSub($menu, '/System/API/users', 'API Users');
			$this->addSub($menu, '/System/Settings/analytics', 'Google Analytics Settings');
			$this->addSub($menu, '/System/auditTrail', 'Audit Trail');
			$this->addSub($menu, '/System/Settings/captcha', 'Google ReCAPTCHA');
			$this->addSub($menu, '/System/Settings/tinify', 'Tinify API Settings');
			$this->addSub($menu, '/System/Settings/constantContact', 'Constant Contact Settings');
			$this->addSub($menu, '/System/Settings/sparkpost', 'SparkPost API Settings');
			$this->addSub($menu, '/System/Settings/email', 'Email Processor Settings');
			$this->addSub($menu, '/System/Settings/favIcon', 'Set FavIcon');
			$this->addSub($menu, '/System/importSQL', 'Import SQL');
			$this->addSub($menu, '/System/inputTest', 'Input Test');
			$this->addSub($menu, '/System/permission', 'Permission Reloader');
			$this->addSub($menu, '/System/inputNormal', 'Input Normal');
			$this->addSub($menu, '/System/cron', 'Cron Jobs');
			$this->addSub($menu, '/System/license', 'License');
			$this->addSub($menu, '/System/pHPInfo', 'PHP Info');
			$this->addSub($menu, '/System/debug', 'Debug Status');
			$this->addSub($menu, '/System/redirects', 'Redirects');
			$this->addSub($menu, '/System/sessionInfo', 'Session Info');
			$this->addSub($menu, '/System/Settings/sms', 'SMS Settings');
			$this->addSub($menu, '/System/migrations', 'Migrations');
			$this->addSub($menu, '/System/Settings/smtp', 'SMTP Settings');
			$this->addSub($menu, '/System/Settings/slack', 'Slack Settings');
			$this->addSub($menu, '/System/docs', 'PHP Documentation');
			$this->addSub($menu, '/System/releaseNotes', 'Release Notes');
			$this->addSub($menu, '/System/releases', 'Releases');
			$this->addSub($menu, '/System/versions/origin/master', 'Versions');
			}
		}

	public function getActiveMenu() : string
		{
		return $this->activeMenu;
		}

	public function getLandingPage(\App\View\Page $page, string $section) : \App\UI\LandingPage
		{
		$landingPage = new \App\UI\LandingPage($page);

		if (isset($this->theMenu[$section]))
			{
			foreach ($this->theMenu[$section]->getMenuItems() as $menuItem)
				{
				if ($menuItem instanceof \PHPFUI\MenuItem)
					{
					$landingPage->addMenuItem($menuItem);
					}
				}
			}

		return $landingPage;
		}

	public function getMenuSections() : array
		{
		return $this->theMenu;
		}

	/**
	 * @return string[]
	 *
	 * @psalm-return list<string>
	 */
	public function getSectionURLs() : array
		{
		$returnValue = [];

		foreach ($this->theMenu as $key => $menu)
			{
			foreach ($menu->getMenuItems() as $menuItem)
			$returnValue[] = $menuItem->getLink();
			}

		return $returnValue;
		}

	protected function getStart() : string
		{
		$this->walk('sort');

		return parent::getStart();
		}

	private function addMenu(\PHPFUI\Menu $parentMenu, string $menuName, string $name) : ?\PHPFUI\Menu
		{
		$this->currentMenu = $menuName;

		$menu = null;

		if ($this->permissions->isAuthorized($name, $menuName))
			{
			$menu = new \PHPFUI\Menu();
			$parentMenu->addSubMenu(new \PHPFUI\MenuItem($name), $menu);

			if ($this->activeLink == $menuName)
				{
				$parentMenu->addClass('is-active');
				$menu->addClass('is-active');
				}
			$this->theMenu[$menuName] = $menu;
			}

		return $menu;
		}

	private function addSub(\PHPFUI\Menu $parentMenu, string $page, string $name) : static
		{
		if ($this->permissions->isAuthorized($name, $this->currentMenu))
			{
			$urlParts = \parse_url($page);

			$target = '';

			if (isset($urlParts['scheme']))
				{
				// do nothing, outside link
				$target = '_blank';
				}

			$menuItem = new \PHPFUI\MenuItem($name, $page);

			if ($target)
				{
				$menuItem->getLinkObject()->addAttribute('target', $target);
				}

			if ($this->activeLink == $page)
				{
				$menuItem->setActive();
				}

			$parentMenu->addMenuItem($menuItem);
			}

		return $this;
		}

	private function addTopMenu(string $menuName, string $name) : ?\PHPFUI\Menu
		{
		$this->currentMenu = $menuName;

		$menu = null;

		if ($this->permissions->isAuthorized($name, $menuName))
			{
			$menu = new \PHPFUI\Menu();
			$this->addSubMenu(new \PHPFUI\MenuItem($name), $menu);
			$this->theMenu[$menuName] = $menu;
			}

		return $menu;
		}
	}
