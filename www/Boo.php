<?php

include '../common.php';

$page = new \PHPFUI\VanillaPage();

if (\App\Tools\Date::today() != \App\Tools\Date::make(\App\Tools\Date::year(), 10, 31))
	{
	$page->redirect('/');

	exit;
	}

$settingTable = new \App\Table\Setting();
$club = $settingTable->value('clubName');
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
echo $page;
