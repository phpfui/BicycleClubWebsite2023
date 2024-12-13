<?php

include '../common.php';

$page = new \PHPFUI\Page();

if (! \App\Model\Session::signedInMemberId())
	{
	$page->redirect('/Join');

	exit;
	}

if (\App\Tools\Date::today() != \App\Tools\Date::make(\App\Tools\Date::year(), 12, 25))
	{
//	$page->redirect('/');

//	exit;
	}

$page->add('<div id="scene">
  <div id="blocker">
    <div id="instructions">
				<span style="font-size:36px">Click to start</span>
				<br /><br />
				Move: Use W, A, S and D keys<br/>
				Jump: SPACE<br/>
				Look: MOUSE<br/>
			  ESC to exit
			</div>
  </div>
  <div id="hint"><span>ESC to exit</span></div>
</div>');
$page->addHeadScript('https://threejs.org/build/three.module.js');
$page->addHeadScript('https://threejs.org/examples/jsm/libs/stats.module.js');
$page->addHeadScript('https://threejs.org/examples/jsm/controls/PointerLockControls.js');

$page->addCSS('body {
    margin: 0;
    overflow: hidden;
  }
canvas {
  width: 100%;
  height: 100%;
  padding: 0;
  margin: 0;
}

#blocker {
  position: absolute;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
}

#instructions {
  width: 100%;
  height: 100%;

  display: -webkit-box;
  display: -moz-box;
  display: box;

  -webkit-box-orient: horizontal;
  -moz-box-orient: horizontal;
  box-orient: horizontal;

  -webkit-box-pack: center;
  -moz-box-pack: center;
  box-pack: center;

  -webkit-box-align: center;
  -moz-box-align: center;
  box-align: center;

  color: #ffffff;
  text-align: center;
  font-family: Arial;
  font-size: 14px;
  line-height: 24px;

  cursor: pointer;
}

#hint {
  position: absolute;
  top: 5px;
  right: 10px;
  color: #ddd;
  font-size: 10px;
  font-family: Arial;
  display: none;
}');

$js = <<<'JS'
let particleSystem, particleCount, particles, controls, onKeyDown, onKeyUp, raycaster, scene, stats, camera;
let objects = [];
const lightColors = [
  '#2980b9',
  '#16a085',
  '#d35400',
  '#8e44ad',
  '#c0392b',
  '#2c3e50',
  '#b33939',
  '#218c74'
]
let moveForward = false;
let moveBackward = false;
let moveLeft = false;
let moveRight = false;
let canJump = false;
let prevTime = performance.now();
let velocity = new THREE.Vector3();
let direction = new THREE.Vector3();
let vertex = new THREE.Vector3();
let color = new THREE.Color();
let clock = new THREE.Clock();

function setupStats() {
  stats = new Stats();
  stats.showPanel( 0 ); // 0: fps, 1: ms, 2: mb, 3+: custom
  document.body.appendChild( stats.dom );
}
function setupCamera() {
  camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 1, 1000);
  camera.position.y = 10;
  camera.rotation.y = 180.65;
}
function setupScene() {
  scene = new THREE.Scene();
  scene.fog = new THREE.Fog(0x242426, 20, 250);
}
function setupRenderer() {
  renderer = new THREE.WebGLRenderer( { antialias: true } );
  renderer.setPixelRatio( window.devicePixelRatio );
  renderer.setSize(window.innerWidth, window.innerHeight);

  renderer.setClearColor( 0x242426 );
  renderer.toneMapping = THREE.LinearToneMapping;

  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  document.body.appendChild(renderer.domElement);
}
function setupControls() {
  raycaster = new THREE.Raycaster( new THREE.Vector3(), new THREE.Vector3( 0, - 1, 0 ), 0, 10 );

  let onKeyDown = function ( event ) {

   switch ( event.keyCode ) {
              case 27:
                moveForward = false;
                moveBackward = false;
                moveLeft = false;
                moveRight = false;
                break
              case 38: // up
              case 87: // w
                moveForward = true;
                break;
              case 37: // left
              case 65: // a
                moveLeft = true; break;
              case 40: // down
              case 83: // s
                moveBackward = true;
                break;
              case 39: // right
              case 68: // d
                moveRight = true;
                break;
              case 32: // space
                if ( canJump === true ) velocity.y += 350;
                canJump = false;
                break;
            }
  };
  let onKeyUp = function ( event ) {
    switch( event.keyCode ) {
      case 38: // up
      case 87: // w
        moveForward = false;
        break;
      case 37: // left
      case 65: // a
        moveLeft = false;
        break;
      case 40: // down
      case 83: // s
        moveBackward = false;
        break;
      case 39: // right
      case 68: // d
        moveRight = false;
        break;
    }
  };

  document.addEventListener( 'keydown', onKeyDown, false );
  document.addEventListener( 'keyup', onKeyUp, false );

  controls = new THREE.PointerLockControls( camera );

  let blocker = document.getElementById( 'blocker' );
  let instructions = document.getElementById( 'instructions' );
  let hint = document.getElementById( 'hint' );

  instructions.addEventListener( 'click', function () { controls.lock(); }, false );

  controls.addEventListener( 'lock', function () {
    instructions.style.display = 'none';
    blocker.style.display = 'none';
    hint.style.display = 'block';
  } );

  controls.addEventListener( 'unlock', function () {
    blocker.style.display = 'block';
    instructions.style.display = '';
    hint.style.display = 'none';
  } );

  scene.add( controls.getObject() );

}
function handleWindowResize() {
  window.addEventListener( 'resize', onWindowResize, false );
  function onWindowResize() {

    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize( window.innerWidth, window.innerHeight );

  }
}
function snow() {
   let loader = new THREE.TextureLoader();
    loader.crossOrigin = '';
   particleCount = 15000;
   let pMaterial = new THREE.PointCloudMaterial({
      color: 0xFFFFFF,
      size: 1.5,
      map: loader.load(
         "https://raw.githubusercontent.com/mrdoob/three.js/dev/examples/textures/sprites/snowflake2.png"
       ),
       blending: THREE.AdditiveBlending,
       depthTest: false,
       transparent: true
    });

    particles = new THREE.Geometry;
    for (var i = 0; i < particleCount; i++) {
        var pX = Math.random()*500 - 250,
            pY = Math.random()*500 - 250,
            pZ = Math.random()*500 - 250,
            particle = new THREE.Vector3(pX, pY, pZ);
        particle.velocity = {};
        particle.velocity.y = 0;
        particles.vertices.push(particle);
    }
    particleSystem = new THREE.PointCloud(particles, pMaterial);
    particleSystem.position.x = 100;
    particleSystem.position.y = 100;
    scene.add(particleSystem);
}
function makeItSnow() {
    var pCount = particleCount;
    while (pCount--) {
    var particle = particles.vertices[pCount];
    if (particle.y < -200) {
      particle.y = 200;
      particle.velocity.y = 0;
    }
    particle.velocity.y -= Math.random() * .02;
    particle.y += particle.velocity.y;
    }
    particles.verticesNeedUpdate = true;
}
function ground() {
  let geometry = new THREE.PlaneGeometry( 700, 600, 22, 12 );
  for (let i = 0; i < geometry.vertices.length; i++) {
    geometry.vertices[i].z = (Math.sin(i * i * i)+1/2) * 3;
  }
  geometry.verticesNeedUpdate = true;
  geometry.normalsNeedUpdate = true;
  geometry.computeFaceNormals();

  let material = new THREE.MeshPhongMaterial({
    color: 0xFFFFFF,
    shininess: 60,
    bumpScale: 0.045,
    emissive: 0xEBF7FD,
    emissiveIntensity: 0.03,
  });

  let plane = new THREE.Mesh( geometry, material );
  plane.rotation.x = Math.PI / -2;
  plane.receiveShadow = true;
  plane.position.y = -5;

  scene.add(plane)
}
function light() {
    var ambientLight = new THREE.AmbientLight(0x222222);
    scene.add(ambientLight);

    let hemiLight = new THREE.HemisphereLight( 0xEBF7FD, 0xEBF7FD, 0.2 );
    hemiLight.color.setRGB(0.75,0.8,0.95);
    hemiLight.position.set( 0, 100, 0 );
    scene.add( hemiLight );
}
function createTree() {
  // tree
  var tree = new THREE.Group();
  var trunkGeometry = new THREE.CylinderBufferGeometry(5, 10, 50);
  var trunkMaterial = new THREE.MeshPhongMaterial({ color: 0x49311c });
  var trunk = new THREE.Mesh(trunkGeometry, trunkMaterial);
  tree.add(trunk);

  // leaves
  var leavesMaterial = new THREE.MeshPhongMaterial({ color: 0x3d5e3a });


  var leavesCone= new THREE.ConeBufferGeometry(20, 40, 6);
  var leavesBottom = new THREE.Mesh(leavesCone, leavesMaterial);
  leavesBottom.position.y = 35;
  tree.add(leavesBottom);

  addRingOfLights(leavesBottom, 15, 17, -15, 0)
  addRingOfLights(leavesBottom, 16, 16, -15,  Math.PI  / 4)
  addRingOfLights(leavesBottom, 10, 11, -3,  0)
  addRingOfLights(leavesBottom, 10, 11, -3,  Math.PI  / 4)


  var middleLeaveCone = new THREE.ConeBufferGeometry(15, 30, 6);
  var leavesMiddle = new THREE.Mesh(middleLeaveCone, leavesMaterial );
  leavesMiddle.position.y = 55;
  tree.add(leavesMiddle);

  addRingOfLights(leavesMiddle, 10, 11, -8)
  addRingOfLights(leavesMiddle, 10, 11, -8, Math.PI  / 4)

  var topLeaveCone = new THREE.ConeBufferGeometry(10, 20, 6);
  var leavesTop = new THREE.Mesh(topLeaveCone, leavesMaterial);
  leavesTop.position.y = 70;
  tree.add(leavesTop);

  addRingOfLights(leavesTop, 6, 6, -3)
  addRingOfLights(leavesTop, 6, 6, -3, Math.PI  / 4)

  return tree
}
function createForest() {
  let numOfTrees = 4;

  // Right Line of Trees
  for(let i = 0; i <= numOfTrees; i++) {
    placeTree(100, 0,40 * i + 40);
  }

  // Right Wall
  for(let i = 0; i <= numOfTrees + 1; i++) {
     placeTree(40 * i + 100, 0,40 * numOfTrees + 40);
  }

  // Right Back Wall
  for(let i = 0; i <= numOfTrees + 1; i++) {
    placeTree(40 * (numOfTrees + 1) + 100, 0, (40 * numOfTrees + 40) - (40 * i + 40));
  }

  // Left Line of Trees
  for(let i = 0; i <= numOfTrees; i++) {
    placeTree(100, 0, -(40 * i + 40));
  }

  // Left Wall of Trees
  for(let i = 0; i <= numOfTrees + 1; i++) {
     placeTree( 40 * i + 100, 0, -(40 * numOfTrees + 40));
  }

   // Left Wall Back of Trees
  for(let i = 0; i <= numOfTrees; i++) {
     placeTree(40 * (numOfTrees + 1) + 100 , 0, -(40 * numOfTrees + 40) + (40 * i + 40));
  }

  // Right Entrance
  placeTree(80, 0, 40);
  placeTree(60, 0, 40);
  placeTree(40, 0, 40);
  placeTree(20, 0, 40);
  placeTree(0, 0, 40);
  placeTree(-20, 0, 40);
  // Left Entrance
  placeTree(80, 0, -40);
  placeTree(60, 0, -40);
  placeTree(40, 0, -40);
  placeTree(20, 0, -40);
  placeTree(0, 0, -40);
  placeTree(-20, 0, -40);

  // Back of Entrance
  placeTree(-40, 0, -40);
  placeTree(-40, 0, -20);
  placeTree(-40, 0, 0);
  placeTree(-40, 0, 20);
  placeTree(-40, 0, 40);


}
function placeTree(x, y, z) {
  let newTree = createTree();
  newTree.position.y = y;
  newTree.position.x = x;
  newTree.position.z = z;
  scene.add(newTree);
  objects.push( newTree );
}
function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min + 1)) + min;
}
function addRingOfLights(thing, left, right, y, rotation = 0) {
    let group = new THREE.Group();
    let light = christmasLight(left, y, 0, randomChristmasColor())
    let light2 = christmasLight(-left, y, 0, randomChristmasColor())
    let light3 = christmasLight(0, y, right, randomChristmasColor())
    let light4 = christmasLight(0, y, -right, randomChristmasColor())
    group.add( light );
    group.add( light2 );
    group.add( light3 );
    group.add( light4 );
    group.rotation.y = rotation;
    thing.add(group);
}
function randomChristmasColor() {
  const numOfLightColors = lightColors.length;
  const color = lightColors[getRandomInt(0, numOfLightColors - 1)];
  return color;
}
function christmasLight(x,y,z, color) {
  var bulbGeometry = new THREE.SphereBufferGeometry( 1, 16, 8 );
  bulbMat = new THREE.MeshStandardMaterial( {
    emissive:color || 0xffffee,
    emissiveIntensity: 3,
    color: color || 0x000000
  } );
  const bulbMesh = new THREE.Mesh( bulbGeometry, bulbMat );
  bulbMesh.position.set(x,y,z);
  return bulbMesh;
}
function createFireLight(color, power = 7){
  var light = new THREE.PointLight( color || 0xFFFFFF , 1, 0 );
  light.castShadow = true;
  light.shadow.mapSize.width = 512;
  light.shadow.mapSize.height = 512;
  light.shadow.camera.near = 0.1;
  light.shadow.camera.far = 120;
  light.shadow.bias = 0.9;
  light.shadow.radius = 5;
  light.power = power;

  return light;
}
function createFlame(x, y, z, color, outerColor, hasLight) {
  const fireLight = createFireLight(color);
  let geometry =  new THREE.ConeGeometry( .5, 4, 6, 10 );
  let material = new THREE.MeshPhongMaterial({
    color: color,
    shininess: 550,
    emissive: color,
    transparent: true,
    opacity: 0.4,
  });
   let flame = new THREE.Mesh( geometry, material );

  if(hasLight) flame.add(fireLight)

  flame.position.x = x;
  flame.position.y = 0;
  flame.position.z = z;

  let outerGeometry = new THREE.ConeGeometry( 1, 6, 6, 10 );
  let outerMaterial = new THREE.MeshPhongMaterial({
    color: outerColor,
    shininess: 550,
    emissive: outerColor,
    transparent: true,
    opacity: .4,
  });
  let outerFlame = new THREE.Mesh( outerGeometry, outerMaterial );
  flame.add(outerFlame)
  outerFlame.position.y =  1;

  return flame;
}
function createLog(x,y,z){
   let geometry = new THREE.CylinderBufferGeometry( 1, 1, 6, 8 );
   let material = new THREE.MeshPhongMaterial({
    color: 0x5C2626,
    shininess: 10,
    emissive: 0x5C2626,
  });
  var log = new THREE.Mesh( geometry, material );
  log.rotation.z = Math.PI / 2;
  log.position.x = x;
  log.position.y = y - 3;
  log.position.z = z;
  return log;
}
function campFire() {
  const log1 = createLog(172, 0, 0);
  log1.rotation.y = Math.PI / 2;
  const log2 = createLog(168, 0, 0);
  log2.rotation.y = Math.PI / 2;
  const log3 = createLog(170, 1, 2);
  const log4 = createLog(170, 1, -2);

  const flame1 = createFlame(172, 3, 0, 0xdb2902, 0xfb4402, false );
  const flame2 = createFlame(170, 3, 2, 0xdb2902, 0xfb4402 );
  const flame3 = createFlame(170, 3, -2, 0xdb2902, 0xfb4402 );
  const flame4 = createFlame(168, 3, 0, 0xdb2902, 0xfb4402, false );
  const flame5 = createFlame(170, 3, 0, 0xdb2902, 0xfb4402, true );
  flame5.scale.set(2,2,2);

  scene.add(flame1);
  scene.add(flame2);
  scene.add(flame3);
  scene.add(flame4);
  scene.add(flame5);

  scene.add(log1);
  scene.add(log2);
  scene.add(log3);
  scene.add(log4);
}
function snowman(x,y,z) {

  const snowMaterial = new THREE.MeshPhongMaterial({
    color: 0xFFFFFF,
    shininess: 60,
    bumpScale: 0.045,
    emissive: 0xEBF7FD,
    emissiveIntensity: 0.03,
  });
  const bottomBall = new THREE.Mesh( new THREE.SphereBufferGeometry( 22, 32, 32 ) , snowMaterial );
  bottomBall.position.set(x, y, z);
  bottomBall.rotation.y = - Math.PI / 2;


  const middleBall = new THREE.Mesh( new THREE.SphereBufferGeometry( 16, 32, 32 ) , snowMaterial );
  middleBall.position.set(0, 24, 0);
	bottomBall.add(middleBall);


  const head = new THREE.Mesh( new THREE.SphereBufferGeometry( 12, 24, 24 ) , snowMaterial );
  head.position.y = 20;
  middleBall.add(head);


  const armMaterial = new THREE.MeshBasicMaterial( { color: 0x111111 , side:THREE.DoubleSide} );
	const rightBicep = new THREE.Mesh( new THREE.CylinderBufferGeometry(1, 1, 22, 12, 1), armMaterial);
  rightBicep.position.x = 20;
	rightBicep.position.y = 5;
	rightBicep.rotation.z = Math.PI / 2;
	middleBall.add( rightBicep );

  const rightForearm = new THREE.Mesh( new THREE.CylinderBufferGeometry(1, 1, 15, 12, 1), armMaterial);
  rightForearm.position.x = 31;
	rightForearm.position.y = 12;
	rightForearm.rotation.z = Math.PI + .03;
  middleBall.add( rightForearm );

  const leftBicep = new THREE.Mesh( new THREE.CylinderBufferGeometry(1, 1, 22, 12, 1), armMaterial);
  leftBicep.position.x = -20;
  leftBicep.position.z = 10;
	leftBicep.position.y = 5;
	leftBicep.rotation.z = Math.PI / 2;
  leftBicep.rotation.y = Math.PI / 4;
	middleBall.add( leftBicep );

  const leftForearm = new THREE.Mesh( new THREE.CylinderBufferGeometry(1, 1, 15, 12, 1), armMaterial);
  leftForearm.position.x = -27;
  leftForearm.position.z = 22;
	leftForearm.position.y = 10;
	leftForearm.rotation.z = Math.PI + .03;
  leftForearm.rotation.x = Math.PI / 4;
  middleBall.add( leftForearm );

  const leftFinger = new THREE.Mesh( new THREE.CylinderBufferGeometry(.4, .4, 4, 12, 1), armMaterial);
  leftFinger.position.x = 0;
  leftFinger.position.z = 0;
	leftFinger.position.y = -9;
  leftForearm.add( leftFinger );

  const leftLeftFinger = new THREE.Mesh( new THREE.CylinderBufferGeometry(.4, .4, 5, 12, 1), armMaterial);
  leftLeftFinger.position.x = 2;
  leftLeftFinger.position.z = 0;
	leftLeftFinger.position.y = -8;
  leftLeftFinger.rotation.x = Math.PI / 8;
  leftLeftFinger.rotation.z = Math.PI / 8;
  leftForearm.add( leftLeftFinger );

  const leftRightFinger = new THREE.Mesh( new THREE.CylinderBufferGeometry(.4, .4, 5, 12, 1), armMaterial);
  leftRightFinger.position.x = -2;
  leftRightFinger.position.z = 0;
	leftRightFinger.position.y = -8;
  leftRightFinger.rotation.x = Math.PI / 8;
  leftRightFinger.rotation.z = -Math.PI / 8;
  leftForearm.add( leftRightFinger );


  const noseMaterial = new THREE.MeshPhongMaterial({
    color: 0xff1133,
    shininess: 60,
    bumpScale: 0.045,
    emissive: 0xff1133,
    emissiveIntensity: 0.03,
  });
	const nose = new THREE.Mesh(new THREE.CylinderBufferGeometry(0.5, 2.5, 8, 12, 4), noseMaterial);
  nose.position.z = 15;
	nose.rotation.x = 1.6;
  nose.rotation.y = -1;
	head.add(nose);

  const eyeMaterial = new THREE.MeshBasicMaterial( { color: 0x000000 } );
	const leftEye = new THREE.Mesh( new THREE.CylinderBufferGeometry(1.75, 1.75, 2, 12, 1), eyeMaterial);
  leftEye.rotation.x = 1.57;
	leftEye.position.set(5,3,11);
	head.add(leftEye)

	const rightEye = leftEye.clone();
  rightEye.rotation.x = 1.57;
	rightEye.position.set(-5,3,11);
	head.add(rightEye);

  snowmanHat = topHat(0, 12, 0);
  head.add(snowmanHat);

  objects.push(bottomBall);
  return bottomBall;
}
function topHat(x = 0, y = 0, z = 0, brimY = -5) {
  let hatMaterial = new THREE.MeshBasicMaterial( { color: 0x111111 , side:THREE.DoubleSide} );
  let hat = new THREE.Mesh( new THREE.CylinderGeometry(10, 10, 14, 12, 1), hatMaterial);
  hat.position.x = x;
	hat.position.y = y;
  hat.position.z = z;


	let brim = new THREE.Mesh( new THREE.RingGeometry( 10, 16, 24, 1 ), hatMaterial);
	hat.add( brim );
	brim.position.y = brimY;
	brim.rotation.x = 1.57;

  return hat;
}
function createSnowmen() {
  const mainSnowman = snowman(220, 10, 0);
  scene.add(mainSnowman);

  const glowingGlobe = christmasLight(193, 53, -27);
  glowingGlobe.scale.x = 3;
  glowingGlobe.scale.y = 3;
  glowingGlobe.scale.z = 3;
  scene.add(glowingGlobe)

  const rightSnowman = snowman(215, 7, 50);
  rightSnowman.scale.x = .75;
  rightSnowman.scale.y = .75;
  rightSnowman.scale.z = .75;
  scene.add(rightSnowman)

  const glowingGlobe2 = christmasLight(195, 39, 30);
  glowingGlobe2.scale.x = 2;
  glowingGlobe2.scale.y = 2;
  glowingGlobe2.scale.z = 2;
  scene.add(glowingGlobe2)

  const leftSnowman = snowman(210, 7, -45);
  leftSnowman.scale.x = .5;
  leftSnowman.scale.y = .5;
  leftSnowman.scale.z = .5;
  scene.add(leftSnowman)

  const glowingGlobe3 = christmasLight(197, 28, -59);
  glowingGlobe2.scale.x = 1.25;
  glowingGlobe2.scale.y = 1.25;
  glowingGlobe2.scale.z =  1.25;
  scene.add(glowingGlobe3)


}
function createTopHats() {
  snowmanHat1 = topHat(128, 1, -50);
  snowmanHat1.scale.set(.25, .25, .25);
  snowmanHat2 = topHat(130, 2, -70);
  snowmanHat2.scale.set(.5, .5, .5);
  snowmanHat3 = topHat(140, 4, -100);
  snowmanHat4 = topHat(170, 6, -130);
  snowmanHat4.scale.set(1.5, 1.5, 1.5);
  snowmanHat5 = topHat(240, 15, -140, -7);
  snowmanHat5.scale.set(2, 2, 2);


  snowmanHat6 = topHat(128, 1, 50);
  snowmanHat6.scale.set(.25, .25, .25);
  snowmanHat7 = topHat(130, 2, 70);
  snowmanHat7.scale.set(.5, .5, .5);
  snowmanHat8 = topHat(140, 4, 100);
  snowmanHat9 = topHat(170, 6, 130);
  snowmanHat9.scale.set(1.5, 1.5, 1.5);
  snowmanHat10 = topHat(240, 15, 140, -7);
  snowmanHat10.scale.set(2, 2, 2);


  scene.add(snowmanHat1);
  scene.add(snowmanHat2);
  scene.add(snowmanHat3);
  scene.add(snowmanHat4);
  scene.add(snowmanHat5);
  scene.add(snowmanHat6);
  scene.add(snowmanHat7);
  scene.add(snowmanHat8);
  scene.add(snowmanHat9);
  scene.add(snowmanHat10);
  objects.push(snowmanHat1, snowmanHat2 ,snowmanHat3, snowmanHat4, snowmanHat5, snowmanHat6, snowmanHat7, snowmanHat8, snowmanHat9, snowmanHat10)
}
function animate() {
  requestAnimationFrame( animate );
  stats.begin();
  particleSystem.rotation.y += 0.01;
  makeItSnow();

  if ( controls.isLocked === true ) {
    raycaster.ray.origin.copy( controls.getObject().position );
	  raycaster.ray.origin.y -= 10;

  	var intersections = raycaster.intersectObjects( objects );
    var onObject = intersections.length > 0;
    var time = performance.now();
    var delta = ( time - prevTime ) / 1000;
    velocity.x -= velocity.x * 10.0 * delta;
    velocity.z -= velocity.z * 10.0 * delta;
    velocity.y -= 9.8 * 100.0 * delta; // 100.0 = mass
    direction.z = Number( moveForward ) - Number( moveBackward );
    direction.x = Number( moveRight ) - Number( moveLeft );
    direction.normalize(); // this ensures consistent movements in all directions

    if ( moveForward || moveBackward ) velocity.z -= direction.z * 400.0 * delta;
    if ( moveLeft || moveRight ) velocity.x -= direction.x * 400.0 * delta;

    if ( onObject === true ) {
      velocity.y = Math.max( 0, velocity.y );
      canJump = true;
    }

    controls.moveRight( - velocity.x * delta );
    controls.moveForward( - velocity.z * delta );
    controls.getObject().position.y += ( velocity.y * delta ); // new behavior

    if ( controls.getObject().position.y < 10 ) {
      velocity.y = 0;
      controls.getObject().position.y = 10;
      canJump = true;
    }

    prevTime = time;

  	stats.end();
  } else {
    // Prevent Player from continuing if esc and forward at the same time
    velocity.x = 0;
    velocity.z = 0;
    controls.moveRight(0);
    controls.moveForward( 0 );
    moveForward = false;
    moveBackward = false;
    moveLeft = false;
    moveRight = false;
  }
  render();
}
function render() {
  renderer.render( scene, camera );
}
const init = () => {
  setupStats();
  setupCamera();
  setupScene();
  setupRenderer();
  setupControls();
  handleWindowResize();
  ground();
  createForest();
  snow();
  light();
  campFire();
  createSnowmen();
  createTopHats();
}
init();
animate();
JS;
$page->addJavaScript($js);

$page->setPageName('Happy Holidays!');
echo $page;
