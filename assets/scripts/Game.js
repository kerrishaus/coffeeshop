const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 3000);

let raycaster = new THREE.Raycaster();

let INTERSECTED;

let focusedStation = null;

let cameraPosition = new THREE.Vector3(0, 0, 10);
let cameraAngle = new THREE.Quaternion();
cameraAngle.setFromAxisAngle(new THREE.Vector3( 1, 0, 0 ), 0);

const pointer = new THREE.Vector2();
const radius = 100;

const renderer = new THREE.WebGLRenderer();
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

const geometry2 = new THREE.PlaneGeometry(20, 10);
const material2 = new THREE.MeshBasicMaterial({color: 0x222222, side: THREE.FrontSide });
const floor = new THREE.Mesh(geometry2, material2);
scene.add(floor);

const entrance = createCube();
entrance.material.color.setHex(0x444444);
entrance.position.x = -10;
entrance.position.y = -2;
scene.add(entrance);            

const cameraRestingPosition = new THREE.Vector3(0, 0, 10);
const cameraRestingAngle = new THREE.Quaternion();
camera.position.copy(cameraRestingPosition);
cameraRestingAngle.setFromAxisAngle(new THREE.Vector3( 1, 0, 0 ), 0);

const shop = new CoffeeShop();
shop.addStation();
shop.addStation();
shop.addStation();
shop.addStation();

const clock = new THREE.Clock();

function onWindowResize()
{
	camera.aspect = window.innerWidth / window.innerHeight;
	camera.updateProjectionMatrix();
	
	/*
	// move camera further up the smaller the window is
	console.log(10 + Math.abs((window.innerWidth / 60)));
    camera.position.z = (10 + (window.innerWidth / 60));
    */

	renderer.setSize(window.innerWidth, window.innerHeight);
}

function onPointerMove(event)
{
	pointer.x = ( event.clientX / window.innerWidth ) * 2 - 1;
	pointer.y = - ( event.clientY / window.innerHeight ) * 2 + 1;
}

function onMouseUp(event)
{
    if (INTERSECTED == null)
        return;
        
    if (INTERSECTED.userData.hasOwnProperty("addStationButton"))
    {
        if (shop.money >= 1000 * shop.stations.length)
        {
            shop.money -= 1000 * shop.stations.length;
            shop.addStation();
        }
    }
    else if (INTERSECTED.userData.hasOwnProperty("station"))
    {
        focusStation(INTERSECTED.userData.station);
    }
}

function onKeyDown(event)
{
    if (focusedStation != null)
    {
	    if (event.code == "Escape" || event.code == "ArrowDown")
	    {
            unfocusStation();
	    }
	    else if (event.code == "ArrowLeft")
	    {
	        if (cameraPosition.x > -7)
	            focusStation(focusedStation - 1);
	    }
	    else if (event.code == "ArrowRight")
	    {
	        if (cameraPosition.x < 8)
	            focusStation(focusedStation + 1);
	    }
    }
    
    if (event.keyCode >= 49 && event.keyCode <= 57)
    {
        const number = 9 - (57 - event.keyCode);
        focusStation(number - 1);
    }
}

$("#stationSpeedIncrease").click(function(event)
{
    if (focusedStation !=null)
    {
        if (shop.money >= 250)
        {
            shop.money -= 250;
            shop.stations[focusedStation].coffeeTime -= 0.1;
        }
    }
});

document.addEventListener('keydown', onKeyDown);

document.addEventListener('mouseup', onMouseUp);
document.addEventListener('mousemove', onPointerMove);

window.addEventListener('resize', onWindowResize);

function animate()
{
	requestAnimationFrame(animate);
	
    shop.update(clock.getDelta());
    
    camera.position.lerp(cameraPosition, 0.3);
    camera.quaternion.slerp(cameraAngle, 0.3);
    
    // intersections
	raycaster.setFromCamera(pointer, camera);

	const intersects = raycaster.intersectObjects(scene.children, true);

	if (intersects.length > 0)
	{
		if (INTERSECTED != intersects[0].object)
		{
			if (INTERSECTED)
			{
			    INTERSECTED.material.color.setHex(INTERSECTED.currentHex);
			    INTERSECTED = null;
			}

            if (intersects[0].object.userData.hasOwnProperty("canClick"))
            {
				INTERSECTED = intersects[0].object;
				INTERSECTED.currentHex = INTERSECTED.material.color.getHex();
				INTERSECTED.material.color.setHex(0xff0000);
            }
		}
	}
	else
	{
		if (INTERSECTED)
		    INTERSECTED.material.color.setHex(INTERSECTED.currentHex);

		INTERSECTED = null;
	}
	
	if (focusedStation != null)
	{
	    $("#stationCoffeeProgress").val(shop.stations[focusedStation].progressBar.getProgress() * 100);
	}
	
	renderer.render(scene, camera);
};

animate();