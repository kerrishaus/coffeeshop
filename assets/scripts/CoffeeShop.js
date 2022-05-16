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