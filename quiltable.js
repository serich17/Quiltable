/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Quiltable implementation : © Sam Richardson samedr16@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * quiltable.js
 *
 * Quiltable user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.quiltable", ebg.core.gamegui, {
        constructor: function(){
            console.log('quiltable constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;
            this.selectableBlocks = []
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );

            // Example to add a div on the game area
            document.getElementById('game_play_area').insertAdjacentHTML('beforeend', `
                <div id="player-tables"></div>
            `);
            
            // Setting up player boards
            Object.values(gamedatas.players).forEach(player => {
                // example of setting up players boards
                this.getPlayerPanelElement(player.id).insertAdjacentHTML('beforeend', `
                    <div id="player-counter-${player.id}">A player counter</div>
                `);

                // example of adding a div for each player
                document.getElementById('player-tables').insertAdjacentHTML('beforeend', `
                    <div id="player-table-${player.id}">
                        <strong>${player.name} Quilt Area</strong>
                        <div class="quilt-board">
                            <div class="item AAAB"></div>
                            <div class="item AAIB"></div>
                            <div class="item AAZB"></div>
                            <div class="item AAPB"></div>
                        
                            <div class="item ABZB"></div>
                            <div class="item ACIB"></div>
                            <div class="item ADIB"></div>
                            <div class="item ADAB"></div>
                        
                            <div class="item"></div>
                            <div class="item"></div>
                            <div class="item"></div>
                            <div class="item"></div>
                        
                            <div class="item"></div>
                            <div class="item"></div>
                            <div class="item"></div>
                            <div class="item"></div>
                        </div>
                    </div>
                `);
            });

            console.log(gamedatas)

            // populate the pattern area with cards
            const pattern_area = document.querySelector(".quilt-board")
            const maxZIndex = 200
            Object.keys(gamedatas.decks).forEach((deck) => {
                deck = gamedatas.decks[deck]

                Object.keys(deck).forEach((card) => {
                    card = deck[card]
                    const card_cont = document.createElement("div")
                    card_cont.classList.add("card")
                    card_cont.id = card.id
                    card_cont.style.zIndex = maxZIndex-card.id
                    card_cont.classList.add(gamedatas.type_arg[card.type_arg].class)
                    card_cont.style.left = gamedatas.locations[card.location_arg].x + "px"
                    card_cont.style.top = gamedatas.locations[card.location_arg].y + "px"
                    pattern_area.appendChild(card_cont)
                })
            })

            Object.keys(gamedatas.pattern_area).forEach((card) => {
                card = gamedatas.pattern_area[card]

                const card_cont = document.createElement("div")
                card_cont.classList.add("card")
                card_cont.classList.add(gamedatas.type_arg[card.type_arg].class)
                card_cont.id = card.id
                card_cont.setAttribute("location", card.location_arg)
                card_cont.style.left = gamedatas.locations[card.location_arg].x + "px"
                card_cont.style.top = gamedatas.locations[card.location_arg].y + "px"
                pattern_area.appendChild(card_cont)
            })


            
            // TODO: Set up your game interface here, according to "gamedatas"
            
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName, args );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
            case 'plan':
                this.selectPatterns(args)
                break;
            case 'plan2':
                this.selectPatterns(args)
                break;

            case 'choose':
                if (this.player_id == args.active_player) {
                    this.selectableBlocks = args.args
                    args.args.forEach((item) => {const card = document.getElementById(item.id)
                        card.classList.add("selectable-card")
                        card.boundSelectPlan = this.chooseBlocks.bind(this);
                        card.addEventListener("click", card.boundSelectPlan)})
                }
                break;
            case 'choose2':
                if (this.player_id == args.active_player) {
                    this.selectableBlocks = args.args
                    args.args.forEach((item) => {const card = document.getElementById(item.id)
                        card.classList.add("selectable-card")
                        card.boundSelectPlan = this.chooseBlocks.bind(this);
                        card.addEventListener("click", card.boundSelectPlan)})
                }
                break;
           
            case 'dummy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
          case 'plan':
            this.removePatterns()
            break;
          case 'plan2':
            this.removePatterns()
            break;
          case 'choose':
            this.removePatterns()
            break;
          case 'choose2':
            this.removePatterns()
            break;
           
            case 'dummy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName, args );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                 case 'playerTurn':
                    this.addActionButton(('planButton'), _('Plan'), () => this.bgaPerformAction("plan"))
                    this.addActionButton(('chooseButton'), _('Choose'), () => this.bgaPerformAction("choose"))
                    this.addActionButton(('returnButton'), _('Return'), () => this.bgaPerformAction("return"))
                    break;
                 case 'playerTurn2':
                    this.addActionButton(('planButton'), _('Plan'), () => this.bgaPerformAction("plan"))
                    this.addActionButton(('chooseButton'), _('Choose'), () => this.bgaPerformAction("choose"))
                    this.addActionButton(('returnButton'), _('Return'), () => this.bgaPerformAction("return"))
                    this.statusBar.addActionButton(_('Pass'), () => this.bgaPerformAction("actPass"), { color: 'secondary' }); 
                    break;
                 case 'plan':
                    this.addActionButton(('backButton'), _('Back'), () => this.bgaPerformAction("back"))
                    break;
                 case 'choose':
                    this.addActionButton(('backButton'), _('Back'), () => this.bgaPerformAction("back"))
                    break;
                 case 'return':
                    this.addActionButton(('backButton'), _('Back'), () => this.bgaPerformAction("back"))
                    break;
                 case 'plan2':
                    this.addActionButton(('backButton'), _('Back'), () => this.bgaPerformAction("back"))
                    break;
                 case 'choose2':
                    this.addActionButton(('backButton'), _('Back'), () => this.bgaPerformAction("back"))
                    break;
                 case 'return2':
                    this.addActionButton(('backButton'), _('Back'), () => this.bgaPerformAction("back"))
                    break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
       selectPatterns: function (args) {
                if (this.player_id == args.active_player) {
                  for (let i = 0; i < 4; i++) {
                    let deck_name = "deck_" + i;
                    if (args.args[deck_name] != null) {
                        const card = document.getElementById(args.args[deck_name])
                        card.classList.add("selectable-card")
                        card.boundSelectPlan = this.selectPlan.bind(this);
                        card.addEventListener("click", card.boundSelectPlan)
                    }
                }

                }
       },
       removePatterns: function () {
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.classList.remove('selectable-card')
                card.classList.remove('selected')
                if (card.boundSelectPlan) {
                    card.removeEventListener('click', card.boundSelectPlan);
                    delete card.boundSelectPlan
                }
            });
       },

       


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        // Example:
        
        onCardClick: function( card_id )
        {
            console.log( 'onCardClick', card_id );

            this.bgaPerformAction("actPlayCard", { 
                card_id,
            }).then(() =>  {                
                // What to do after the server call if it succeeded
                // (most of the time, nothing, as the game will react to notifs / change of state instead)
            });        
        },

        selectPlan: function(element) {
            let card_id = parseInt(element.target.id)
            this.bgaPerformAction("choosePattern", { 
                card: card_id,
            }).then(() => {});  
        },

        chooseBlocks: function (element) {
            let card = element.target;
        
            // If the card is already selected and selectable, attempt to unselect
            if (card.classList.contains("selected") && card.classList.contains("selectable-card")) {
                let selectedCards = Array.from(document.querySelectorAll(".selected"))
                    .map(card => parseInt(card.getAttribute("location"), 10))
                    .filter(loc => !isNaN(loc));
        
                let cardLoc = parseInt(card.getAttribute("location"), 10);
        
                // Remove the clicked card from selected list temporarily
                let updatedSelected = selectedCards.filter(loc => loc !== cardLoc);
        
                // Check if all remaining selected cards are still connected
                if (this.isConnected(updatedSelected)) {
                    card.classList.remove("selected"); // Safe to unselect
                }
            } 
            // Otherwise, select the card if it's not already selected
            else if (!card.classList.contains("selected") && card.classList.contains("selectable-card")) {
                card.classList.add("selected");
            }
        
            this.changeSelectables(3);
        },
        isConnected: function (selectedLocations) {
            if (selectedLocations.length < 2) return true; // Single card is always connected
        
            let visited = new Set();
            let toVisit = [selectedLocations[0]]; // Start from any selected card
        
            while (toVisit.length > 0) {
                let current = toVisit.pop();
                visited.add(current);
        
                // Get adjacent cards and filter only the ones in selectedLocations
                let neighbors = this.getAdjacentLocations(current)
                    .filter(loc => selectedLocations.includes(loc) && !visited.has(loc));
        
                toVisit.push(...neighbors);
            }
        
            // If all selected locations are visited, they are connected
            return visited.size === selectedLocations.length;
        },
        
        


        changeSelectables: function (maxSelected) {
            const pattern_board = document.querySelector(".pattern-board")
            let selectedCards = pattern_board.querySelectorAll(".selected"); // Use correct class name
            // Retrieving it correctly inside changeSelectables()
            let selectedLocations = Array.from(pattern_board.querySelectorAll(".selected"))
            .map(card => card.getAttribute("location")) // Now correctly retrieves the attribute
            .filter(loc => loc !== null) // Ensure location exists
            .map(loc => parseInt(loc, 10)); // Convert to integer safely

            console.log("Selected Locations:", selectedLocations); // Check if it works now

        
            let adjacentLocations = new Set();
        
            // Find all adjacent locations from selected cards
            selectedLocations.forEach(loc => {
                let adjacents = this.getAdjacentLocations(loc);
                adjacents.forEach(adj => adjacentLocations.add(adj));
            });
        
            console.log("Adjacent Locations:", adjacentLocations); // Debugging line
        
            // Get all turned-over cards
            let allFaceUpCards = pattern_board.querySelectorAll(".card");
        
            allFaceUpCards.forEach(card => {
                let locAttr = card.getAttribute("location");
                if (locAttr === null) return; // Skip cards without a location attribute
        
                let loc = parseInt(locAttr, 10);
        
                if (selectedCards.length >= maxSelected) {
                    // If max selected, disable all except already selected ones
                    if (!card.classList.contains("selected")) {
                        card.classList.remove("selectable-card");
                    }
                } else {
                    // Otherwise, allow only adjacent cards to be selectable
                    if (adjacentLocations.has(loc) || card.classList.contains("selected")) {
                        card.classList.add("selectable-card");
                    } else {
                        card.classList.remove("selectable-card");
                    }
                }
            });

            console.log(Object.keys(selectedCards).length)
            if (Object.keys(selectedCards).length == 0) {
                this.selectableBlocks.forEach((item) => {const card = document.getElementById(item.id)
                    card.classList.add("selectable-card")})
            }
        },
        
        getAdjacentLocations: function (loc) {
            let adjacencyMap = {
                208: [209, 212], 209: [208, 210], 210: [209, 211], 211: [210, 215],
                212: [208, 216], 215: [211, 219],
                216: [212, 220], 219: [215, 223],
                220: [216, 221], 221: [220, 222], 222: [221, 223], 223: [222, 219]
            };
        
            return adjacencyMap[loc] || []; // Return adjacent locations or empty array if unmapped
        },
        
        
        
        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your quiltable.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
