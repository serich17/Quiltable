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

            this.playerId
            this.selectedCards = []; // Stores selected card objects with row/col
            this.tempCards = []; // Temporary card elements for preview
            this.board

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
            this.playerId = this.player_id
            // Example to add a div on the game area
            document.getElementById('game_play_area').insertAdjacentHTML('beforeend', `
                <div id="player-tables"></div>
            `);

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

            // back cards placed in position
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
                            <div class="item"></div>
                            <div class="item"></div>
                            <div class="item"></div>
                            <div class="item"></div>
                        
                            <div class="item"></div>
                            <div class="item"></div>
                            <div class="item"></div>
                            <div class="item"></div>
                        
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

                const patterns = document.createElement("section")
                patterns.classList.add("patterns")

                document.getElementById("player-table-"+player.id).appendChild(patterns)
                this.board = dojo.query(`#player-table-${this.player_id} .quilt-board`)[0];

                const player_board = document.getElementById("player-table-"+player.id).querySelector(".quilt-board")
                if (Object.keys(gamedatas.boards).includes(player.id)) {
                    Object.values(gamedatas.boards[player.id]).forEach(card => {
                        let card_cont = document.createElement("div")
                        card_cont.classList.add("card")
                        card_cont.classList.add(gamedatas.type_arg[card.type_arg].class)
                        card_cont.id = card.id
                        card_cont.setAttribute("location", card.location_arg)
                        if (card.location_arg != "0") {
                            card_cont.style.left = gamedatas.locations[card.location_arg].x + "px"
                            card_cont.style.top = gamedatas.locations[card.location_arg].y + "px"
                            player_board.appendChild(card_cont) 
                        } else {
                            patterns.appendChild(card_cont)
                        }
                    })
                }
            });

            console.log(gamedatas)


            
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
            dojo.query('.temp-card', this.board).forEach(dojo.destroy);
            break;
          case 'choose2':
            this.removePatterns()
            dojo.query('.temp-card', this.board).forEach(dojo.destroy);
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
                    this.statusBar.addActionButton(_('Plan'), () => this.bgaPerformAction("plan"))
                    this.statusBar.addActionButton(_('Choose'), () => this.bgaPerformAction("choose"))
                    this.statusBar.addActionButton(_('Return'), () => this.bgaPerformAction("return"))
                    break;
                 case 'playerTurn2':
                    this.statusBar.addActionButton(_('Plan'), () => this.bgaPerformAction("plan"))
                    this.statusBar.addActionButton(_('Choose'), () => this.bgaPerformAction("choose"))
                    this.statusBar.addActionButton(_('Return'), () => this.bgaPerformAction("return"))
                    this.statusBar.addActionButton(_('Pass'), () => this.bgaPerformAction("actPass"), { color: 'secondary' }); 
                    break;
                 case 'plan':
                    this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("back"), { color: 'secondary' });
                    break;
                 case 'choose':
                    this.statusBar.addActionButton(_('Confirm Selection'), () => this.confirmSelection(), {id: 'confirm_selection'})
                    this.statusBar.addActionButton(_('Finalize Placement'), () => this.finalizeCardPlacement(), {id: 'confirm_placement', style: 'display:none'});
                    this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("back"), { color: 'secondary' })
                    break;
                 case 'return':
                    this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("back"), { color: 'secondary' });
                    break;
                 case 'plan2':
                    this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("back"), { color: 'secondary' });
                    break;
                 case 'choose2':
                    this.statusBar.addActionButton(_('Confirm Selection'), () => this.confirmSelection(), {id: 'confirm_selection'})
                    this.statusBar.addActionButton(_('Finalize Placement'), () => this.finalizeCardPlacement(), {id: 'confirm_placement', style: 'display:none'});
                    this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("back"), { color: 'secondary' })
                    break;
                 case 'return2':
                    this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("back"), { color: 'secondary' });
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
            this.selectedCards = []
            cards.forEach(card => {
                card.classList.remove('selectable-card')
                card.classList.remove('selected')
                if (card.boundSelectPlan) {
                    card.removeEventListener('click', card.boundSelectPlan);
                    delete card.boundSelectPlan
                }
            });
       },



// Updated confirmSelection function
confirmSelection: function() {
    let selectedLocations = dojo.query(".selected");
    console.log("Selected cards:", selectedLocations);
    
    // Validate selection (2-3 cards)
    if (selectedLocations.length < 2 || selectedLocations.length > 3) {
        this.showMessage(_("Please select 2-3 adjacent cards"), "error");
        return;
    }
    
    // Get card IDs and locations
    const selectedCards = [];
    selectedLocations.forEach(card => {
        const locationId = card.getAttribute("location");
        if (!locationId) {
            console.error("Card is missing location attribute:", card);
            return;
        }
        
        selectedCards.push({
            id: card.id,
            location: this.getCardLocation(parseInt(locationId), 'pattern-area')
        });
    });
    
    if (selectedCards.length < 2) {
        this.showMessage(_("Could not determine locations for selected cards"), "error");
        return;
    }
    
    console.log("Selected cards with locations:", selectedCards);
    
    // Get board reference - do this here to ensure we have the latest
    this.board = dojo.query(`#player-table-${this.player_id} .quilt-board`)[0];
    if (!this.board) {
        console.error("Could not find quilt board");
        return;
    }
    
    // Clear previous temporary cards
    dojo.query('.temp-card', this.board).forEach(dojo.destroy);
    dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
    
    // Validate adjacency
    if (!this.areCardsAdjacent(selectedCards.map(card => card.location))) {
        this.showMessage(_("Selected cards must be adjacent"), "error");
        return;
    }
    
    // Create temporary transparent cards on player's board
    this.tempCards = selectedCards.map((card, index) => {
        // Make a clone of the original card
        const originalCard = dojo.byId(card.id);
        // Get the computed style of the original card
        const computedStyle = window.getComputedStyle(originalCard);
        const backgroundImage = computedStyle.backgroundImage;
        
        const tempCard = dojo.create('div', {
            'class': 'temp-card',
            'data-original-card-id': card.id,
            'data-original-row': card.location.row,
            'data-original-col': card.location.col,
            'style': `
                left: ${card.location.x}px; 
                top: ${card.location.y}px; 
                background-image: ${backgroundImage || "none"};
                background-position: ${computedStyle.backgroundPosition};
            `
        }, this.board);
                
        return tempCard;
    });
    
    // Create the group controls
    this.createCardGroupControls();
    
    // Store the initial positions for rotation calculations
    this.storeInitialCardPositions();
    
    // Make the entire group draggable
    this.makeGroupDraggable();
    
    // Enable confirm placement button, disable selection
    dojo.style('confirm_placement', 'display', 'block');
    dojo.style('confirm_selection', 'display', 'none');
    
    // Add visual hint for valid placement
    this.showMessage(_("Drag cards to position and rotate them. At least one card must be adjacent to an existing card."), "info");
},

// Store initial positions for rotation reference
storeInitialCardPositions: function() {
    // Find the center of the card group
    let totalX = 0, totalY = 0;
    this.tempCards.forEach(card => {
        const left = parseInt(card.style.left);
        const top = parseInt(card.style.top);
        totalX += left + (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--size')) / 2);
        totalY += top + (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--size')) / 2);
    });
    
    const centerX = totalX / this.tempCards.length;
    const centerY = totalY / this.tempCards.length;
    
    // Store center point and initial positions relative to center
    this.cardGroupCenter = { x: centerX, y: centerY };
    this.cardInitialPositions = this.tempCards.map(card => {
        const left = parseInt(card.style.left);
        const top = parseInt(card.style.top);
        const cardSize = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--size'));
        
        return {
            element: card,
            relX: (left + (cardSize / 2)) - centerX,
            relY: (top + (cardSize / 2)) - centerY,
            row: parseInt(card.getAttribute('data-original-row')),
            col: parseInt(card.getAttribute('data-original-col'))
        };
    });
    
    // Initialize rotation state
    this.currentRotation = 0;
},

// Create controls for the card group
createCardGroupControls: function() {
    // Remove any existing controls
    dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
    
    // Create a container for the controls
    const controls = dojo.create('div', {
        'class': 'card-group-controls',
        'style': 'top: -50px; left: 0px;'
    }, this.board);
    
    // Add rotation button
    const rotateButton = dojo.create('div', {
        'class': 'rotate-all-button',
        'innerHTML': '↻',
        'title': 'Rotate all cards (90° clockwise)'
    }, controls);
    
    // Connect rotation handler
    dojo.connect(rotateButton, 'onclick', () => this.rotateAllCards());
    
    // Position the controls relative to the card group
    this.updateControlsPosition();
},

// Update position of the controls
updateControlsPosition: function() {
    const controls = dojo.query('.card-group-controls', this.board)[0];
    if (!controls || this.tempCards.length === 0) return;
    
    // Find the highest card in the group
    let minTop = Infinity;
    let avgLeft = 0;
    
    this.tempCards.forEach(card => {
        const top = parseInt(card.style.top);
        const left = parseInt(card.style.left);
        minTop = Math.min(minTop, top);
        avgLeft += left;
    });
    
    avgLeft = avgLeft / this.tempCards.length;
    
    // Position controls above the group
    controls.style.top = (minTop - 50) + 'px';
    controls.style.left = avgLeft + 'px';
},

// Rotate all cards while preserving their relative positions
rotateAllCards: function() {
    // Increment rotation (90° clockwise)
    this.currentRotation = (this.currentRotation + 90) % 360;
    const radians = (this.currentRotation * Math.PI) / 180;
    
    // Calculate new positions based on rotation around center
    this.cardInitialPositions.forEach(cardInfo => {
        // Apply rotation matrix
        const newRelX = cardInfo.relX * Math.cos(radians) - cardInfo.relY * Math.sin(radians);
        const newRelY = cardInfo.relX * Math.sin(radians) + cardInfo.relY * Math.cos(radians);
        
        const cardSize = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--size'));
        
        // Update card position (accounting for center of card)
        const newLeft = this.cardGroupCenter.x + newRelX - (cardSize / 2);
        const newTop = this.cardGroupCenter.y + newRelY - (cardSize / 2);
        
        // Snap to grid
        const snappedPosition = this.snapToGrid(newLeft, newTop);
        
        // Apply position
        cardInfo.element.style.left = snappedPosition.left + 'px';
        cardInfo.element.style.top = snappedPosition.top + 'px';
        
        // Validate placement
        this.validateTempCardPlacement(cardInfo.element);
    });
    
    // Update controls position
    this.updateControlsPosition();
},

// Make the entire group draggable
makeGroupDraggable: function() {
    // Initial state for dragging
    let isDragging = false;
    let startX, startY;
    let cardOffsets = [];
    
    // Initialize dragging when any card is clicked
    this.tempCards.forEach(card => {
        dojo.connect(card, 'onmousedown', (evt) => {
            evt.preventDefault();
            isDragging = true;
            startX = evt.clientX;
            startY = evt.clientY;
            
            // Store current positions of all cards
            cardOffsets = this.tempCards.map(c => ({
                element: c,
                offsetLeft: parseInt(c.style.left),
                offsetTop: parseInt(c.style.top)
            }));
            
            // Add move and mouseup handlers
            const moveHandle = dojo.connect(document, 'onmousemove', handleMouseMove);
            const upHandle = dojo.connect(document, 'onmouseup', () => {
                isDragging = false;
                dojo.disconnect(moveHandle);
                dojo.disconnect(upHandle);
                
                // Snap all cards to grid
                this.snapAllCardsToGrid();
                
                // Update center point
                this.updateGroupCenter();
                
                // Validate all card placements
                this.tempCards.forEach(c => this.validateTempCardPlacement(c));
                
                // Update controls position
                this.updateControlsPosition();
            });
        });
    });
    
    // Handle mouse movement for the entire group
    const handleMouseMove = (moveEvt) => {
        if (!isDragging) return;
        
        const deltaX = moveEvt.clientX - startX;
        const deltaY = moveEvt.clientY - startY;
        
        // Move all cards by the same delta
        cardOffsets.forEach(cardOffset => {
            cardOffset.element.style.left = (cardOffset.offsetLeft + deltaX) + 'px';
            cardOffset.element.style.top = (cardOffset.offsetTop + deltaY) + 'px';
        });
        
        // Update controls position while dragging
        this.updateControlsPosition();
    };
},

// Snap to grid based on card size and gap
snapToGrid: function(left, top) {
    const rootStyles = getComputedStyle(document.documentElement);
    const cardSize = parseInt(rootStyles.getPropertyValue('--size'));
    const gap = parseInt(rootStyles.getPropertyValue('--gap'));
    const cellSize = cardSize + gap;
    
    // Snap to nearest grid cell
    const snappedLeft = Math.round(left / cellSize) * cellSize;
    const snappedTop = Math.round(top / cellSize) * cellSize;
    
    return { left: snappedLeft, top: snappedTop };
},

// Snap all cards to grid
snapAllCardsToGrid: function() {
    this.tempCards.forEach(card => {
        const left = parseInt(card.style.left);
        const top = parseInt(card.style.top);
        const snappedPosition = this.snapToGrid(left, top);
        
        card.style.left = snappedPosition.left + 'px';
        card.style.top = snappedPosition.top + 'px';
    });
},

// Update the group center after moving
updateGroupCenter: function() {
    let totalX = 0, totalY = 0;
    const cardSize = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--size'));
    
    this.tempCards.forEach(card => {
        const left = parseInt(card.style.left);
        const top = parseInt(card.style.top);
        totalX += left + (cardSize / 2);
        totalY += top + (cardSize / 2);
    });
    
    this.cardGroupCenter = {
        x: totalX / this.tempCards.length,
        y: totalY / this.tempCards.length
    };
},

// Improved finalize function that creates permanent cards
finalizeCardPlacement: function() {
    console.log("Finalizing card placement");
    
    // Validate all temp cards
    this.tempCards.forEach(card => this.validateTempCardPlacement(card));
    
    const validCards = dojo.query('.temp-card.valid-placement', this.board);
    console.log("Valid cards:", validCards.length, "of", this.tempCards.length);
    
    if (validCards.length !== this.tempCards.length) {
        this.showMessage(_("Invalid placement. All cards must be within bounds and adjacent to existing cards."), "error");
        return;
    }
    
    // Collect final placement details
    const placements = [];
    validCards.forEach(card => {
        const originalCardId = card.getAttribute('data-original-card-id');
        
        // Get card position relative to grid
        const left = parseInt(card.style.left);
        const top = parseInt(card.style.top);
        
        // Calculate grid position
        const rootStyles = getComputedStyle(document.documentElement);
        const gridSize = parseInt(rootStyles.getPropertyValue('--size'));
        const gap = parseInt(rootStyles.getPropertyValue('--gap'));
        
        // Calculate closest grid position
        const col = Math.round(left / (gridSize + gap)) + 1;
        const row = Math.round(top / (gridSize + gap)) + 1;
        
        // Get the corresponding location ID for this position
        const locationId = this.getLocationIdFromPosition(row, col);
        
        placements.push({
            cardId: originalCardId,
            rotation: this.currentRotation, // Use the group rotation
            locationId: locationId
        });
        
        // Create a permanent card
        const permanentCard = dojo.create('div', {
            'class': 'board-card',
            'data-original-card-id': originalCardId,
            'style': `
                position: absolute; 
                left: ${left}px; 
                top: ${top}px; 
                width: var(--size); 
                height: var(--size); 
                transform: rotate(${this.currentRotation}deg);
                background-image: ${card.style.backgroundImage};
                background-size: cover;
            `
        }, this.board);
    });
    
    // Remove temp cards and controls
    dojo.query('.temp-card', this.board).forEach(dojo.destroy);
    dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
    
    // Reset rotation state
    this.currentRotation = 0;
    
    // Send placements to server
    this.sendCardPlacements(placements);
    
    // Hide placement button, show selection button again
    dojo.style('confirm_placement', 'display', 'none');
    dojo.style('confirm_selection', 'display', 'block');
},

// Improved adjacency check for board cards
checkAdjacentToExistingCards: function(tempCard) {
    const boardCards = dojo.query('.board-card', this.board);
    if (boardCards.length === 0) {
        // If no cards yet, any position is valid
        return true;
    }
    
    const tempRect = tempCard.getBoundingClientRect();
    
    return Array.from(boardCards).some(boardCard => {
        const boardRect = boardCard.getBoundingClientRect();
        
        // Check if the cards are adjacent (within a threshold)
        const threshold = 5; // Small threshold to account for rounding errors
        
        // Adjacent horizontally
        const adjacentHorizontally = 
            Math.abs((boardRect.right) - tempRect.left) < threshold ||
            Math.abs((tempRect.right) - boardRect.left) < threshold;
            
        // Adjacent vertically
        const adjacentVertically = 
            Math.abs((boardRect.bottom) - tempRect.top) < threshold ||
            Math.abs((tempRect.bottom) - boardRect.top) < threshold;
            
        // Check if cards are touching either horizontally or vertically (not diagonally)
        const touchingHorizontally = adjacentHorizontally && 
            !(tempRect.bottom < boardRect.top || tempRect.top > boardRect.bottom);
            
        const touchingVertically = adjacentVertically && 
            !(tempRect.right < boardRect.left || tempRect.left > boardRect.right);
            
        return touchingHorizontally || touchingVertically;
    });
},

// Get location ID from row/col position
getLocationIdFromPosition: function(row, col) {
    // Based on your location mapping
    const baseId = 207; // ID offset
    return baseId + ((row - 1) * 4) + col;
},

// Send placements to server with proper ajaxcall
sendCardPlacements: function(placements) {
    console.log("Sending placements to server:", placements);
    
    // Format the placements for server
    const placementData = JSON.stringify(placements);
    
    console.log(placementData)
},
getCardLocation: function(cardId, area) {
    const rootStyles = getComputedStyle(document.documentElement);
    const gap = parseInt(rootStyles.getPropertyValue('--gap'), 10);
    const size = parseInt(rootStyles.getPropertyValue('--size'), 10);

    const cards = {};
    let cardIdStart = 208; // Start ID for first card
    let rows = 4, cols = 4; // Grid dimensions

    for (let row = 1; row <= rows; row++) {
        for (let col = 1; col <= cols; col++) {
            const x = (col - 1) * (size + gap);
            const y = (row - 1) * (size + gap);
            cards[cardIdStart++] = { x, y, row, col };
        }
    }

    const locationMap = {
        'pattern-area': cards,
        'player-board': {} // Fill this if needed
    };
    return locationMap[area][cardId]
},
areCardsAdjacent: function(locations) {
    if (locations.length <= 1) return true;

    for (let i = 0; i < locations.length; i++) {
        let isAdjacentToAny = false;
        for (let j = 0; j < locations.length; j++) {
            if (i !== j && this.isAdjacent(locations[i], locations[j])) {
                isAdjacentToAny = true;
                break;
            }
        }
        if (!isAdjacentToAny) {
            return false;
        }
    }
    return true;
},

isAdjacent: function(loc1, loc2) {
    return (
        (Math.abs(loc1.row - loc2.row) === 1 && loc1.col === loc2.col) ||
        (Math.abs(loc1.col - loc2.col) === 1 && loc1.row === loc2.row)
    );
},


validateTempCardPlacement: function(card) {
    const boardCards = dojo.query('.board-card', this.board);
    const isFirstPlacement = boardCards.length === 0;
    
    // Check if card overlaps existing cards or is out of board bounds
    const isValidPlacement = isFirstPlacement || 
        this.checkAdjacentToExistingCards(card);
    
    // Change card appearance based on validity
    dojo.toggleClass(card, 'valid-placement', isValidPlacement);
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
        
            //The number passed in is the max amount of cards that can be selected
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
        
        


        changeSelectables: function (maxSelected, minSelected = 2) {
            const pattern_board = document.querySelector(".pattern-board")
            let selectedCards = pattern_board.querySelectorAll(".selected"); // Use correct class name
            // Retrieving it correctly inside changeSelectables()
            let selectedLocations = Array.from(pattern_board.querySelectorAll(".selected"))
            .map(card => card.getAttribute("location")) // Now correctly retrieves the attribute
            .filter(loc => loc !== null) // Ensure location exists
            .map(loc => parseInt(loc, 10)); // Convert to integer safely

            console.log("Selected Locations:", selectedLocations); // Check if it works now

            console.log("length:  " +selectedCards.length)

            if (selectedCards.length >= minSelected) {
                document.getElementById("confirm_selection").style.display = 'inline-block'
            } else {
                document.getElementById("confirm_selection").style.display = 'none'
            }

        
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
