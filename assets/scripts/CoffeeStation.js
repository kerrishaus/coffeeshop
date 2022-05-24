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
        
        $("#stationsBar").append("<div id='station" + this.number + "' class='stationCard'><div>Served: <span id='station" + this.number + "served'>0</span><hr/><div>Time: <span class='station" + this.number + "coffeeTime'>" + this.coffeeTime + "s</span><button class='stationSpeedIncrease' data-station='" + this.number + "'>Upgrade</button></div><label>" + (this.number + 1) + " <progress id='station" + this.number + "progress' value='0' max='100'></progress></label>");
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
                
                this.shop.reputation += this.line <= 5 ? 1 : -1;
                
                $("#station" + this.number + "served").html(this.customersServed);
            }
            
            this.progressBar.setProgress(this.currentCoffeeTime, this.coffeeTime);
            $("#station" + this.number + "progress").val(this.progressBar.getProgress() * 100);
            
            $("#station" + this.number + "coffeeTime").html(this.currentCoffeeTime + ", " + (this.line >= 5 ? "<span style='color: " + (this.line >= 10 ? "red" : "yellow") + ";'>" + this.line + "</span>" : this.line));
        }
        
        for (const customer of this.customers)
            customer.update(deltaTime);
    }
}

function focusStation(stationNumber)
{
    if (stationNumber > shop.stations.length)
        return;
        
    if (focusedStation != null)
        unfocusStation();
    
    focusedStation = stationNumber;
        
    shop.stations[focusedStation].object.getWorldPosition(cameraPosition);
    cameraPosition.y -= 4;
    cameraPosition.z = 4;
    
	cameraAngle.setFromAxisAngle(new THREE.Vector3( 1, 0, 0 ), Math.PI / 2.5);
	
	$("#station" + focusedStation).addClass("selected");
}

function unfocusStation()
{
    $("#station").removeClass("open");
    
    cameraPosition.copy(cameraRestingPosition);
    cameraAngle.setFromAxisAngle(new THREE.Vector3( 1, 0, 0 ), 0);
    
    $("#station" + focusedStation).removeClass("selected");
    
    focusedStation = null;
}