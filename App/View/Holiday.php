<?php

namespace App\View;

class Holiday
	{
	public function __construct(private \App\View\Page $page)
		{
		}

	public function blank() : \PHPFUI\VanillaPage
		{
		$page = new \PHPFUI\VanillaPage();
		$page->add('');
		$css = <<<'STYLESHEET'
STYLESHEET;

		$page->addCSS($css);
		$page->setPageName('');

		return $page;
		}

	public function date(int $month, int $day) : bool
		{
		$today = \App\Tools\Date::today();

		return \App\Tools\Date::month($today) == $month && \App\Tools\Date::day($today) == $day;
		}

	public function halloween() : \PHPFUI\VanillaPage
		{
		$page = new \PHPFUI\VanillaPage();
		$club = $this->page->value('clubName');
		$page->add('<div class="message">
			<h1>Happy Halloween</h1>
		   <span>From ' . $club . '</span>
	   		</div>
		<div class="b-layers">
			<div class="b1">
				<!--blocks-->
				<div class="blocks">
					<div class="block block-1">
						<div class="pumpkins">
							<!--p-->
							<div class="pumpkin">
								<div class="p-eye"></div>
								<div class="p-nose"></div>
								<div class="p-teeth"></div>
							</div>
							<!--p-->
							<!--p-->
							<div class="pumpkin">
								<div class="p-eye"></div>
								<div class="p-nose"></div>
								<div class="p-teeth"></div>
							</div>
							<!--p-->
						</div>
					</div>
					<div class="block block-2">
						<div class="roof"></div>
						<div class="mid">
							<div class="windows">
								<div class="win"></div>
								<div class="win"></div>
								<div class="win"></div>

							</div>
						</div>
					</div>
					<div class="block block-3">
						<div class="tower">
							<div class="windows">
								<div class="win"></div>
								<div class="win"></div>

							</div>
						</div>
					</div>
					<div class="block block-4"></div>
					<div class="block block-5"></div>
					<div class="block block-6"></div>
				</div>
				<!--blocks-->
			</div>
			<div class="b2"></div>
			<div class="b3"></div>
			<div class="b4">
						<div class="bats">
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
			<!--new bat-->
			<div class="haloween-bat">
				<div class="h-left-wing">
					<div class="l-wing"></div>
				</div>
				<div class="h-bat-body">
					<div class="h-bt"></div>
					<div class="h-bt"></div>
				</div>
				<div class="h-right-wing">
					<div class="r-wing"></div>
				</div>
			</div>
			<!--new bat-->
		</div>
			 <div class="moon">
			 </div>
			</div>
		</div>');
		$css = <<<'STYLESHEET'
* {
  margin: 0;
  padding: 0;
}
html,
body {
  background: #82958f;
}
/********************
Message
********************/
.message {
  color: #fd870b;
  font-family: creepster;
  height: 200px;
  letter-spacing: 2px;
  margin-top: -100px;
  position: absolute;
  right: 1em;
  text-align: center;
  text-shadow: 1px 1px 10px #4d5c5f;
  top: 40%;
  z-index: 999999;
}
.message h1 {
  font-size: 60px;
}
/********************
background layers
********************/
.b-layers > div {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
}
.b-layers > div:nth-child(1) {
  background: #0e2128;
  box-shadow: 0 0 50px 10px #0e2128;
}
.b-layers > div:nth-child(1):after {
  right: -350px;
  top: -100px;
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 600px 110px 600px;
  border-color: transparent transparent #0e2128 transparent;
  line-height: 0;
  _border-color: #000 #000 #0e2128 #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.b-layers > div:nth-child(1):before {
  border-color: transparent #0e2128 transparent transparent;
  border-style: solid;
  border-width: 121px 72px 307px 0;
  height: 0;
  left: 2px;
  line-height: 0;
  top: -220px;
  -webkit-transform: rotate(80deg);
  -ms-transform: rotate(80deg);
  transform: rotate(80deg);
  width: 0;
  _border-color: #000 #0e2128 #000 #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.b-layers > div:nth-child(2) {
  background: #3b4b4b;
  box-shadow: 0 0 50px 10px #3b4b4b;
}
.b-layers > div:nth-child(2):before {
  top: -100px;
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 1500px 150px 800px;
  border-color: transparent transparent #3b4b4b transparent;
  line-height: 0;
  _border-color: #000 #000 #3b4b4b #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.b-layers > div:nth-child(3) {
  background: #505f62;
  box-shadow: 0 0 50px 10px #505f62;
}
.b-layers > div:nth-child(3):before {
  top: -30px;
  left: -282px;
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 800px 100px 400px;
  border-color: transparent transparent #505f62 transparent;
  line-height: 0;
  _border-color: #000 #000 #505f62 #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.b-layers > div:nth-child(3):after {
  left: 100px;
  top: -30px;
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 1500px 50px 200px;
  border-color: transparent transparent #505f62 transparent;
  line-height: 0;
  _border-color: #000 #000 #505f62 #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.b-layers > div:nth-child(4) {
  background: #6d807e;
  box-shadow: 0 0 50px 10px #6d807e;
}
.b-layers > div:nth-child(4):before {
  top: -23px;
  left: -500px;
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 600px 50px 800px;
  border-color: transparent transparent #6d807e transparent;
  line-height: 0;
  _border-color: #000 #000 #6d807e #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.b-layers > div:nth-child(4):after {
  right: -5px;
  top: -30px;
  -webkit-transform: rotate(-1deg);
  -ms-transform: rotate(-1deg);
  transform: rotate(-1deg);
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 800px 50px 500px;
  border-color: transparent transparent #6d807e transparent;
  line-height: 0;
  _border-color: #000 #000 #6d807e #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.b-layers .b1 {
  height: 130px;
  z-index: 3;
}
.b-layers .b2 {
  height: 180px;
  z-index: 2;
}
.b-layers .b3 {
  height: 230px;
  z-index: 1;
}
.b-layers .b4 {
  height: 280px;
  z-index: 0;
}
.b-layers .b4 .moon {
  background: #ffffff;
  border-radius: 100%;
  box-shadow: 0 0 5px 0 #e7e7e7, -50px 36px 79px 0 #71807b;
  content: "";
  height: 450px;
  left: 100px;
  position: absolute;
  top: -450px;
  width: 450px;
  z-index: -1;
  -webkit-animation: moon-rise 10s linear 1;
  /* Chrome, Safari, Opera */
  animation: moon-rise 10s linear 1;
  /* Standard syntax */
}
.b-layers  > div:before,
.b-layers  > div:after {
  content: "";
  position: absolute;
}
/********************
Building Blocks
********************/
.blocks {
  position: relative;
  bottom: -60px;
}
.blocks .block {
  background: #0e2128;
  position: absolute;
}
.blocks .block.block-1 {
  height: 200px;
  left: 140px;
  top: -150px;
  width: 413px;
}
.blocks .block.block-1:before {
  border-color: transparent #0e2128 transparent transparent;
  border-style: solid;
  border-width: 100px 50px 300px 0;
  height: 0;
  left: -44px;
  line-height: 0;
  top: -100px;
  -webkit-transform: rotate(17deg);
  -ms-transform: rotate(17deg);
  transform: rotate(17deg);
  width: 0;
  _border-color: #000 #0e2128 #000 #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.blocks .block.block-1:after {
  border-color: transparent transparent transparent #0e2128;
  border-style: solid;
  border-width: 100px 0 300px 50px;
  height: 0;
  line-height: 0;
  right: -53px;
  top: -70px;
  -webkit-transform: rotate(-15deg);
  -ms-transform: rotate(-15deg);
  transform: rotate(-15deg);
  width: 0;
  _border-color: #000 #000 #000 #0e2128;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.blocks .block.block-2 {
  height: 153px;
  left: 175px;
  top: -300px;
  width: 350px;
}
.blocks .block.block-3 {
  height: 150px;
  left: 215px;
  top: -440px;
  width: 270px;
}
.blocks .block.block-3 .tower {
  position: relative;
}
.blocks .block.block-3 .tower:before {
  border-color: transparent transparent #0e2128;
  border-style: solid;
  border-width: 0 100px 96px;
  height: 0;
  left: 35px;
  line-height: 0;
  padding: 16px;
  top: -125px;
  width: 0;
}
.blocks .block.block-3 .tower:after {
  border-color: transparent transparent #0e2128;
  border-style: solid;
  border-width: 0 60px 235px;
  height: 0;
  left: 90px;
  line-height: 0;
  top: -211px;
  width: 0;
}
.blocks .block.block-3:before {
  height: 138px;
  left: -16px;
  top: -44px;
  -webkit-transform: rotate(33deg);
  -ms-transform: rotate(33deg);
  transform: rotate(33deg);
  width: 169px;
}
.blocks .block.block-3:after {
  height: 139px;
  right: -16px;
  -webkit-transform: rotate(-200deg);
  -ms-transform: rotate(-200deg);
  transform: rotate(-200deg);
  width: 123px;
}
.blocks .block:nth-child(4) {
  border-color: transparent transparent #fd870b;
  border-style: solid;
  border-width: 0 25px 12px;
  height: 0;
  left: 250px;
  line-height: 0;
  padding: 0 60px;
  top: -105px;
  width: 0;
  _border-color: #000 #000 #fd870b #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
  -webkit-transform: rotate(-3deg);
  -ms-transform: rotate(-3deg);
  transform: rotate(-3deg);
}
.blocks .block:nth-child(4):before {
  border-color: transparent transparent #fd870b;
  border-style: solid;
  border-width: 0 34px 11px;
  height: 0;
  left: -14px;
  line-height: 0;
  padding: 0 60px;
  top: 26px;
  width: 0;
  _border-color: #000 #000 #fd870b #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
  -webkit-transform: rotate(1deg);
  -ms-transform: rotate(1deg);
  transform: rotate(1deg);
}
.blocks .block:nth-child(4):after {
  border-color: transparent transparent #fd870b;
  border-style: solid;
  border-width: 0 41px 12px;
  height: 0;
  left: -45px;
  line-height: 0;
  padding: 0 60px;
  top: 55px;
  width: 0;
  _border-color: #000 #000 #fd870b #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
  -webkit-transform: rotate(-2deg);
  -ms-transform: rotate(-2deg);
  transform: rotate(-2deg);
}
.blocks .block:nth-child(5) {
  border-color: transparent transparent #fd870b;
  border-style: solid;
  border-width: 0 60px 11px;
  height: 0;
  left: 226px;
  line-height: 0;
  padding: 0 60px;
  top: -19px;
  width: 0;
  _border-color: #000 #000 #fd870b #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
  -webkit-transform: rotate(-1deg);
  -ms-transform: rotate(-1deg);
  transform: rotate(-1deg);
}
.blocks .block:nth-child(5):before {
  border-color: transparent transparent #fd870b;
  border-style: solid;
  border-width: 0 55px 14px;
  height: 0;
  left: -26px;
  line-height: 0;
  padding: 0 60px;
  top: 28px;
  width: 0;
  _border-color: #000 #000 #fd870b #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
  -webkit-transform: rotate(2deg);
  -ms-transform: rotate(2deg);
  transform: rotate(2deg);
}
.blocks .block:nth-child(5):after {
  border-color: transparent transparent #fd870b;
  border-style: solid;
  border-width: 0 58px 15px;
  height: 0;
  left: -26px;
  line-height: 0;
  padding: 0 60px;
  top: 55px;
  width: 0;
  _border-color: #000 #000 #fd870b #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
  -webkit-transform: rotate(3deg);
  -ms-transform: rotate(3deg);
  transform: rotate(3deg);
}
.blocks .block.block-1:before,
.blocks .block.block-1:after {
  position: absolute;
  content: "";
}
.blocks .block.block-3:before,
.blocks .block.block-3:after {
  background: none repeat scroll 0 0 #0e2128;
  content: "";
  height: 93px;
  position: absolute;
  top: -60px;
  width: 80px;
}
.blocks .block.block-3 .tower:before,
.blocks .block.block-3 .tower:after {
  position: absolute;
  content: "";
}
.blocks .block:nth-child(4):before,
.blocks .block:nth-child(4):after {
  position: absolute;
  content: "";
}
.blocks .block:nth-child(5):before,
.blocks .block:nth-child(5):after {
  position: absolute;
  content: "";
}
/********************
------->roof
********************/
.roof {
  border-color: transparent transparent #0e2128;
  border-style: solid;
  border-width: 0 126px 42px;
  height: 0;
  left: -86px;
  line-height: 0;
  padding: 0 135px;
  position: absolute;
  top: -16px;
  width: 0;
  _border-color: #000 #000 #0e2128 #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
/********************
windows
********************/
.windows .win {
  position: absolute;
  background: #fd870b;
  box-shadow: 0 0 2px 0 #fd870b;
}
.windows .win:after,
.windows .win:before {
  position: absolute;
  content: "";
  background: #0e2128;
}
/********************
Mid Windows
********************/
.block .mid .win:nth-child(1) {
  bottom: 0;
  height: 136px;
  left: 116px;
  position: absolute;
  -webkit-transform: rotate(2deg) translateZ(2px);
  transform: rotate(2deg) translateZ(2px);
  width: 100px;
  box-shadow: 0 2px 3px 4px #0e2128, 0 15px 1px 0 #fd870b;
  -webkit-animation: w-flash 3.8s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: w-flash 3.8s linear infinite alternate;
  /* Standard syntax */
}
.block .mid .win:nth-child(1):before {
  left: 45px;
  -webkit-transform: rotate(0deg);
  -ms-transform: rotate(0deg);
  transform: rotate(0deg);
}
.block .mid .win:nth-child(1):after {
  -webkit-transform: rotate(80deg);
  -ms-transform: rotate(80deg);
  transform: rotate(80deg);
  left: 50px;
}
.block .mid .win:nth-child(2) {
  bottom: 32px;
  height: 136px;
  left: 20px;
  position: absolute;
  -webkit-transform: scale(0.5) rotate(10deg) translateZ(10px);
  transform: scale(0.5) rotate(10deg) translateZ(10px);
  width: 100px;
  box-shadow: 0 2px 3px 4px #0e2128, 0 15px 1px 0 #fd870b;
  -webkit-animation: w-flash 3.8s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: w-flash 3.8s linear infinite alternate;
  /* Standard syntax */
}
.block .mid .win:nth-child(2):before {
  left: 45px;
  -webkit-transform: rotate(5deg);
  -ms-transform: rotate(5deg);
  transform: rotate(5deg);
}
.block .mid .win:nth-child(2):after {
  -webkit-transform: rotate(70deg);
  -ms-transform: rotate(70deg);
  transform: rotate(70deg);
  left: 50px;
}
.block .mid .win:nth-child(3) {
  bottom: 24px;
  height: 136px;
  left: 216px;
  position: absolute;
  -webkit-transform: rotate(-4deg) translateZ(2px) scale(0.6);
  transform: rotate(-4deg) translateZ(2px) scale(0.6);
  width: 100px;
  box-shadow: 0 2px 3px 4px #0e2128, 0 15px 1px 0 #fd870b;
  -webkit-animation: w-flash 3.8s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: w-flash 3.8s linear infinite alternate;
  /* Standard syntax */
}
.block .mid .win:nth-child(3):before {
  left: 45px;
  -webkit-transform: rotate(-4deg);
  -ms-transform: rotate(-4deg);
  transform: rotate(-4deg);
}
.block .mid .win:nth-child(3):after {
  -webkit-transform: rotate(86deg);
  -ms-transform: rotate(86deg);
  transform: rotate(86deg);
  left: 50px;
}
.block .mid .win:nth-child(1):after,
.block .mid .win:nth-child(1):before {
  width: 6px;
  height: 140px;
  box-shadow: 0 0 3px 0 #0e2128;
}
.block .mid .win:nth-child(2):after,
.block .mid .win:nth-child(2):before {
  width: 6px;
  height: 140px;
  box-shadow: 0 0 3px 0 #0e2128;
}
.block .mid .win:nth-child(3):after,
.block .mid .win:nth-child(3):before {
  width: 6px;
  height: 140px;
  box-shadow: 0 0 3px 0 #0e2128;
}
/********************
Tower Windows
********************/
.tower .windows .win:nth-child(1) {
  height: 136px;
  position: absolute;
  right: 7px;
  -webkit-transform: rotate(2deg) translateZ(2px) scale(0.6);
  transform: rotate(2deg) translateZ(2px) scale(0.6);
  width: 100px;
  z-index: 9;
}
.tower .windows .win:nth-child(1):before {
  left: 45px;
  -webkit-transform: rotate(6deg);
  -ms-transform: rotate(6deg);
  transform: rotate(6deg);
}
.tower .windows .win:nth-child(1):after {
  -webkit-transform: rotate(75deg);
  -ms-transform: rotate(75deg);
  transform: rotate(75deg);
  left: 50px;
}
.tower .windows .win:nth-child(2) {
  bottom: -139px;
  height: 136px;
  left: 20px;
  position: absolute;
  -webkit-transform: scale(0.6) rotate(-10deg) translateZ(10px);
  transform: scale(0.6) rotate(-10deg) translateZ(10px);
  width: 100px;
}
.tower .windows .win:nth-child(2):before {
  left: 45px;
  -webkit-transform: rotate(-5deg);
  -ms-transform: rotate(-5deg);
  transform: rotate(-5deg);
}
.tower .windows .win:nth-child(2):after {
  -webkit-transform: rotate(60deg);
  -ms-transform: rotate(60deg);
  transform: rotate(60deg);
  left: 50px;
}
.tower .windows .win:nth-child(1):after,
.tower .windows .win:nth-child(1):before {
  width: 6px;
  height: 140px;
  box-shadow: 0 0 3px 0 #0e2128;
}
.tower .windows .win:nth-child(2):after,
.tower .windows .win:nth-child(2):before {
  width: 6px;
  height: 140px;
  box-shadow: 0 0 3px 0 #0e2128;
}
/********************
Bat
********************/
.bats {
  left: 24px;
  position: absolute;
  top: -430px;
  -webkit-animation: bats-fly 10s linear 1;
  /* Chrome, Safari, Opera */
  animation: bats-fly 10s linear 1;
  /* Standard syntax */
}
.haloween-bat:nth-child(1) {
  left: 548px;
  position: absolute;
  top: 100px;
  -webkit-transform: scale(0.2) rotate(-20deg);
  -ms-transform: scale(0.2) rotate(-20deg);
  transform: scale(0.2) rotate(-20deg);
  z-index: 9999;
  -webkit-animation: bats-move1 0.2s linear 0.01s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move1 0.2s linear infinite 0.01s alternate;
  /* Standard syntax */
}
.haloween-bat:nth-child(2) {
  left: 715px;
  position: absolute;
  top: 100px;
  -webkit-transform: scale(0.18) rotate(-29deg) translateZ(1px);
  transform: scale(0.18) rotate(-29deg) translateZ(1px);
  z-index: 9999;
  -webkit-animation: bats-move2 0.2s linear 0.2s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move2 0.2s linear infinite 0.2s alternate;
  /* Standard syntax */
}
.haloween-bat:nth-child(3) {
  left: 651px;
  position: absolute;
  top: 185px;
  -webkit-transform: scale(0.16) rotate(-22deg);
  -ms-transform: scale(0.16) rotate(-22deg);
  transform: scale(0.16) rotate(-22deg);
  z-index: 9999;
  -webkit-animation: bats-move3 0.2s linear 0.04s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move3 0.2s linear infinite 0.04s alternate;
  /* Standard syntax */
}
.haloween-bat:nth-child(4) {
  left: 642px;
  position: absolute;
  top: 264px;
  -webkit-transform: scale(0.14) rotate(-26deg);
  -ms-transform: scale(0.14) rotate(-26deg);
  transform: scale(0.14) rotate(-26deg);
  z-index: 9999;
  -webkit-animation: bats-move4 0.2s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move4 0.2s linear infinite alternate;
  /* Standard syntax */
}
.haloween-bat:nth-child(5) {
  left: 745px;
  position: absolute;
  top: 281px;
  -webkit-transform: scale(0.12) rotate(-20deg);
  -ms-transform: scale(0.12) rotate(-20deg);
  transform: scale(0.12) rotate(-20deg);
  z-index: 9999;
  -webkit-animation: bats-move5 0.2s linear 0.02s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move5 0.2s linear infinite 0.02s alternate;
  /* Standard syntax */
}
.haloween-bat:nth-child(6) {
  left: 648px;
  position: absolute;
  top: 330px;
  -webkit-transform: scale(0.1) rotate(-20deg);
  -ms-transform: scale(0.1) rotate(-20deg);
  transform: scale(0.1) rotate(-20deg);
  z-index: 9999;
  -webkit-animation: bats-move6 0.2s linear 0.03s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move6 0.2s linear infinite 0.03s alternate;
  /* Standard syntax */
}
.haloween-bat:nth-child(7) {
  left: 748px;
  position: absolute;
  top: 342px;
  -webkit-transform: scale(0.08) rotate(-33deg);
  -ms-transform: scale(0.08) rotate(-33deg);
  transform: scale(0.08) rotate(-33deg);
  z-index: 9999;
  -webkit-animation: bats-move7 0.2s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move7 0.2s linear infinite alternate;
  /* Standard syntax */
}
.haloween-bat:nth-child(8) {
  left: 723px;
  position: absolute;
  top: 369px;
  -webkit-transform: scale(0.06) rotate(-20deg);
  -ms-transform: scale(0.06) rotate(-20deg);
  transform: scale(0.06) rotate(-20deg);
  z-index: 9999;
  -webkit-animation: bats-move8 0.2s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move8 0.2s linear infinite alternate;
  /* Standard syntax */
}
.haloween-bat:nth-child(9) {
  left: 766px;
  position: absolute;
  top: 375px;
  -webkit-transform: scale(0.05) rotate(-35deg);
  -ms-transform: scale(0.05) rotate(-35deg);
  transform: scale(0.05) rotate(-35deg);
  z-index: 9999;
  -webkit-animation: bats-move9 0.2s linear 0.03s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move9 0.2s linear infinite 0.03s alternate;
  /* Standard syntax */
}
.haloween-bat:nth-child(10) {
  left: 800px;
  position: absolute;
  top: 377px;
  -webkit-transform: scale(0.02) rotate(-30deg);
  -ms-transform: scale(0.02) rotate(-30deg);
  transform: scale(0.02) rotate(-30deg);
  z-index: 9999;
  -webkit-animation: bats-move10 0.2s linear 0.01s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move10 0.2s linear infinite 0.01s alternate;
  /* Standard syntax */
}
.haloween-bat .h-left-wing {
  background: none repeat scroll 0 0 #000;
  height: 182px;
  position: absolute;
  width: 350px;
  z-index: 99;
}
.haloween-bat .h-left-wing .l-wing {
  position: relative;
}
.haloween-bat .h-left-wing .l-wing:before {
  background: none repeat scroll 0 0 #82958f;
  border-radius: 50% 50% 7%;
  height: 122px;
  left: 260px;
  position: absolute;
  top: -66px;
  width: 92px;
}
.haloween-bat .h-left-wing:before {
  background: none repeat scroll 0 0 #82958f;
  border-radius: 100%;
  height: 205px;
  top: 143px;
  width: 384px;
  z-index: 10;
}
.haloween-bat .h-left-wing:after {
  background: none repeat scroll 0 0 #82958f;
  border-radius: 100%;
  height: 191px;
  left: -197px;
  top: 0;
  -webkit-transform: rotate(28deg);
  -ms-transform: rotate(28deg);
  transform: rotate(28deg);
  width: 350px;
  z-index: 10;
}
.haloween-bat .h-bat-body {
  background: none repeat scroll 0 0 #000;
  height: 244px;
  left: 350px;
  margin: 9px auto 0;
  position: absolute;
  width: 52px;
  z-index: 9999;
}
.haloween-bat .h-bat-body .h-bt {
  position: relative;
}
.haloween-bat .h-bat-body .h-bt:nth-child(1) {
  z-index: 9;
}
.haloween-bat .h-bat-body .h-bt:nth-child(1):before {
  position: absolute;
  content: "";
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 40px 0 0 10px;
  border-color: transparent transparent transparent #000;
  line-height: 0;
  _border-color: #000 #000 #000 #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
  left: 0;
  top: 0;
}
.haloween-bat .h-bat-body .h-bt:nth-child(1):after {
  position: absolute;
  content: "";
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 0 40px 10px;
  border-color: transparent transparent #000 transparent;
  line-height: 0;
  _border-color: #000 #000 #000 #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
  right: 0;
  top: 0;
}
.haloween-bat .h-bat-body .h-bt:nth-child(2) {
  background: none repeat scroll 0 0 #000;
  border-radius: 50%;
  box-shadow: 0 -30px 0 27px #82958f;
  height: 18px;
  top: 31px;
  width: 52px;
}
.haloween-bat .h-bat-body .h-bt:nth-child(2):before {
  background: none repeat scroll 0 0 #82958f;
  border-radius: 100%;
  bottom: -252px;
  content: "";
  height: 131px;
  left: -71px;
  position: absolute;
  width: 100px;
  box-shadow: 41px -122px 0 3px #000;
}
.haloween-bat .h-bat-body .h-bt:nth-child(2):after {
  background: none repeat scroll 0 0 #82958f;
  border-radius: 100%;
  bottom: -252px;
  content: "";
  height: 131px;
  left: 25px;
  position: absolute;
  width: 100px;
  box-shadow: -41px -122px 0 3px #000;
}
.haloween-bat .h-right-wing {
  background: none repeat scroll 0 0 #000;
  height: 182px;
  position: absolute;
  width: 350px;
  -webkit-transform: scale(-1, 1);
  -ms-transform: scale(-1, 1);
  transform: scale(-1, 1);
  margin-left: 402px;
  z-index: 99;
}
.haloween-bat .h-right-wing .r-wing {
  position: relative;
}
.haloween-bat .h-right-wing .r-wing:before {
  background: none repeat scroll 0 0 #82958f;
  border-radius: 50% 50% 7%;
  height: 122px;
  left: 260px;
  position: absolute;
  top: -66px;
  width: 92px;
}
.haloween-bat .h-right-wing:before {
  background: none repeat scroll 0 0 #82958f;
  border-radius: 100%;
  height: 205px;
  top: 143px;
  width: 384px;
  z-index: 10;
}
.haloween-bat .h-right-wing:after {
  background: none repeat scroll 0 0 #82958f;
  border-radius: 100%;
  height: 191px;
  left: -197px;
  top: 0;
  -webkit-transform: rotate(28deg);
  -ms-transform: rotate(28deg);
  transform: rotate(28deg);
  width: 350px;
  z-index: 10;
}
.haloween-bat .h-left-wing:before,
.haloween-bat .h-left-wing:after {
  position: absolute;
  content: "";
}
.haloween-bat .h-left-wing .l-wing:before,
.haloween-bat .h-left-wing .l-wing:after {
  position: absolute;
  content: "";
}
.haloween-bat .h-right-wing:before,
.haloween-bat .h-right-wing:after {
  position: absolute;
  content: "";
}
.haloween-bat .h-right-wing .r-wing:before,
.haloween-bat .h-right-wing .r-wing:after {
  position: absolute;
  content: "";
}
/********************
pumpkin
********************/
.pumpkin {
  background: none repeat scroll 0 0 #0e2128;
  border-radius: 40%;
  box-shadow: 0 0 30px 0 #0b1b28;
  height: 125px;
  padding: 5px;
  position: relative;
  width: 160px;
  z-index: 99;
}
.pumpkin .p-eye {
  position: relative;
}
.pumpkin .p-eye:before {
  border-color: transparent transparent #fd880b;
  border-style: solid;
  border-width: 0 25px 18px 5px;
  height: 0;
  left: 30px;
  line-height: 0;
  top: 30px;
  width: 0;
  _border-color: #000 #000 #fd880b #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.pumpkin .p-eye:after {
  left: 100px;
  top: 30px;
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 0 5px 18px 25px;
  border-color: transparent transparent #fd880b transparent;
  line-height: 0;
  _border-color: #000 #000 #fd880b #000;
  _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
}
.pumpkin .p-nose {
  background: none repeat scroll 0 0 #fd870b;
  border-radius: 20% 20% 50% 50%;
  height: 20px;
  left: 80px;
  position: absolute;
  top: 57px;
  width: 10px;
}
.pumpkin .p-teeth {
  background: none repeat scroll 0 0 #fd870b;
  box-shadow: -10px 10px 0 0 #fd870b, -20px 20px 0 0 #fd870b, -30px 30px 0 0 #fd870b, -40px 40px 0 0 #fd870b, -50px 50px 0 0 #fd870b, -60px 60px 0 0 #fd870b;
  height: 15px;
  left: 113px;
  position: absolute;
  top: 93px;
  -webkit-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
  width: 15px;
}
.pumpkin:nth-child(1) {
  top: -87px;
  -webkit-transform: rotate(-30deg) scale(0.5);
  -ms-transform: rotate(-30deg) scale(0.5);
  transform: rotate(-30deg) scale(0.5);
  left: -67px;
}
.pumpkin:nth-child(2) {
  left: 298px;
  top: -366px;
  -webkit-transform: rotate(22deg) scale(0.25);
  -ms-transform: rotate(22deg) scale(0.25);
  transform: rotate(22deg) scale(0.25);
}
.pumpkin:before {
  background: none repeat scroll 0 0 #0e2128;
  content: "";
  height: 10px;
  left: 50%;
  position: absolute;
  top: -5px;
  width: 5px;
}
.pumpkin .p-eye:before,
.pumpkin .p-eye:after {
  position: absolute;
  content: "";
}
/********************
window flash
********************/
.w-flash {
  -webkit-animation: w-flash 3.8s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: w-flash 3.8s linear infinite alternate;
  /* Standard syntax */
}
@-webkit-keyframes w-flash {
  0% {
    background: red;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 #fd870b;
  }
  25% {
    background: yellow;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 #fd870b;
  }
  50% {
    background: blue;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 #fd870b;
  }
  75% {
    background: green;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 #fd870b;
  }
  100% {
    background: red;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 #fd870b;
  }
}
/* Standard syntax */
@keyframes w-flash {
  0% {
    background: red;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 red;
  }
  25% {
    background: yellow;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 yellow;
  }
  50% {
    background: blue;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 blue;
  }
  75% {
    background: green;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 green;
  }
  100% {
    background: red;
    box-shadow: 0 2px 5px 4px #0e2128, 0 15px 5px 0 red;
  }
}
/********************
moon rise
********************/
.moon-rise {
  -webkit-animation: moon-rise 10s linear 1;
  /* Chrome, Safari, Opera */
  animation: moon-rise 10s linear 1;
  /* Standard syntax */
}
@-webkit-keyframes moon-rise {
  0% {
    background: #FFE4B5;
    top: 0px;
  }
  25% {
    top: -112px;
  }
  50% {
    top: -225px;
  }
  75% {
    top: -337px;
  }
  100% {
    top: -450px;
  }
}
/* Standard syntax */
@keyframes moon-rise {
  0% {
    background: #FFE4B5;
    top: 0px;
  }
  25% {
    top: -112px;
  }
  50% {
    top: -225px;
  }
  75% {
    top: -337px;
  }
  100% {
    top: -450px;
  }
}
/********************
bats fly
********************/
.bats-fly {
  -webkit-animation: bats-fly 10s linear 1;
  /* Chrome, Safari, Opera */
  animation: bats-fly 10s linear 1;
  /* Standard syntax */
}
@-webkit-keyframes bats-fly {
  0% {
    left: 864px;
    position: absolute;
    top: -94px;
    transform: scale(0.3) rotate(-15deg);
  }
}
/* Standard syntax */
@keyframes bats-fly {
  0% {
    left: 864px;
    position: absolute;
    top: -94px;
    transform: scale(0.3) rotate(-15deg);
  }
}
/********************
bats fly
********************/
.bats-move1 {
  -webkit-animation: bats-move1 0.2s linear 0.01s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move1 0.2s linear infinite 0.01s alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move1 {
  0% {
    top: 100px;
  }
  100% {
    top: 104px;
  }
}
/* Standard syntax */
@keyframes bats-move1 {
  0% {
    top: 100px;
  }
  100% {
    top: 104px;
  }
}
/********************
bats fly
********************/
.bats-move2 {
  -webkit-animation: bats-move2 0.2s linear 0.2s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move2 0.2s linear infinite 0.2s alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move2 {
  0% {
    top: 100px;
  }
  100% {
    top: 103px;
  }
}
/* Standard syntax */
@keyframes bats-move2 {
  0% {
    top: 100px;
  }
  100% {
    top: 103px;
  }
}
/********************
bats fly
********************/
.bats-move3 {
  -webkit-animation: bats-move3 0.2s linear 0.04s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move3 0.2s linear infinite 0.04s alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move3 {
  0% {
    top: 183px;
  }
  100% {
    top: 181px;
  }
}
/* Standard syntax */
@keyframes bats-move3 {
  0% {
    top: 183px;
  }
  100% {
    top: 181px;
  }
}
/********************
bats fly
********************/
.bats-move4 {
  -webkit-animation: bats-move4 0.2s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move4 0.2s linear infinite alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move4 {
  0% {
    top: 264px;
  }
  100% {
    top: 267px;
  }
}
/* Standard syntax */
@keyframes bats-move4 {
  0% {
    top: 264px;
  }
  100% {
    top: 267px;
  }
}
/********************
bats fly
********************/
.bats-move5 {
  -webkit-animation: bats-move5 0.2s linear 0.02s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move5 0.2s linear infinite 0.02s alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move5 {
  0% {
    top: 281px;
  }
  100% {
    top: 283px;
  }
}
/* Standard syntax */
@keyframes bats-move5 {
  0% {
    top: 281px;
  }
  100% {
    top: 283px;
  }
}
/********************
bats fly
********************/
.bats-move6 {
  -webkit-animation: bats-move6 0.2s linear 0.03s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move6 0.2s linear infinite 0.03s alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move6 {
  0% {
    top: 342px;
  }
  100% {
    top: 344px;
  }
}
/* Standard syntax */
@keyframes bats-move6 {
  0% {
    top: 342px;
  }
  100% {
    top: 344px;
  }
}
/********************
bats fly
********************/
.bats-move7 {
  -webkit-animation: bats-move7 0.2s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move7 0.2s linear infinite alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move7 {
  0% {
    top: 330px;
  }
  100% {
    top: 332px;
  }
}
/* Standard syntax */
@keyframes bats-move7 {
  0% {
    top: 330px;
  }
  100% {
    top: 332px;
  }
}
/********************
bats fly
********************/
.bats-move8 {
  -webkit-animation: bats-move8 0.2s linear infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move8 0.2s linear infinite alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move8 {
  0% {
    top: 367px;
  }
  100% {
    top: 369px;
  }
}
/* Standard syntax */
@keyframes bats-move8 {
  0% {
    top: 367px;
  }
  100% {
    top: 369px;
  }
}
/********************
bats fly
********************/
.bats-move9 {
  -webkit-animation: bats-move9 0.2s linear 0.03s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move9 0.2s linear infinite 0.03s alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move9 {
  0% {
    top: 367px;
  }
  100% {
    top: 369px;
  }
}
/* Standard syntax */
@keyframes bats-move9 {
  0% {
    top: 367px;
  }
  100% {
    top: 369px;
  }
}
/********************
bats fly
********************/
.bats-move10 {
  -webkit-animation: bats-move10 0.2s linear 0.01s infinite alternate;
  /* Chrome, Safari, Opera */
  animation: bats-move10 0.2s linear infinite 0.01s alternate;
  /* Standard syntax */
}
@-webkit-keyframes bats-move10 {
  0% {
    top: 377px;
  }
  100% {
    top: 378px;
  }
}
/* Standard syntax */
@keyframes bats-move10 {
  0% {
    top: 377px;
  }
  100% {
    top: 378px;
  }
}
STYLESHEET;

		$page->addCSS($css);
		$page->addStyleSheet('https://fonts.googleapis.com/css2?family=Creepster&display=swap');
		$page->setPageName('BOO!');

		return $page;
		}

	public function independenceDay() : \PHPFUI\VanillaPage
		{
		$page = new \PHPFUI\VanillaPage();
		$page->add('<canvas id="fireworksCanvas"></canvas>');
		$page->addCSS('body {
        margin: 0;
        overflow: hidden;
        background-color: #000;
      }
      canvas {
        display: block;
      }');
		$page->addJavaScript('const canvas = document.getElementById("fireworksCanvas");
      const ctx = canvas.getContext("2d");

      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;

      // Sound effects
      const launchSounds = ["/sounds/July4th/launch.wav"];

      const explosionSounds = [
        "/sounds/July4th/explosion1.wav",
        "/sounds/July4th/explosion2.wav",
        "/sounds/July4th/explosion3.wav",
      ];

      function playRandomSound(soundArray) {
        const sound = soundArray[Math.floor(Math.random() * soundArray.length)];
        const audio = new Audio(sound);
        audio.play();
      }

      class Particle {
        constructor(x, y, color, velocity) {
          this.x = x;
          this.y = y;
          this.color = color;
          this.radius = Math.random() * 2 + 1;
          this.velocity = velocity;
          this.life = 100;
          this.alpha = 1;
          this.shimmer = Math.random() < 0.3; // 30% chance of shimmer
        }

        draw() {
          ctx.beginPath();
          ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
          let alpha = this.alpha;
          if (this.shimmer) {
            alpha *= 0.5 + Math.random() * 0.5; // Random shimmer effect
          }
          ctx.fillStyle = `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, ${alpha})`;
          ctx.fill();
        }

        update() {
          this.x += this.velocity.x;
          this.y += this.velocity.y;
          this.velocity.y += 0.05; // gravity
          this.life--;
          this.alpha -= 0.01;
        }
      }

      class Firework {
        constructor(x, y) {
          this.x = x;
          this.y = canvas.height;
          this.targetX = x;
          this.targetY = y;
          this.color = {
            r: Math.floor(Math.random() * 255),
            g: Math.floor(Math.random() * 255),
            b: Math.floor(Math.random() * 255),
          };
          const angle = (Math.random() * Math.PI) / 4 - Math.PI / 8;
          const speed = Math.random() * 3 + 5;
          this.velocity = {
            x: Math.sin(angle) * speed,
            y: -Math.cos(angle) * speed,
          };
          this.particles = [];
          this.trail = [];
          this.exploded = false;
          this.explosionProgress = 0;
          this.hasShimmer = Math.random() < 0.5; // 50% chance of shimmering trail

          playRandomSound(launchSounds);
        }

        explode() {
          for (let i = 0; i < 150; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = Math.random() * 3 + 1;
            const velocity = {
              x: Math.cos(angle) * speed,
              y: Math.sin(angle) * speed,
            };
            this.particles.push(
              new Particle(this.x, this.y, this.color, velocity)
            );
          }
          this.exploded = true;
          playRandomSound(explosionSounds);
        }

        update() {
          if (!this.exploded) {
            this.x += this.velocity.x;
            this.y += this.velocity.y;
            this.velocity.y += 0.05;
            this.trail.push(
              new Particle(this.x, this.y, this.color, { x: 0, y: 0 })
            );
            if (this.trail.length > 20) this.trail.shift();
            if (this.velocity.y >= 0 || this.y <= this.targetY) this.explode();
          } else {
            this.explosionProgress += 0.02;
            this.particles.forEach((particle) => {
              particle.update();
              particle.draw();
            });
            this.particles = this.particles.filter(
              (particle) => particle.life > 0
            );
          }

          this.trail.forEach((particle, index) => {
            particle.alpha =
              (index / this.trail.length) * (1 - this.explosionProgress);
            if (this.hasShimmer) {
              particle.shimmer = true;
            }
            particle.draw();
          });
        }

        draw() {
          if (!this.exploded) {
            ctx.beginPath();
            ctx.arc(this.x, this.y, 2, 0, Math.PI * 2);
            ctx.fillStyle = `rgb(${this.color.r}, ${this.color.g}, ${this.color.b})`;
            ctx.fill();
          }
        }
      }

      let fireworks = [];

      function animate() {
        ctx.fillStyle = "rgba(0, 0, 0, 0.1)";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        fireworks.forEach((firework) => {
          firework.update();
          firework.draw();
        });

        if (Math.random() < 0.05) {
          const x = Math.random() * canvas.width;
          const y = (Math.random() * canvas.height) / 2;
          fireworks.push(new Firework(x, y));
        }

        fireworks = fireworks.filter(
          (firework) => !firework.exploded || firework.particles.length > 0
        );

        requestAnimationFrame(animate);
      }

      animate();

      // Resize canvas when window is resized
      window.addEventListener("resize", () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
      });

      // Create firework on click
      canvas.addEventListener("click", (event) => {
        fireworks.push(new Firework(event.clientX, event.clientY));
      });');
		$page->setPageName('Happy Independence Day');

		return $page;
		}

	public function newYearsDay() : \PHPFUI\VanillaPage
		{
		$page = new \PHPFUI\VanillaPage();
		$year = \App\Tools\Date::year(\App\Tools\Date::today());

	 $page->add('<link href="https://fonts.googleapis.com/css2?family=Lobster" rel="stylesheet" type="text/css">
							<div class="container">
        <div class="countdown"></div>
        <div class="card">
            <div class="decor-container__upper">
                <div class="decor-container">
                    <div class="strings string1"></div>
                    <div class="bulb bulb1"></div>
                </div>

                <div class="decor-container">
                    <div class="strings string2"></div>
                    <div class="bulb bulb2"></div>
                </div>

                <div class="decor-container">
                    <div class="strings string3"></div>
                    <div class="bulb bulb3"></div>
                </div>

                <div class="decor-container">
                    <div class="strings string4"></div>
                    <div class="bulb bulb4"></div>
                </div>
            </div>
        </div>
        <div class="text-container">
            <h2>Happy New Year</h2>
            <h2>' . $year . '</h2>
        </div>
        <canvas id="confettiHTMLCanvas"></canvas>
    </div>');
		$css = <<<'STYLESHEET'
* {
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}

body {
    font-family: "Lobster", sans-serif;
    display: flex;
}

canvas {
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
}

.container {
    color: white;
    width: 550px;
    padding: 2%;
    background-color: #0d2146;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: auto;
    margin-top: 50px;
    border-radius: 10px;
    box-shadow: 2px 4px 8px #0d2146;
}

.heading {
    color: #fff;
}

.card {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    height: 70%;
    width: 50%;
    margin: 50px 0;
    /* background-color: #fff; */
    border-radius: 10px;
}

.decor-container__upper {
    display: flex;
    justify-content: space-around;
}

.decor-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.strings {
    height: 0;
    width: 0.3rem;
}

.bulb {
    height: 100px;
    width: 100px;
    border-radius: 50%;
    background-color: transparent;
}

.string1 {
    height: 100px;
    background-color: #adeb9c;
}

.bulb1 {
    background-color: #adeb9c;
}

.string2 {
    transition: all infinite ease-in;
    height: 180px;
    background-color: #f1a5c5;
}

.bulb2 {
    background-color: #f1a5c5;
}

.string3 {
    transition: all infinite ease-in;
    height: 60px;
    background-color: #d997dc;
}

.bulb3 {
    background-color: #d997dc;
}

.string4 {
    transition: all infinite ease-in;
    height: 150px;
    background-color: #d4db8a;
}

.bulb4 {
    background-color: #d4db8a;
}

.text-container {
    text-align: center;
    color: #fff;
}

.text-container h2 {
    font-size: 40px;
    letter-spacing: 3px;
}

.clock {
    display: flex;
    font-size: 3rem;
}

.digit-container {
    width: 50px;
    height: 60px;
    background-color: #333;
    color: #fff;
    text-align: center;
    margin: 0 5px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}

.digit {
    position: absolute;
    width: 100%;
    bottom: 0;
    /* opacity: 0; */
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
    color: white;
}
STYLESHEET;

		$page->addCSS($css);
		$page->setPageName('');
		$page->addJavaScript('let newSecond1,
    newSecond2,
    newMinute1,
    newMinute2,
    newHour1,
    newHour2,
    newDay1,
    newDay2 = 0;
let lastSecond1,
    lastSecond2,
    lastMinute1,
    lastMinute2,
    lastHour1,
    lastHour2,
    lastDay1,
    lastDay2 = 0;
const countdownDiv = document.querySelector(".countdown");
const newYearDate = new Date("31 Dec, 2023, 23:59:59");
const currentDate = new Date();
const milliDiff = newYearDate.getTime() - currentDate.getTime();
const countDown = () => {
    const newYearDate = new Date("31 Dec, 2023, 23:59:59");
    const currentDate = new Date();
    const milliDiff = newYearDate.getTime() - currentDate.getTime();
    const totalSeconds = Math.floor(milliDiff / 1000);
    const totalMinutes = Math.floor(totalSeconds / 60);
    totalHours = Math.floor(totalMinutes / 60);
    remSeconds = totalSeconds % 60;
    remMinutes = totalMinutes % 60;
    const remDays = Math.floor(totalHours / 24);
    const remHours = totalHours % 24;
    if (remMinutes < 10) {
        remMinutes = "0" + remMinutes;
    }
    if (remSeconds < 10) {
        remSeconds = "0" + remSeconds;
    }
    if (totalHours < 10) {
        totalHours = "0" + totalHours;
    }
    //   countdownDiv.innerHTML =
    `<h1 class="heading">Time Remaining: ${remDays}d :
        ${remHours}h : ${remMinutes}m : ${remSeconds}s</h1>`;
    // document.getElementById("hour1").innerHTML =
    Math.floor(remHours / 10);
    // console.log(document.getElementById("hour1"))
    newSecond2 = Math.floor(remSeconds % 10);
    newSecond1 = second1.innerText = Math.floor(remSeconds / 10);
    newMinute1 = Math.floor(remMinutes / 10);
    newMinute2 = Math.floor(remMinutes % 10);
    newHour1 = Math.floor(remHours / 10);
    newHour2 = Math.floor(remHours % 10);
    newDay1 = Math.floor(remDays / 10);
    newDay2 = Math.floor(remDays % 10);

    slideDigit("day1", newDay1, lastDay1);
    slideDigit("day2", newDay2, lastDay2);
    slideDigit("hour1", newHour1, lastHour1);
    slideDigit("hour2", newHour2, lastHour2);
    slideDigit("minute1", newMinute1, lastMinute1);
    slideDigit("minute2", newMinute2, lastMinute2);
    slideDigit("second1", newSecond1, lastSecond1);
    slideDigit("second2", newSecond2, lastSecond2);
    setTimeout(() => {
        second2.innerText = lastSecond2 = newSecond2;
        second1.innerText = lastSecond1 = newSecond1;
        minute2.innerText = lastMinute2 = newMinute2;
        minute1.innerText = lastMinute1 = newMinute1;
        hour2.innerText = lastHour2 = newHour2;
        hour1.innerText = lastHour1 = newHour1;
        day2.innerText = lastDay2 = newDay2;
        day1.innerText = lastDay1 = newDay1;
    }, 500);
};
let myInterval = setInterval(countDown, 1000);

// Replacing countdown time to current time
setTimeout(() => {
    clearInterval(myInterval);
    setInterval(newTimer, 1000);
    startFiringConfetti();
}, milliDiff);

const newTimer = () => {
    const newCurrentDate = new Date();
    let hours = newCurrentDate.getHours();
    let minutes = newCurrentDate.getMinutes();
    let seconds = newCurrentDate.getSeconds();

    if (seconds < 10) {
        seconds = "0" + seconds;
    }
    if (minutes < 10) {
        minutes = "0" + minutes;
    }
    if (hours < 10) {
        hours = "0" + hours;
    }
    //   countdownDiv.innerHTML =
    `<h1 class="heading">CountDown: ${hours} : ${minutes} : ${seconds}</h1>`;
};

function startFiringConfetti() {
    const canvas = document.getElementById("confettiHTMLCanvas");
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const ctx = canvas.getContext("2d");

    const pieces = [];
    const numberOfPieces = 200;
    const colors = ["#f00", "#0f0", "#00f", "#ff0", "#0ff"];

    function newPiece() {
        this.x = canvas.width * Math.random();
        this.y = canvas.height * Math.random() - canvas.height;
        this.rotation = Math.random() * 360;
        this.color =
            colors[Math.floor(Math.random() * colors.length)];
        this.diameter = Math.random() * 10 + 5;
        this.speed = this.diameter / 2;
        this.rise = 0;
        this.angle = 0;
    }

    for (let i = 0; i < numberOfPieces; i++) {
        pieces.push(new newPiece());
    }

    function updateNewPiece(piece) {
        piece.rotation += 0.5;
        piece.angle += 0.01;
        piece.rise += 0.5;
        piece.y -= piece.speed;
        piece.x += Math.sin(piece.angle) - 0.5 + Math.random();

        if (piece.y <= -20) {
            piece.y = canvas.height + 20;
            piece.x = Math.random() * canvas.width;
        }
    }

    function drawNewPiece(piece) {
        ctx.beginPath();
        ctx.lineWidth = piece.diameter;
        ctx.strokeStyle = piece.color;
        ctx.moveTo(piece.x + piece.diameter / 4, piece.y);
        ctx.lineTo(piece.x, piece.y + piece.diameter / 4);
        ctx.stroke();
        ctx.closePath();
    }

    function animateConfetti() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        pieces.forEach((piece) => {
            updateNewPiece(piece);
            drawNewPiece(piece);
        });

        requestAnimationFrame(animateConfetti);
    }

    animateConfetti();
}

const bulb1 = document.querySelector(".bulb1");
const bulb2 = document.querySelector(".bulb2");
const bulb3 = document.querySelector(".bulb3");
const bulb4 = document.querySelector(".bulb4");
const blinkBulb1 = () => {
    bulb1.classList.toggle("bulb1");
};
setInterval(blinkBulb1, 500);

const blinkBulb2 = () => {
    bulb2.classList.toggle("bulb2");
};
setInterval(blinkBulb2, 800);

const blinkBulb3 = () => {
    bulb3.classList.toggle("bulb3");
};
setInterval(blinkBulb3, 300);

const blinkBulb4 = () => {
    bulb4.classList.toggle("bulb4");
};
setInterval(blinkBulb4, 400);

function slideDigit(id, value, lastValue) {
    console.log("slide");
    const digit = document.getElementById(id);
    if (value !== lastValue) {
        digit.style.transform = "translateY(-100%)"; // Move digit upwards
        digit.style.opacity = "0"; // Fade out current digit

        setTimeout(() => {
            digit.style.transform = "translateY(0)";

            // Slide back to original position
            digit.textContent = value; // Set new digit value
            digit.style.opacity = "1"; // Fade in new digit
        }, 300); // Adjust this timeout as needed
    }
}');

		return $page;
		}

	public function thanksgiving() : \PHPFUI\VanillaPage
		{
		$page = new \PHPFUI\VanillaPage();
		$page->add('<link href="https://fonts.googleapis.com/css?family=Indie+Flower" rel="stylesheet" type="text/css">
<div class="JT">
  <div class="JT-leg JT-leg_left">
    <div class="JT-leg-shin"></div>
    <div class="JT-leg-foot JT-leg-foot-left"></div>
  </div>
  <div class="JT-leg JT-leg_right">
    <div class="JT-leg-shin"></div>
    <div class="JT-leg-foot JT-leg-foot-right"></div>
  </div>
  <div class="JT-bounce">
    <div class="JT-rock">
    <div class="JT-shake">
      <div class="JT-feathers">
        <div class="JT-feather JT-feather-0"></div>
        <div class="JT-feather JT-feather-1"></div>
        <div class="JT-feather JT-feather-2"></div>
        <div class="JT-feather JT-feather-3"></div>
        <div class="JT-feather JT-feather-4"></div>
        <div class="JT-feather JT-feather-9"></div>
        <div class="JT-feather JT-feather-8"></div>
        <div class="JT-feather JT-feather-7"></div>
        <div class="JT-feather JT-feather-6"></div>
        <div class="JT-feather JT-feather-5"></div>
      </div>
      <div class="JT-bod"></div>
      <div class="JT-sign">Happy Thanksgiving</div>
    </div>
    <div class="JT-head"></div>
    <div class="JT-eye JT-eye-right">
      <div class="JT-eye-iris"></div>
    </div>
    <div class="JT-eye JT-eye-left">
      <div class="JT-eye-iris"></div>
    </div>
    <div class="JT-beak"></div>
    <div class="JT-hat">
      <div class="JT-hat-brim"></div>
      <div class="JT-hat-pipe"></div>
    </div>
  </div>
  </div>
</div>');
		$css = <<<'STYLESHEET'
html {
  height: 100%;
  background: #fbb343;
  background: linear-gradient(to right, rgba(251, 180, 67, 1) 0%, rgba(247, 153, 13, 1) 100%);
}

body {
  background-color: brown;
  background: -moz-radial-gradient(center, ellipse cover, rgba(252, 252, 252, 1) 0%, rgba(254, 182, 69, 1) 100%);
  background: -webkit-radial-gradient(center, ellipse cover, rgba(252, 252, 252, 1) 0%, rgba(254, 182, 69, 1) 100%);
  background: radial-gradient(ellipse at center, rgba(252, 252, 252, 1) 0%, rgba(254, 182, 69, 1) 100%);
  background-repeat: no-repeat;
  height: 438px;
}

.JT {
  position: absolute;
  top: 302px;
  left: 50%;
  transform: translate(-50%);
}

.JT-leg {
  position: absolute;
  top: 90px;
  z-index: -100;
}

.JT-leg_left {
  left: 48%;
}

.JT-leg_right {
  left: 63%;
}

.JT-leg-shin {
  width: 10px;
  height: 40px;
  background: #f8931f;
  position: relative;
  z-index: 100;
  background: linear-gradient(to right, #f7ad59 0%, #f9a94a 100%);
}

.JT-leg-foot {
  width: 40px;
  height: 10px;
  background-color: #f8931f;
  border-top-left-radius: 100% 100%;
  border-top-right-radius: 100% 100%;
  border-bottom-right-radius: 14% 100%;
  border-bottom-left-radius: 14% 100%;
  position: absolute;
  z-index: 200;
  bottom: -5px;
}

.JT-leg-foot-left {
  left: -30px;
  transform-origin: left;
  transform: rotate(0deg);
  position: absolute;
}

.JT-leg-foot-right {
  left: 0px;
  position: absolute;
  transform-origin: left;
  transform: rotate(-20deg);
  -webkit-animation: shake .5s infinite;
  animation: shake .5s infinite;
}

.JT-feathers {
  position: absolute;
  z-index: 50;
  left: -100%;
  top: 20%;
  transform: tronsform-origin(left bottom);
  transform: rotate(-5deg);
}

.JT-feather {
  width: 180px;
  height: 60px;
  background-color: #aa6615;
  border-top-left-radius: 40% 100%;
  border-top-right-radius: 100% 70%;
  border-bottom-right-radius: 100% 100%;
  border-bottom-left-radius: 40% 100%;
  box-shadow: 0px 0px 0px #000000;
  position: absolute;
  transform-origin: right center;
}

.JT-feather-1 {
  transform: rotate(20deg);
  background-color: #a95026;
}

.JT-feather-2 {
  transform: rotate(40deg);
  background-color: #ab8427;
}

.JT-feather-3 {
  transform: rotate(60deg)
}

.JT-feather-4 {
  transform: rotate(80deg);
  background-color: #f8931f;
}

.JT-feather-5 {
  transform: rotate(100deg);
  background-color: #f8931f;
}

.JT-feather-6 {
  transform: rotate(120deg)
}

.JT-feather-7 {
  transform: rotate(140deg);
  background-color: #ab8427;
}

.JT-feather-8 {
  transform: rotate(160deg);
  background-color: #a95026;
}

.JT-feather-9 {
  transform: rotate(180deg)
}

.JT-bod {
  position: relative;
  width: 120px;
  height: 100px;
  background-color: #7b2e12;
  background: linear-gradient(to right, rgba(123, 46, 18, 1) 0%, rgba(64, 19, 3, 1) 100%);
  border-top-left-radius: 100% 100%;
  border-top-right-radius: 100% 100%;
  border-bottom-right-radius: 50% 80%;
  border-bottom-left-radius: 50% 80%;
  z-index: 900;
}

.JT-sign {
  position: absolute;
  z-index: 1300;
  width: 230px;
  height: 44px;
  line-height: 45px;
  left: -50px;
  background: #fff;
  background: linear-gradient(to right, rgba(255, 255, 255, 1) 0%, rgba(246, 246, 246, 1) 47%, rgba(207, 205, 207, 1) 100%);
  top: 45px;
  transform: rotate(5deg);
  text-align: center;
  font-family: 'Indie Flower', cursive;
  font-size: 26px;
  font-weight: bold;
}

.JT-sign::before {
  content: "";
  width: 20px;
  height: 20px;
  display: block;
  position: absolute;
  left: -10px;
  top: -5px;
  background-color: #7b2e12;
  border-top-left-radius: 50% 50%;
  border-top-right-radius: 50% 50%;
  border-bottom-right-radius: 30% 30%;
  border-bottom-left-radius: 30% 30%;
  transform: rotate(90deg);
}

.JT-sign::after {
  content: "";
  width: 20px;
  height: 20px;
  background-color: #7b2e12;
  display: block;
  position: absolute;
  right: -12px;
  top: 15px;
  background-color: #7b2e12;
  border-top-left-radius: 50% 50%;
  border-top-right-radius: 50% 50%;
  border-bottom-right-radius: 30% 30%;
  border-bottom-left-radius: 30% 30%;
  transform: rotate(-90deg);
}

.JT-head {
  position: absolute;
  z-index: 1000;
  left: 20%;
  top: -50px;
  width: 70px;
  height: 75px;
  background-color: #c4694a;
  background: linear-gradient(to right, rgba(194, 142, 124, 1) 0%, rgba(196, 104, 74, 1) 100%);
  border-radius: 100%;
}

.JT-eye {
  border-radius: 100%;
  width: 50px;
  height: 50px;
  background: #fff;
  position: absolute;
  top: -55px;
  z-index: 1050;
}

.JT-eye-left {
  left: 15px;
}

.JT-eye-right {
  left: 55px;
}

.JT-eye-iris {
  position: absolute;
  top: 36px;
  left: 20px;
  z-index: 1060;
  border-radius: 100%;
  width: 10px;
  height: 10px;
  background: #000;
}

.JT-beak {
  position: absolute;
  top: -14px;
  left: 40px;
  z-index: 1040;
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 35px 20px 0 20px;
  border-color: #f8d04f transparent transparent transparent;
  border-radius: 200px;
}

.JT-hat {
  position: absolute;
  top: -115px;
  left: 15px;
  z-index: 1100;
  transform: tronsform-origin(left bottom);
  transform: rotate(-10deg);
}

.JT-hat-pipe {
  width: 70px;
  height: 60px;
  background-color: #000;
  background: linear-gradient(to right, rgba(74, 74, 74, 1) 0%, rgba(0, 0, 0, 1) 100%);
  border-bottom: 15px solid #fff;
  z-index: 1200;
  position: relative;
}

.JT-hat-brim {
  position: absolute;
  bottom: -15px;
  left: -60%;
  z-index: 1150;
  width: 160px;
  height: 26px;
  background-color: #000;
  background: linear-gradient(to right, rgba(74, 74, 74, 1) 0%, rgba(0, 0, 0, 1) 100%);
  border-top-left-radius: 100% 100%;
  border-top-right-radius: 100% 100%;
  border-bottom-right-radius: 57% 32%;
  border-bottom-left-radius: 66% 32%;
}

.JT-shake {
  -webkit-animation: shake 3.5s infinite;
  animation: shake 3.5s infinite;
}

.JT-rock {
  -webkit-animation: rock 3.5s infinite;
  animation: rock 3.5s infinite;
}

.JT-bounce {
  -webkit-animation: bounce 1s infinite;
  animation: bounce 1s infinite;
}

@keyframes shake {
  0% {
    -webkt-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  50% {
    -webkt-transform: rotate(-10deg);
    transform: rotate(-10deg);
  }
  100% {
    -webkt-transform: rotate(0deg);
    transform: rotate(0deg);
  }
}

@keyframes rock {
  0% {
    -webkit-transform: translate(10px);
    transform: translate(10px);
  }
  50% {
    -webkit-transform: translate(0px);
    transform: translate(0px);
  }
  100% {
    -webkit-transform: translate(10px);
    transform: translate(10px);
  }
}

@keyframes bounce {
  0% {
    -webkit-transform: translate(0px, 0px);
    transform: translate(0px, 0px);
  }
  30% {
    -webkit-transform: translate(0px, 3px);
    transform: translate(0px, 3px);
  }
  75% {
    -webkit-transform: translate(0px, -3px);
    transform: translate(0px, -3px);
  }
  100% {
    -webkit-transform: translate(0px, 0px);
    transform: translate(0px, 0px);
  }
}
STYLESHEET;

		$page->addCSS($css);
		$page->setPageName('Happy Thanksgiving');

		return $page;
		}

	public function thanksgivingDay() : bool
		{
		$year = \App\Tools\Date::year(-1);
		// Create a DateTime object for the first Thursday of November
		$firstThursday = new \DateTime("first Thursday of November {$year}");

		// Calculate the difference in weeks between the first Thursday and the fourth Thursday
		$diff = new \DateInterval('P3W');

		// Add the difference to the first Thursday to get the fourth Thursday
		$thanksgiving = $firstThursday->add($diff);

		return $this->date((int)$thanksgiving->format('m'), (int)$thanksgiving->format('d'));
		}
	}
