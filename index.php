<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>coffeeshop</title>
		<style>
			body { margin: 0; }
		</style>
        <script src="https://kerrishaus.com/assets/threejs/build/three.js"></script>
        <script src="https://kerrishaus.com/assets/scripts/jquery-3.6.0.min.js"></script>
        
        <script src="https://portal.kerrishaus.com/assets/javascript/messages.js"></script>
        <link rel='stylesheet' href='https://portal.kerrishaus.com/assets/styles/messages.css'></link>
        
        <style>
            #notificationContainer
            {
                box-sizing: border-box;
                
                padding: 1em;
                
                top: 0px;
                left: 0px;
                
                width: 100vw;
                height: 100vh;
            }
        </style>
	</head>
	<body>
	    <style>
	        #info
	        {
	            position: absolute;
	            top: 0px;
	            left: 0px;
	            
	            pointer-events: none;
	            
	            color: white;
	        }
	    </style>
	    
	    <div id='info'>
	        <div>money: $<span id='money'>0</span></div>
	        <div>served: <span id='customers'>0</span></div>
	        <div>reputation: <span id='reputation'>0</span></div>
	        <div>rush: <span id='rushStatus'></span></div>
	        <div id='rushTimer'></div>
	        <div id='customerTimer'></div>
	        <div id='stationInfo'>
	        </div>
	    </div>
	    
	    <style>
	        #station
	        {
	            box-sizing: border-box;
	            
	            position: absolute;
	            top: -2em;
	            left: 0px;
	            
	            background: #222222;
	            color: white;
	            
	            padding: 1em;
	            
	            width: 100vw;
	            height: 0px;
	            
	            overflow-x: hidden;
	            
	            transition: height 0.25s, top 0.25s;
	        }
	        
	        #station.open
	        {
	            top: 0px;
	            height: 60vh;
	        }
	        
	        h1#stationId
	        {
	            margin: 0px;
	        }
	    </style>
	    
	    <div id='station'>
	        <h1>Station #<span id='stationId'>0</span></h1>
	        <div>servedCustomers <span id='stationServedCustomers'>0</span></div>
	    </div>
	    
	    <script>
	        function getRandomInt(min, max) 
	        {
                min = Math.ceil(min);
                max = Math.floor(max);
                return Math.floor(Math.random() * (max - min) + min); //The maximum is exclusive and the minimum is inclusive
            }
	    
	        function createCube()
	        {
    	        const geometry = new THREE.BoxGeometry();
    			const material = new THREE.MeshBasicMaterial({ color: 0x000000 });
    			const cube = new THREE.Mesh(geometry, material);
    			return cube;
	        }
	    </script>
	    
	    <script>
	        class ProgressBar3D extends THREE.Group
	        {
                constructor()
                {
                    super();
                    
        			const geometry = new THREE.PlaneGeometry(1, .25);
                    const material = new THREE.MeshBasicMaterial({color: 0xff0000, side: THREE.FrontSide });
                    this.track = new THREE.Mesh(geometry, material);
                    
                    const geometry2 = new THREE.PlaneGeometry(1, .25);
                    const material2 = new THREE.MeshBasicMaterial({color: 0x00ff00, side: THREE.FrontSide });
                    this.bar = new THREE.Mesh(geometry2, material2);
                    
                    this.add(this.track);
                    this.add(this.bar);
                    scene.add(this);
                }
                
                setProgress(amount, total)
                {
                    this.bar.scale.set(this.percent(amount, total) / 100, 1, 1);
                }
                
                percent(amount, total)
                {
                   return (100 * amount) / total;
                } 
	        };
	    </script>
	    
	    <script>
	        class Customer
	        {
	            constructor(station)
	            {
	                this.station = station;
	                this.linePosition = station.line;
	                
	                this.object = new THREE.Group();
	                this.object.position.copy(new THREE.Vector3(-10, -2, 0));
	                
	                this.torso = createCube();
	                this.torso.scale.x = 0.8;
	                this.torso.scale.y = 0.8;
	                this.torso.scale.z = 0.8;
	                this.object.add(this.torso);
	                
	                this.setHex(0xff0000);
	                
	                scene.add(this.object);
	                
	                this.calculateTarget();
	                
	                // 0 = moving to station
	                // 1 = waiting to move to a station;
	                // 2 = waiting in line at a station
	                // 3 = waiting for coffee to be finished at a station
	                // 4 = leaving
	                this.state = 0;
	                
	                return this;
	            }
	            
	            destruct()
	            {
	                scene.remove(this.object);
	            }
	            
	            setHex(color)
	            {
	                this.torso.material.color.setHex(color);
	            }
	            
	            calculateTarget()
	            {
	                this.timeElapsed = 0;
	                this.startPosition = this.object.position;
	                this.endPosition = new THREE.Vector3(-8 + (1 * this.station.number) + this.station.number, 2 - this.linePosition, 0);
	            }
	            
	            update(deltaTime)
	            {
	                if (this.timeElapsed > 0.7)
	                {
	                    this.object.position.copy(this.endPosition);
	                    
	                    if (this.state == 0)
	                    {
	                        this.state = 2;
	                        this.setHex(0x0000ff);
	                    }
	                    else if (this.state == 2 && this.linePosition == 0)
	                    {
	                        this.state = 3;
	                        this.setHex(0x00ff00);
	                    }
	                }
	                else
	                {
                        this.object.position.lerpVectors(this.startPosition, this.endPosition, this.timeElapsed / 3);
                        this.timeElapsed += deltaTime;
	                }
	            }
	        }
	    </script>
	    
	    <script>
	        class CoffeeStation
	        {
	            constructor(shop)
	            {
	                this.shop = shop;
	                this.number = shop.stations.length;
	                
	                this.object = new THREE.Group();
	                
	                const model = createCube();
	                model.userData.canClick = true;
	                model.userData.station = this.number;
	                model.material.color.setHex(0xffffff);
	                this.object.add(model);
	                
	                this.progressBar = new ProgressBar3D();
	                this.object.add(this.progressBar);
	                
	                this.progressBar.position.y = 1;
	                
	                this.object.position.x = -8 + (1 * shop.stations.length) + shop.stations.length;
	                this.object.position.y = 3;
	                
	                this.customers = new Array();
	                this.customersServed = 0;
	                scene.add(this.object);
	                
	                this.coffeeTime = 4;
	                this.currentCoffeeTime = 0;
	                
	                this.line = 0;
	                
	                $("#stationInfo").append("<div id='station" + this.number + "'>" + this.number + ": <span id='station" + this.number + "coffeeTime'></span></div>");
	            }
	            
	            destruct()
	            {
	                scene.remove(this.object);
	                
	                this.customers.forEach(function(element, index, array)
	                {
                        element.destruct();
	                });
	            }

	            update(deltaTime)
	            {
	                if (this.customers.length > 0 && this.customers[0].state == 3)
	                {
    	                if (this.currentCoffeeTime < this.coffeeTime)
    	                    this.currentCoffeeTime += deltaTime;
    	                else
    	                {
    	                    this.customers.shift().destruct();
    	                    this.line -= 1;

    	                    for (const customer of this.customers)
    	                    {
    	                        customer.linePosition -= 1;
    	                        customer.calculateTarget();
                            }

                            this.currentCoffeeTime = 0;
                            
                            this.customersServed += 1;
                            this.shop.money += 4;
                            this.shop.customersServed += 1;
                            
                            if (this.line <= 5)
                                this.shop.reputation += 1;
    	                }
    	                
    	                this.progressBar.setProgress(this.currentCoffeeTime, this.coffeeTime);
	                }
	                
	                $("#station" + this.number + "coffeeTime").html(this.currentCoffeeTime + ", " + (this.line >= 5 ? "<span style='color: " + (this.line >= 10 ? "red" : "yellow") + ";'>" + this.line + "</span>" : this.line));
	                
	                for (const customer of this.customers)
	                    customer.update(deltaTime);
	            }
	        }
	        
	        function focusStation(stationNumber)
	        {
	            if (stationNumber > shop.stations.length)
	                return;
	            
		        focusedStation = stationNumber;
			        
		        shop.stations[focusedStation].object.getWorldPosition(cameraPosition);
		        cameraPosition.y -= 4;
		        cameraPosition.z = 4;
		        
    			cameraAngle.setFromAxisAngle(new THREE.Vector3( 1, 0, 0 ), Math.PI / 2.5);
		        
		        $("#stationId").html(focusedStation + 1);
		        $("#stationServedCustomers").html(shop.stations[focusedStation].customersServed);
		        $("#station").addClass("open");
	        }
	        
	        function unfocusStation()
	        {
		        $("#station").removeClass("open");
		        
		        cameraPosition.copy(cameraRestingPosition);
		        cameraAngle.setFromAxisAngle(new THREE.Vector3( 1, 0, 0 ), 0);
		        
		        focusedStation = null;
	        }
	    </script>
	    
	    <script>
	        class CoffeeShop
	        {
	            constructor()
	            {
	                this.money = 0;
	                this.customersServed = 0;
	                this.stations = new Array();
	                
	                this.reputation = 0;
	                
	                this.customerInterval = 2.3;
	                this.customerRushIntervalFactor = 2; // customerInterval is divided by this value
	                this.effectiveCustomerInterval = this.customerInterval;
	                
	                this.rushInterval = 30;
	                this.rush = false;
	                this.rushTime = 5;
	                
	                this.timeSinceLastRust = 0;
	                this.timeSinceLastCustomer = 0;
	                
	                this.addStationButton = createCube();
	                this.addStationButton.position.y = 3;
	                this.addStationButton.position.x = -8;
	                this.addStationButton.userData.canClick = true;
	                this.addStationButton.userData.addStationButton = true;
	                scene.add(this.addStationButton);
	            }
	            
	            newCustomer()
	            {
	                let smallestLine = 10, station = 0;
	                
	                for (let i = 0; i < this.stations.length; i++)
	                {
	                    if (this.stations[i].line < smallestLine)
	                    {
	                        smallestLine = this.stations[i].line;
	                        station = i;
	                    }
	                }
                    
	                if (!this.rush && this.stations[station].line >= 10)
	                {
	                    this.reputation -= 1;
	                    console.warn("too many customers for station" + station);
	                    return;
	                }
	                
	                var customer = new Customer(this.stations[station]);

	                this.stations[station].customers.push(customer);
	                this.stations[station].line += 1;
	            }
	            
	            addMoney(amount)
	            {
	                this.money += amount;
	            }
	            
	            addStation()
	            {
	                if (this.stations.length >= 9)
	                {
	                    console.warn("cannot create another station");
	                    return;
	                }
	                
	                if (this.stations.length == 8)
	                    scene.remove(this.addStationButton);
	                
                    this.customerInterval -= 0.052 * this.stations.length;
                    this.effectiveCustomerInterval = this.customerInterval;
	                
	                //const station = new CoffeeStation(new THREE.Vector2(-8 + (1 * this.stations.length) + this.stations.length, 3), this.stations.length);
	                const station = new CoffeeStation(this);
	                this.stations.push(station);
	                
	                this.addStationButton.position.x += 2;
	                
	                return station;
	            }
	            
	            getStation(number)
	            {
	                return this.stations[number];
	            }
	            
	            update(deltaTime)
	            {
	                if (this.timeSinceLastRush < this.rushInterval)
	                    this.timeSinceLastRush += deltaTime;
                    else
                    {
                        if (this.rush)
                        {
                            if (this.timeSinceLastRush < this.rushInterval + this.rushTime)
                                this.timeSinceLastRush += deltaTime;
                            else
                            {
                                this.timeSinceLastRush = 0;
                                this.effectiveCustomerInterval = this.customerInterval;
                                $("#rushStatus").html("no");
                                this.rush = false;
                            }
                        }
                        else
                        {
                            if (this.stations.length > 2)
                            {
                                $("#rushStatus").html("<span style='color: red'>yes</span>");
                                this.rush = true;
                                this.effectiveCustomerInterval = this.customerInterval / this.customerRushIntervalFactor;
                            }
                        }
                    }
	                
	                if (this.timeSinceLastCustomer < this.effectiveCustomerInterval)
	                    this.timeSinceLastCustomer += deltaTime;
                    else
                    {
                        this.newCustomer();
                        this.timeSinceLastCustomer = 0;
                    }
                        
                    $("#customerTimer").html(this.effectiveCustomerInterval + ": " + this.timeSinceLastCustomer);
                    $("#rushTimer").html(this.timeSinceLastRush);
                    
                    $("#money").html(this.money);
                    $("#customers").html(this.customersServed);
                    
                    $("#reputation").html(this.reputation);
                    
	                this.stations.forEach(function(element, index, array)
	                {
                        element.update(deltaTime);
	                });
	            }
	        }
	    </script>
	
		<script>
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
			        shop.addStation();
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
				
				renderer.render(scene, camera);
			};

			animate();
		</script>
	</body>
</html>
