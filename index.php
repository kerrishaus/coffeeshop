<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name='author' content='Kerris Haus'>
		
		<title>coffeeshop</title>
		
		<link rel='stylesheet' href='https://kerrishaus.com/games/coffeeshop/assets/styles/game.css'></link>

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
	    
	    <div id='station'>
	        <h1>Station #<span id='stationId'>0</span></h1>
	        <div>servedCustomers <span id='stationServedCustomers'>0</span></div>
	        <div>coffeeTime <span id='stationCoffeeTime'></span>s</div>
	        <div><progress id="stationCoffeeProgress" value="0" max="100"></progress></div>
	        <button id='stationSpeedIncrease'>Increase Speed $250</button>
	    </div>
	    
	    <script src='https://kerrishaus.com/games/coffeeshop/assets/scripts/Utility.js'></script>
	    <script src='https://kerrishaus.com/games/coffeeshop/assets/scripts/ProgressBar3D.js'></script>
	    
        <script src='https://kerrishaus.com/games/coffeeshop/assets/scripts/CoffeeShop.js'></script>
        <script src='https://kerrishaus.com/games/coffeeshop/assets/scripts/CoffeeStation.js'></script>
        <script src='https://kerrishaus.com/games/coffeeshop/assets/scripts/Customer.js'></script>
        
        <script src='https://kerrishaus.com/games/coffeeshop/assets/scripts/Game.js'></script>
	</body>
</html>