<?php

namespace App\View;

class MainMenu extends \PHPFUI\AccordionMenu
	{
	private string $activeLink = '';

	private string $activeMenu = '';

	private string $currentMenu = '';

	private string $currentName = '';

	private bool $started = false;

	private array $theMenu = [];

	public function __construct(private readonly \App\Model\PermissionBase $permissions)
		{
		parent::__construct();

		$this->addTopMenu('Rides', 'Rides');
		$this->addSub('edit/0', 'Add A Ride');
		$this->addSub('addByCueSheet', 'Add CueSheet Ride');
		$this->addSub('past', 'Past Rides');
		$this->addSub('myPast', 'My Past Rides');
		$this->addSub('attendance', 'Ride Attendance');
		$this->addSub('schedule', 'Ride Schedule');
		$this->addSub('statistics', 'Ride Statistics');
		$this->addSub('search', 'Search Rides');
		$this->addSub('myCategoryRides', 'Rides In My Category');
		$this->addSub('csv', 'Download Rides.csv');

		$this->addTopMenu('Newsletter', 'Newsletters');
		$this->addSub('all', 'Newsletters');
		$this->addSub('upload', 'Add A Newsletter');
		$this->addSub('settings', 'Newsletter Settings');

		$this->addTopMenu('News', 'News');
		$this->addSub('latest', 'Latest News');
		$this->addSub('board', 'Board Minutes');

		$this->addTopMenu('Photo', 'Photos');
		$this->addSub('browse', 'Browse Photos');
		$this->addSub('myPhotos', 'My Photos');
		$this->addSub('taggers', 'Top Taggers');
		$this->addSub('inPhotos', 'In Photos');
		$this->addSub('search', 'Find Photos');
		$this->addSub('mostTagged', 'Most Tagged');

		$this->addTopMenu('Forums', 'Forums');
		$this->addSub('manage', 'Manage Forums');
		$this->addSub('my', 'My Forums');

		$forumTable = new \App\Table\Forum();
		$forumTable->addOrderBy('name');

		foreach ($forumTable->getRecordCursor() as $forum)
			{
			if ($permissions->isAuthorized($forum->name, 'Forums'))
				{
				$this->addSub('home/' . $forum->forumId, $forum->name);
				}
			}

		$this->addTopMenu('CueSheets', 'Cue Sheets');
		$settingTable = new \App\Table\Setting();
		$this->addSub('addCue', 'Add A New Cue Sheet');
		$this->addSub('pending', 'Approve Cue Sheets');
		$this->addSub('notes', 'Cue Sheet Notes');
		$this->addSub('configure', 'Cue Sheet Configuration');
		$this->addSub('find', 'Find A Cue Sheet');
		$this->addSub('my', 'My Cue Sheets');
		$this->addSub('recent', 'Recent Cue Sheets');
		$this->addSub('statistics', 'Cue Sheet Statistics');
		$this->addSub('templates', 'Cue Sheet Templates');
		$this->addSub('merge', 'Merge Cue Sheets');

		$this->addTopMenu('RWGPS', 'Ride With GPS');
		$this->addSub('find', 'Search RWGPS');
		$this->addSub('settings', 'RideWithGPS Settings');
		$this->addSub('stats', 'RWGPS Stats');
		$this->addSub('upcoming', 'Upcoming RWGPS');
		$this->addSub('addUpdate', 'Add / Update RWGPS');
		$url = $settingTable->value('RideWithGPSURL');

		if ($url)
			{
			$this->addSub($url, 'Club RWGPS Library');
			}

		$this->addTopMenu('Events', 'Events');
		$this->addSub('edit/0', 'Add Event');
		$this->addSub('my', 'My Events');
		$this->addSub('messages', 'Manage Messages');
		$this->addSub('manage/All', 'Manage All Events');
		$this->addSub('manage/My', 'Manage My Events');
		$this->addSub('upcoming', 'Upcoming Events');

		$this->addTopMenu('Leaders', 'Leaders');
		$this->addSub('crashReport', 'Crash Report');
		$this->addSub('pending', 'Approve Pending Leaders');
		$this->addSub('apply', 'Become A Ride Leader');
		$this->addSub('email', 'Email All Leaders');
		$this->addSub('configure', 'Leader Configuration');
		$this->addSub('report', 'Leader Report');
		$this->addSub('pastRides', 'My Past Leads');
		$this->addSub('myRides', 'My Upcoming Leads');
		$this->addSub('show', 'Leaders By Name');
		$this->addSub('unreported', 'My Unreported Leads');
		$this->addSub('allUnreported', 'All Unreported Rides');
		$this->addSub('assistantLeads', 'My Assistant Leads');
		$this->addSub('minorWaiver', 'Minor Waiver');
		$this->addSub('nonMemberWaiver', 'Non Member Waiver');

		$this->addTopMenu('Locations', 'Locations');
		$this->addSub('locations', 'Start Locations');
		$this->addSub('merge', 'Merge Start Locations');
		$this->addSub('new', 'Add Start Location');

		$this->addTopMenu('Membership', 'Membership');
		$this->addSub('extend', 'Extend Memberships');
		$this->addSub('editMembership/0', 'Add New Membership');
		$this->addSub('audit', 'Membership Audit');
		$this->addSub('statistics', 'Club Statistics');
		$this->addSub('find', 'Find Members');
		$this->addSub('myInfo', 'Edit My Info');
		$this->addSub('myNotifications', 'My Notifications');
		$this->addSub('roster', 'Club Roster');
		$this->addSub('rosterReport', 'Roster Report');
		$this->addSub('password', 'Change My Password');
		$this->addSub('combineMemberships', 'Combine Memberships');
		$this->addSub('combineMembers', 'Combine Members');
		$this->addSub('card', 'Membership Card');
		$this->addSub('emailAll', 'Email All Members');
		$this->addSub('subscriptions', 'Update Subscriptions');
		$this->addSub('minor', 'Print Minor Release');
		$this->addSub('configure', 'Membership Configuration');
		$this->addSub('qrCodes', 'Membership QR Codes');
		$this->addSub('mom/' . \App\Tools\Date::year(\App\Tools\Date::today()), 'Member Of The Month');
		$this->addSub('emails', 'Membership Emails');
		$this->addSub('confirm', 'Membership Confirm');
		$this->addSub('newMembers', 'New Members');
		$this->addSub('csv', 'Download CSV');
		$this->addSub('recent', 'Recent Sign Ins');
//		$this->addSub('Subscription', 'Manage My Subscription');
		$this->addSub('renew', 'Renew My Membership');

		$this->addTopMenu('GA', $settingTable->value('generalAdmissionName', 'General Admission'));
		$this->addSub('manage', 'Manage Dates');
		$this->addSub('editRider/0', 'Add Registration');
		$this->addSub('email', 'Email Registrants');
		$this->addSub('labels', 'Mailing Labels');
		$this->addSub('landingPageEditor', 'Landing Page Editor');
		$this->addSub('register', 'Register');
		$this->addSub('find', 'Find Registrants');
		$this->addSub('download', 'Download Registrants');

		$this->addTopMenu('Volunteer', 'Volunteer');
		$this->addSub('pickAJob', 'Volunteer');
		$this->addSub('myJobs', 'My Assignments');
		$this->addSub('events', 'Volunteer Events');
		$this->addSub('myPoints', 'My Points');
		$this->addSub('pointHistory', 'Point History');
		$this->addSub('points', 'Outstanding Volunteer Points');
		$this->addSub('pointsReport', 'Volunteer Points Report');
		$this->addSub('pointsSettings', 'Volunteer Points Settings');
		$this->addSub('historyReport', 'Volunteer History Report');

		$this->addTopMenu('Video', 'Videos');
		$this->addSub('addVideo', 'Add Video');
		$this->addSub('types', 'Video Types');
		$this->addSub('search', 'Find Videos');
		$this->addSub('all', 'All Videos');

		$this->addTopMenu('Store', 'Store');
		$this->addSub('addItem', 'Add Store Item');
		$this->addSub('DiscountCodes/list', 'Discount Codes');
		$this->addSub('shop', 'Shop');
		$this->addSub('inventory', 'Manage Inventory');
		$this->addSub('inventoryReport', 'Inventory Report');
		$this->addSub('invoiceReport', 'Invoice Report');
		$this->addSub('email', 'Email Buyers');
		$this->addSub('find', 'Find Invoice');
		$this->addSub('unshipped', 'Unshipped Invoices');
		$this->addSub('cart', 'My Cart');
		$this->addSub('checkout', 'Check Out');
		$this->addSub('myOrders', 'My Completed Orders');
		$this->addSub('configuration', 'Store Configuration');
		$this->addSub('Options/list', 'Store Options');
		$this->addSub('Orders/list', 'Store Orders');
		$this->addSub('myUnpaid', 'My Unpaid Invoices');

		$this->addTopMenu('File', 'Files');
		$this->addSub('browse', 'Browse Files');
		$this->addSub('myFiles', 'My Files');
		$this->addSub('search', 'Find Files');

		$this->addTopMenu('Content', 'Content');
		$this->addSub('newStory', 'Add Content');
		$this->addSub('SlideShow/list', 'Slide Shows');
		$this->addSub('recent', 'Recent Content');
		$this->addSub('search', 'Search Content');
		$this->addSub('categories', 'Content By Category');
		$this->addSub('orphan', 'Show Orphan Content');
		$this->addSub('purge', 'Purge Expired Content');

		$this->addTopMenu('Polls', 'Polls');
		$this->addSub('edit/0', 'Add Poll');
		$this->addSub('past', 'Past Polls');
		$this->addSub('current', 'Current Polls');
		$this->addSub('future', 'Future Polls');
		$this->addSub('myVotes', 'My Votes');
		$this->addSub('myMembershipVotes', 'My Membership Votes');

		$settingTable = new \App\Table\Setting();

		$calendarName = $settingTable->value('calendarName');

		if (! empty($calendarName))
			{
			$this->addTopMenu('Calendar', 'Calendar');
			$this->addSub('notes', 'Calendar Notes');
			$this->addSub('addEvent', 'Add Calendar Event');
			$this->addSub('configure', 'Calendar Configuration');
			$this->addSub('events', $calendarName);
			$this->addSub('pending', 'Pending Calendar Events');
			$this->addSub('rejected', 'Rejected Calendar Events');
			}

		$this->addTopMenu('Finance', 'Finances');
		$this->addSub('store', 'Store Payment Summary');
		$this->addSub('invoice', 'Invoice Summary');
		$this->addSub('checksReceived', 'Print Checks Received');
		$this->addSub('maintenance', 'Check Maintenance');
		$this->addSub('payPal', 'PayPal Settings');
		$this->addSub('importTaxTable', 'Import Tax Table');
		$this->addSub('tax', 'Taxes Collected');
		$this->addSub('payPalTerms', 'Edit PayPal Terms and Conditions');
		$this->addSub('checksNotReceived', 'Unreceived Checks');
		$this->addSub('missingInvoices', 'Missing Invoices');

		$this->addTopMenu('Banners', 'Banners');
		$this->addSub('addBanner', 'Add Banner');
		$this->addSub('allBanners', 'All Banners');
		$this->addSub('pending', 'Pending Banners');
		$this->addSub('past', 'Past Banners');
		$this->addSub('current', 'Current Banners');
		$this->addSub('active', 'Active Banners');
		$this->addSub('settings', 'Banner Settings');

		$this->addTopMenu('SignInSheets', 'Sign In Sheets');
		$this->addSub('pending', 'Pending Sign In Sheets');
		$this->addSub('find', 'Search Sign In Sheets');
		$this->addSub('my', 'My Sign In Sheets');
		$this->addSub('rejectEmail', 'Edit Reject Sign In Sheet Email');
		$this->addSub('settings', 'Sign In Sheets Configuration');
		$this->addSub('tips', 'Sign In Sheets Tips');
		$this->addSub('acceptEmail', 'Edit Accept Sign In Sheet Email');

		$this->addTopMenu('Admin', 'Administration');
		$this->addSub('bikeShopAreas', 'Bike Shop Areas');
		$this->addSub('bikeShopList', 'Bike Shop Maintenance');
		$this->addSub('board', 'Edit Board Members');
		$this->addSub('myPermissions', 'My Permissions');
		$this->addSub('images', 'System Images');
		$this->addSub('permissions', 'Permissions');
		$this->addSub('publicPage', 'Edit Public Pages');
		$this->addSub('permissionGroups', 'Permission Groups');
		$this->addSub('clubEmails', 'Club Email Addresses');
		$this->addSub('emailQueue', 'Email Queue');
		$this->addSub('editWaiver', 'Waiver Editor');
		$this->addSub('journalQueue', 'Journal Queue');
		$this->addSub('blackList', 'Email Blacklist');
		$this->addSub('config', 'Site Configuration');
		$this->addSub('files', 'Manage Files');
		$this->addSub('permissionGroupAssignment', 'Permission Group Assignments');
		$this->addSub('passwordPolicy', 'Password Policy');

		$this->addTopMenu('System', 'System');
		$this->addSub('API/users', 'API Users');
		$this->addSub('Settings/analytics', 'Google Analytics Settings');
		$this->addSub('auditTrail', 'Audit Trail');
		$this->addSub('Settings/captcha', 'Google ReCAPTCHA');
		$this->addSub('Settings/tinify', 'Tinify API Settings');
		$this->addSub('Settings/constantContact', 'Constant Contact Settings');
		$this->addSub('Settings/sparkpost', 'SparkPost API Settings');
		$this->addSub('Settings/email', 'Email Processor Settings');
		$this->addSub('Settings/favIcon', 'Set FavIcon');
		$this->addSub('importSQL', 'Import SQL');
		$this->addSub('inputTest', 'Input Test');
		$this->addSub('permission', 'Permission Reloader');
		$this->addSub('inputNormal', 'Input Normal');
		$this->addSub('cron', 'Cron Jobs');
		$this->addSub('license', 'License');
		$this->addSub('pHPInfo', 'PHP Info');
		$this->addSub('debug', 'Debug Status');
		$this->addSub('redirects', 'Redirects');
		$this->addSub('sessionInfo', 'Session Info');
		$this->addSub('Settings/sms', 'SMS Settings');
		$this->addSub('migrations', 'Migrations');
		$this->addSub('Settings/smtp', 'SMTP Settings');
		$this->addSub('Settings/slack', 'Slack Settings');
		$this->addSub('docs', 'PHP Documentation');
		$this->addSub('releaseNotes', 'Release Notes');
		$this->addSub('releases', 'Releases');
		$this->addSub('versions/origin/master', 'Versions');
		}

	public function getActiveMenu() : string
		{
		return $this->activeMenu;
		}

	public function getLandingPage(Page $page, string $section) : \App\UI\LandingPage
		{
		$landingPage = new \App\UI\LandingPage($page);
		$match = "~|~{$section}";

		foreach ($this->theMenu as $key => $menuSection)
			{
			if (\strpos($key, $match))
				{
				foreach ($menuSection->getMenuItems() as $menuItem)
					{
					$landingPage->addLink($menuItem->getLink(), $menuItem->getName());
					}
				$landingPage->sort();

				return $landingPage;
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
			$parts = \explode('|~', $key);
			$returnValue[] = $parts[1];
			}

		return $returnValue;
		}

	public function setActiveLink(string $link) : bool
		{
		$this->activeLink = $link;
		$parts = \explode('/', $link);
		$this->activeMenu = $parts[1];

		return true;
		}

	protected function getStart() : string
		{
		if (! $this->started)
			{
			$this->started = true;

			foreach ($this->theMenu as $menuText => &$menu)
				{
				[$menuText, $menuPath] = \explode('~|~', $menuText);

				if ($menuPath == $this->activeMenu)
					{
					$menu->addClass('is-active');
					}
				$menu->setActiveLink($this->activeLink);
				$menu->sort();

				$this->addSubMenu(new \PHPFUI\MenuItem($menuText), $menu);
				}
			}

		return parent::getStart();
		}

	private function addSub(string $page, string $name) : static
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
			elseif ('/' != $page[0])
				{
				$page = '/' . $this->currentMenu . '/' . $page;
				}
			else
				{
				$page = '/' . $this->currentMenu . $page;
				}
			$currentIndex = "{$this->currentName}~|~{$this->currentMenu}";

			if (isset($this->theMenu[$currentIndex]))
				{
				$menuItem = new \PHPFUI\MenuItem($name, $page);

				if ($target)
					{
					$menuItem->getLinkObject()->addAttribute('target', $target);
					}
				$this->theMenu[$currentIndex]->addMenuItem($menuItem);
				}
			}

		return $this;
		}

	private function addTopMenu(string $menu, string $name) : static
		{
		$this->currentMenu = $menu;
		$this->currentName = $name;

		if ($this->permissions->isAuthorized($name, $menu))
			{
			$this->theMenu["{$name}~|~{$menu}"] = new \PHPFUI\Menu();
			}

		return $this;
		}
	}
