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
    
    getProgress()
    {
        return this.bar.scale.x;
    }
    
    percent(amount, total)
    {
       return (100 * amount) / total;
    } 
};