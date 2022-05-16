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