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
	        <div>rush: <span id='rushStatus'></span></div>
	        <div id='rushTimer'></div>
	        <div id='customerTimer'></div>
	        <div id='stationInfo'>
	        </div>
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
    			return new THREE.Mesh(geometry, material);
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
	                this.object = createCube();
	                this.object.material.color.setHex(0xff0000);
	                this.object.position.copy(new THREE.Vector3(-10, -2, 0));
	                this.object.scale.x = 0.8;
	                this.object.scale.y = 0.8;
	                this.object.scale.z = 0.8;
	                
	                this.station = station;
	                this.linePosition = station.line;
	                
	                scene.add(this.object);
	                
	                this.calculateTarget();
	                
	                // 0 = moving to station
	                // 1 = waiting to move to a station;
	                // 2 = waiting on a station
	                // 3 = leaving
	                this.state = 0;
	                
	                console.log("Created customer.");
	                
	                return this;
	            }
	            
	            destruct()
	            {
	                scene.remove(this.object);
	                
	                console.log("Destructed customer");
	            }
	            
	            calculateTarget()
	            {
	                this.timeElapsed = 0;
	                this.startPosition = this.object.position;
	                this.endPosition = new THREE.Vector3(-8 + (1 * this.station.number) + this.station.number, 2 - this.linePosition, 0);
	            }
	            
	            update(deltaTime)
	            {
	                if (this.timeElapsed > 0.8)
	                {
	                    this.object.position.copy(this.endPosition);
	                    
	                    if (this.state = 1)
	                    {
	                        this.state = 2;
	                        this.object.material.color.setHex(0x0000ff);
	                    }
	                    else if (this.state == 2 && this.linePosition == 0)
	                    {
	                        this.state = 3;
	                        this.object.material.color.setHex(0x00ff00);
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
	            constructor(position, number)
	            {
	                this.number = number;
	                
	                this.object = new THREE.Group();
	                
	                const model = createCube();
	                model.material.color.setHex(0xffffff);
	                this.object.add(model);
	                
	                this.progressBar = new ProgressBar3D();
	                this.object.add(this.progressBar);
	                
	                this.progressBar.position.y = 1;
	                
	                this.object.position.x = position.x;
	                this.object.position.y = position.y;
	                
	                this.customers = new Array();
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
	                if (this.customers.length > 0 && this.customers[0].state == 2)
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
    	                }
    	                
    	                this.progressBar.setProgress(this.currentCoffeeTime, this.coffeeTime);
	                }
	                
	                $("#station" + this.number + "coffeeTime").html(this.currentCoffeeTime + ", " + (this.line >= 5 ? "<span style='color: " + (this.line >= 10 ? "red" : "yellow") + ";'>" + this.line + "</span>" : this.line));
	                
	                for (const customer of this.customers)
	                    customer.update(deltaTime);
	                
	                /*
	                for (const [index, customer] of this.customers.entries())
	                {
	                    if (customer.state == 2)
	                    {
	                        if (this.currentCoffeeTime < this.coffeeTime)
	                            this.currentCoffeeTime += deltaTime;
                            else
                            {
                                customer.destruct();
                                this.customers.splice(index, 1);
                                console.log(this.customers.length);
                            }
	                    }
	                    else
                            customer.update(deltaTime);
	                }
	                */
	            }
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
	                
	                this.customerInterval = 1;
	                this.customerRushInterval = 0.5;
	                this.effectiveCustomerInterval = this.customerInterval;
	                
	                this.rushInterval = 30;
	                this.rush = false;
	                this.rushTime = 5;
	                
	                this.timeSinceLastRust = 0;
	                this.timeSinceLastCustomer = 0;
	                
	                this.addStationButton = createCube();
	                scene.add(this.addStationButton);
	                
	                console.log("Created coffeeshop.");
	            }
	            
	            newCustomer()
	            {
	                var station = getRandomInt(0, this.stations.length);
	                
	                if (this.stations[station].line >= 10)
	                {
	                    console.warn("too many customers for station" + station);
	                    return;
	                }
	                
	                var customer = new Customer(this.stations[station]);

	                this.stations[station].customers.push(customer);
	                this.stations[station].line += 1;
	                
	                console.log("added customer to shop, using station " + customer.station.number);
	            }
	            
	            addMoney(amount)
	            {
	                this.money += amount;
	            }
	            
	            addStation()
	            {
	                const station = new CoffeeStation(new THREE.Vector2(-8 + (1 * this.stations.length) + this.stations.length, 3), this.stations.length);
	                this.stations.push(station);
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
                            $("#rushStatus").html("<span style='color: red'>yes</span>");
                            this.rush = true;
                            this.effectiveCustomerInterval = this.customerRushInterval;
                        }
                    }
	                
	                if (this.timeSinceLastCustomer < this.effectiveCustomerInterval)
	                    this.timeSinceLastCustomer += deltaTime;
                    else
                    {
                        this.newCustomer();
                        this.timeSinceLastCustomer = 0;
                    }
                        
                    $("#customerTimer").html(this.timeSinceLastCustomer);
                    $("#rushTimer").html(this.timeSinceLastRush);
	                
	                this.stations.forEach(function(element, index, array)
	                {
                        element.update(deltaTime);
	                });
	            }
	        }
	    </script>
	
		<script>
			const scene = new THREE.Scene();
			const camera = new THREE.PerspectiveCamera( 75, window.innerWidth / window.innerHeight, 0.1, 1000 );

			const renderer = new THREE.WebGLRenderer();
			renderer.setSize( window.innerWidth, window.innerHeight );
			document.body.appendChild( renderer.domElement );

			const geometry2 = new THREE.PlaneGeometry( 20, 10 );
            const material2 = new THREE.MeshBasicMaterial( {color: 0x222222, side: THREE.FrontSide } );
            const floor = new THREE.Mesh( geometry2, material2 );
            scene.add(floor);
            
            const entrance = createCube();
            entrance.material.color.setHex(0x444444);
            entrance.position.x = -10;
            entrance.position.y = -2;
            scene.add(entrance);            

            const cameraRestingPosition = new THREE.Vector3(0, 0, 10);
			camera.position.copy(cameraRestingPosition);
			
			const shop = new CoffeeShop();
			shop.addStation().coffeeTime = 1;
			shop.addStation().coffeeTime = 2;
			shop.addStation().coffeeTime = 3;
			
			const clock = new THREE.Clock();
			
			function animate() 
			{
				requestAnimationFrame(animate);
				
			    shop.update(clock.getDelta());
			    
			    /*
				shop.customers.forEach(function(element, index, arary)
				{
				    const target = new THREE.Vector3(-8 + (1 * element.station) + (1 * element.station) + 0.1, 2 - shop.getStation(element.station).line, 0);
				    
				    element.object.position.lerp(target, 0.05);
				    
				    approxeq = function(v1, v2, epsilon)
				    {
                        if (epsilon == null)
                        {
                            epsilon = 0.01;
                        }
                        return Math.abs(v1 - v2) < epsilon;
                    };
                    
                    if (approxeq(element.object.position.x, target.x) && approxeq(element.object.position.y, target.y))
				    {
				        shop.getStation(element.station).line -= 1;
				        shop.customersServed += 1;
				        shop.money += 4;
				        element.destruct();
				        shop.customers.splice(index, 1);
				    }
				});
			    */
				
				document.getElementById("money").innerHTML = shop.money;
				document.getElementById("customers").innerHTML = shop.customersServed;

				renderer.render(scene, camera);
			};

			animate();
		</script>
	</body>
</html>