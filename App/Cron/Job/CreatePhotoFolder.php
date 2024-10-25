<?php

namespace App\Cron\Job;

class CreatePhotoFolder extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Create Year Photo Folder.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$year = $this->controller->runningAtYear();
		$key = ['name' => "{$year}", 'parentFolderId' => 0];
		$folder = new \App\Record\Folder($key);

		if ($folder->empty())
			{
			$folder->setFrom($key);
			$folder->folderType = \App\Enum\FolderType::PHOTO;
			$folder->insert();
			}

		$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
		$name = 'Thanksgiving';
		$banner = new \App\Record\Banner(['description' => $name]);

		if (! $banner->loaded())
			{
			$banner->url = $host . '/Holiday/thanksgiving';
			$banner->description = $name;
			$banner->pending = 0;
			$banner->html = '<div class="slide">
<div class="cartoon">
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="tail"></div>
  <div class="leg"></div>
  <div class="leg"></div>
  <div class="body"></div>
  <div class="wing"></div>
  <div class="wing"></div>
  <div class="hat-top"></div>
  <div class="hat-bottom"></div>
  <div class="head">
    <div class="eye"></div>
    <div class="beak"></div>
    <div class="red"></div>
  </div>
</div>
</div>';
			$banner->css = '.slide {
  background-color: #ffc300;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 15rem;
}
.cartoon {
  height: 6.7rem;
  width: 20.368rem;
  font-size: 0.536rem;
  position: relative;
  margin: 2rem;
  text-align: center;
  position: absolute;
  --brown1: #753;
  --brown2: #975;
  --orange1: orange;
  --orange2: darkorange;
  width: 80vmin;
  height: 80vmin;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.cartoon div {
  position: absolute;
  box-sizing: border-box;
}

.body {
  width: 40%;
  height: 40%;
  background: var(--brown1);
  color: var(--brown1);
  border-radius: 50%;
  top: 40%;
  left: 50%;
  transform: translate(-50%, 0);
  box-shadow:
    -4vmin 14.5vmin 0 -13vmin,
     4vmin 14.5vmin 0 -13vmin
}

.wing {
  height: 30%;
  width: 20%;
  background: var(--brown2);
  border-radius: 100% 0% 100% 5%;
  top: 41%;
  left: 23%;
  transform-origin: top right;
  transform: rotate(-10deg);
}

.wing + .wing {
  left: auto;
  right: 43%;
  transform: scaleX(-1) rotate(-10deg);
}

.head {
  width: 19%;
  height: 19%;
  border-radius: 50%;
  background: var(--brown2);
  top: 15%;
  left: 50%;
  transform: translate(-50%, 0);
  clip-path: polygon(0% 20%, 100% 20%, 100% 500%, 0% 500%);
}

.head::before {
  content: "";
  display: block;
  position: absolute;
  width: 40%;
  height: 120%;
  background: var(--brown2);
  top: 80%;
  left: 50%;
  transform: translate(-50%, 0);
  border-radius: 0 0 100% 100% / 30%;
}

.eye {
  width: 10%;
  height: 10%;
  background: black;
  border-radius: 50%;
  top: 45%;
  left: 50%;
  transform: translate(-50%, 0) translate(-2.5vmin, 0);
  box-shadow: 5vmin 0
}

.beak {
  width: 4vmin;
  height: 3vmin;
  border-left: 4vmin solid var(--orange1);
  border-top: 1.5vmin solid transparent;
  border-bottom: 1.5vmin solid transparent;
  top: 60%;
  left: 50%;
}

.red {
  width: 2.5vmin;
  height: 6vmin;
  background: #d00;
  border-radius: 100% / 100% 0 100% 100%;
  top: 60%;
  left: 50%;
  transform: translate(-100%, 0);
}

.leg {
  width: 3%;
  height: 21%;
  background: var(--orange1);
  top: 79%;
  left: 50%;
  transform: translate(-50%, 0) translate(-4vmin, 0);
  border-radius: 5vmin / 50%;
  background-image:
    repeating-linear-gradient(transparent 0 10%, rgba(0,0,0,0.1) 0 11%);
}

.leg + .leg {
  transform: translate(-50%, 0) translate(4vmin, 0);
}

.leg::before,
.leg::after {
  content: "";
  display: block;
  position: absolute;
  width: 80%;
  height: 35%;
  left: 50%;
  bottom: 0;
  background: var(--orange1);
  border-radius: 5vmin;
  transform-origin: 50% 0;
  transform: translate(-50%, 0) rotate(30deg);
}
.leg::after {
  transform: translate(-50%, 0) rotate(-30deg);
}

.hat-bottom {
  width: 35%;
  height: 8%;
  border-radius: 50%;
  background: black;
  top: 15%;
  left: 50%;
  transform: translate(-50%, 0);
}

.hat-top {
  width: 16%;
  height: 20%;
  background: black;
  box-shadow: inset 0 -7vmin #fff3;
  border-radius: 100% / 230% 230% 0% 0%;
  top: 0;
  left: 50%;
  transform: translate(-50%, 0);
}

.hat-top::before {
  content: "";
  display: block;
  position: absolute;
  width: 40%;
  height: 2vmin;
  border: 1vmin solid #772;
  border-radius: 1vmin;
  bottom: 3.25vmin;
  left: 50%;
  transform: translate(-50%, 0);
}

.tail {
  width: 10%;
  height: 40%;
  background: var(--orange1);
  border-radius: 100% / 30% 30% 170% 170%;
  transform-origin: 50% 100%;
  top: 30%;
  left: 50%;
  transform: translate(-50%, 0);
}

.tail:nth-child(odd) {
  background: var(--orange1);
}

.tail:nth-child(even) {
  background: var(--orange2);
}

.tail:nth-child(1) { transform: translate(-50%, 0) rotate(0); }
.tail:nth-child(2) { transform: translate(-50%, 0) rotate(17deg); }
.tail:nth-child(3) { transform: translate(-50%, 0) rotate(34deg); }
.tail:nth-child(4) { transform: translate(-50%, 0) rotate(51deg); }
.tail:nth-child(5) { transform: translate(-50%, 0) rotate(68deg); }
.tail:nth-child(6) { transform: translate(-50%, 0) rotate(85deg); }
.tail:nth-child(7) { transform: translate(-50%, 0) rotate(102deg); }
.tail:nth-child(8) { transform: translate(-50%, 0) rotate(-17deg); }
.tail:nth-child(9) { transform: translate(-50%, 0) rotate(-34deg); }
.tail:nth-child(10) { transform: translate(-50%, 0) rotate(-51deg); }
.tail:nth-child(11) { transform: translate(-50%, 0) rotate(-68deg); }
.tail:nth-child(12) { transform: translate(-50%, 0) rotate(-85deg); }
.tail:nth-child(13) { transform: translate(-50%, 0) rotate(-102deg); }


/***/

#youtube {
  z-index: 2;
  display: block;
  width: 100px;
  height: 70px;
  position: absolute;
  bottom: 20px;
  right: 20px;
  background: red;
  border-radius: 50% / 11%;
  transition: transform 0.5s;
}

#youtube:hover,
#youtube:focus {
  transform: scale(1.1);
}

#youtube::before {
  content: "";
  display: block;
  position: absolute;
  top: 7.5%;
  left: -6%;
  width: 112%;
  height: 85%;
  background: red;
  border-radius: 9% / 50%;
}

#youtube::after {
  content: "";
  display: block;
  position: absolute;
  top: 20px;
  left: 40px;
  width: 45px;
  height: 30px;
  border: 15px solid transparent;
  box-sizing: border-box;
  border-left: 30px solid white;
}

#youtube span {
  font-size: 0;
  position: absolute;
  width: 0;
  height: 0;
  overflow: hidden;
}


/****/
a.dev {
  width: 70px;
  height: 70px;
  position: fixed;
  bottom: 20px;
  right: 135px;
  background: black;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.5s;
  text-decoration: none;
  font-size: 25px;
  font-weight: bold;
  font-family: Arial, sans-serif;
  border-radius: 4px;
}

a.dev span {
  position: absolute;
  left: -10000px;
  width: 1px;
  height: 1px;
  overflow: hidden;
}

a.dev:hover {
  transform: scale(1.1)
}';
			$banner->insert();
			}
		$year = \App\Tools\Date::year(-1);
		$firstThursday = new \DateTime("first Thursday of November {$year}");
		$diff = new \DateInterval('P3W');
		$thanksgiving = $firstThursday->add($diff);
		$this->updateBanner($banner, 11, (int)$thanksgiving->format('d'));

		$name = 'New Years Day';
		$banner = new \App\Record\Banner(['description' => $name]);

		if (! $banner->loaded())
			{
			$banner->url = $host . '/Holiday/newYearsDay';
			$banner->description = $name;
			$banner->pending = 0;
			$banner->html = '';
			$banner->css = '';
			$banner->insert();
			}
		$this->updateBanner($banner, 1, 1);

		$name = 'Independence Day';
		$banner = new \App\Record\Banner(['description' => $name]);

		if (! $banner->loaded())
			{
			$banner->url = $host . '/Holiday/independenceDay';
			$banner->description = $name;
			$banner->pending = 0;
			$banner->html = '<div class="slide"><b class="US_flag"></b></div>';
			$banner->css = '.slide {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 20rem;
}
.US_flag{
	left: -250px;
	top: 10%;
	margin-left: 50%;
	position: absolute;
	display: block;
	width: 500px;
	height: 20px;
	background: #cc0000;
	box-shadow: 0 20px 0 #f3f3f3, 0 40px 0 #cc0000, 0 60px 0 #f3f3f3, 0 80px 0 #cc0000, 0 100px 0 #f3f3f3, 0 120px 0 #cc0000, 0 140px 0 #f3f3f3, 0 160px 0 #cc0000, 0 180px 0 #f3f3f3, 0 200px 0 #cc0000, 0 220px 0 #f3f3f3, 0 240px 0 #cc0000;
}
.US_flag:before{
	content: "\0020";
	background: #191b6d;
	display: block;
	width: 200px;
	height: 140px;
	position: absolute;
}
.US_flag:after{
	content: "\2605";
	font-size: 14px;
	color: #f3f3f3;
	text-style: none;
	display: block;
	width: 500px;
	position: absolute;
	margin: 5px;
	text-shadow: 35px 0 0 #f3f3f3, 70px 0 0 #f3f3f3, 105px 0 0 #f3f3f3, 140px 0 0 #f3f3f3, 175px 0 0 #f3f3f3, 17px 14px 0 #f3f3f3, 52px 14px 0 #f3f3f3,   87px 14px 0 #f3f3f3,  122px 14px 0 #f3f3f3, 157px 14px 0 #f3f3f3, 0 28px 0 #f3f3f3, 35px 28px 0 #f3f3f3, 70px 28px 0 #f3f3f3, 105px 28px 0 #f3f3f3, 140px 28px 0 #f3f3f3, 175px 28px 0 #f3f3f3, 17px 42px 0 #f3f3f3, 52px 42px 0 #f3f3f3,   87px 42px 0 #f3f3f3,  122px 42px 0 #f3f3f3, 157px 42px 0 #f3f3f3, 0 56px 0 #f3f3f3, 35px 56px 0 #f3f3f3, 70px 56px 0 #f3f3f3, 105px 56px 0 #f3f3f3, 140px 56px 0 #f3f3f3, 175px 56px 0 #f3f3f3,    17px 70px 0 #f3f3f3, 52px 70px 0 #f3f3f3,   87px 70px 0 #f3f3f3,  122px 70px 0 #f3f3f3, 157px 70px 0 #f3f3f3, 0 84px 0 #f3f3f3, 35px 84px 0 #f3f3f3, 70px 84px 0 #f3f3f3, 105px 84px 0 #f3f3f3, 140px 84px 0 #f3f3f3, 175px 84px 0 #f3f3f3, 17px 98px 0 #f3f3f3, 52px 98px 0 #f3f3f3,   87px 98px 0 #f3f3f3,  122px 98px 0 #f3f3f3, 157px 98px 0 #f3f3f3, 0 112px 0 #f3f3f3, 35px 112px 0 #f3f3f3, 70px 112px 0 #f3f3f3, 105px 112px 0 #f3f3f3, 140px 112px 0 #f3f3f3, 175px 112px 0 #f3f3f3;
}';
			$banner->insert();
			}
		$this->updateBanner($banner, 1, 1);

		$name = 'Happy Halloween';
		$banner = new \App\Record\Banner(['description' => $name]);

		if (! $banner->loaded())
			{
			$banner->url = $host . '/Holiday/halloween';
			$banner->description = $name;
			$banner->pending = 0;
			$banner->html = '<div class="slide">
<div class="bat">
  <span class="sr-only">Sona the Bat</span>
  <div class="head">
    <span class="sr-only">Head</span>
    <div class="eye">
      <span class="sr-only">Left Eye</span>
    </div>
    <div class="eye">
      <span class="sr-only">Right Eye</span>
    </div>
  </div>
  <div class="bod">
    <span class="sr-only">Body</span>
    <div class="wings">
      <span class="sr-only">Wings</span>
    </div>
    <div class="tail">
      <span class="sr-only">Tail</span>
    </div>
  </div>
</div>
</div>';
			$banner->css = '.bat {
  background-color: #1a1a1a;
  height: 6.7rem;
  width: 20.368rem;
  font-size: 0.536rem;
  position: relative;
  margin: 2rem;
  text-align: center;
}
.bat:before {
  content: "";
  display: block;
  position: absolute;
  border-radius: 50%;
  height: 10.71em;
  width: 12.6em;
  background: #ffc300;
  top: -6.3em;
  left: 50%;
  margin-left: -6.3em;
}
.head {
  background: #1a1a1a;
  border-radius: 50%;
  height: 6.615em;
  width: 6.3em;
  position: relative;
  margin: 0 auto;
  z-index: 100;
}
.head:before, .head:after {
  content: "";
  display: block;
  position: absolute;
  height: 6em;
  width: 6em;
  background-color: #1a1a1a;
  border-radius: 6em 0px;
  top: -1em;
  z-index: -1;
}
.head:before {
  transform: rotate(110deg);
  left: -1.35em;
}
.head:after {
  right: -1.35em;
  transform: rotate(-20deg);
}
.eye {
  background: #fff;
  border-radius: 50%;
  height: 1.0184rem;
  width: 1.0184rem;
  position: absolute;
  top: 1.75em;
  left: 1em;
}
.eye:before {
  height: 0.5em;
  width: 0.5em;
  background: #fff;
  border-radius: 50%;
  content: "";
  position: absolute;
  top: 32%;
  left: 30%;
  box-shadow: 0.25em 0.2em 0 0.25em #000;
}
.eye + .eye {
  right: 1em;
  left: initial;
}
.bod:before, .bod:after {
  content: "";
  display: block;
  position: absolute;
  background: #ffc300;
  height: 10.184rem;
  width: 6.1104rem;
  top: -6.92512rem;
}
.bod:before {
  left: 0;
  transform: rotate(75deg);
}
.bod:after {
  transform: rotate(-75deg);
  right: 0;
}
.wings {
  height: 0;
  width: 0;
  border-style: solid;
  border-width: 0 11em 7em 11em;
  border-color: transparent transparent #ffc300 transparent;
  margin-left: -11em;
  position: absolute;
  bottom: -3.5em;
  left: 50%;
}
.wings:before, .wings:after {
  content: "";
  display: block;
  position: absolute;
  background: #ffc300;
  border-radius: 50%;
  height: 10.71em;
  width: 12.6em;
  position: absolute;
  bottom: -5.775em;
}
.wings:before {
  box-shadow: 8em 5em 0 #ffc300;
  left: -24em;
}
.wings:after {
  box-shadow: -8em 5em 0 #ffc300;
  right: -24em;
}
.tail {
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 8em 4em 0 4em;
  border-color: #1a1a1a transparent transparent transparent;
  margin: 0 auto;
  position: relative;
}
.slide {
  font-size: 1vw;
  background-color: #ffc300;
  display: flex;
  justify-content: center;
  align-items: center;
}
.sr-only {
  position: absolute;
  left: -9999em;
}';
			$banner->insert();
			}
		$this->updateBanner($banner, 10, 31);

		}

	public function willRun() : bool
		{
		return $this->controller->runDayOfMonth(1) && $this->controller->runMonth(1) && $this->controller->runAt(0, 0);
		}

	private function updateBanner(\App\Record\Banner $banner, int $month, int $day) : void
		{
		$banner->startDate = $banner->endDate = \App\Tools\Date::makeString(\App\Tools\Date::year(-1), $month, $day);
		$banner->update();
		}
	}
