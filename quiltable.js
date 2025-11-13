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
    "ebg/counter", "dojo/query",
    getLibUrl('bga-score-sheet', '1.x'),

],
function (dojo, declare, gui, counter, query, BgaScoreSheet) {
    return declare("bgagame.quiltable", ebg.core.gamegui, {
        constructor: function(){
            console.log('quiltable constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;
            this.selectableBlocks = []
            this.log_span_num = 0
            this.playerId
            this.selectedCards = []; // Stores selected card objects with row/col
            this.tempCards = []; // Temporary card elements for preview
            this.board
            this.isShiftEnabled = true
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
            this.playerCount = Object.keys(gamedatas.players).length
            this.options = gamedatas.options
            this.playerId = this.player_id
            this.locations = gamedatas.locations
            this.types = gamedatas.type_arg
            // Example to add a div on the game area
            document.getElementById('play-board').insertAdjacentHTML('beforeend', `
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
                    card_cont.setAttribute("location", card.location_arg)
                    card_cont.setAttribute("type", card.type_arg)
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
                card_cont.setAttribute("type", card.type_arg)
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
                        <div class="title" style="color:#${player.color}">${player.name}</div>
                        <div class="quilt-cont">
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
                    </div>
                `);
                if (player.id == this.player_id) {
                    document.getElementById("player-table-"+player.id).style.order = -1
                }

                const patterns = document.createElement("section")
                patterns.classList.add("patterns")

                dojo.query(`#player-table-${player.id} .quilt-cont`)[0].appendChild(patterns)
                // document.getElementById("player-table-"+player.id).appendChild(patterns)
                this.board = dojo.query(`#player-table-${this.player_id} .quilt-board`)[0];

                if (Object.keys(gamedatas.boards).includes(player.id)) {
                    this.setup_board_cards(gamedatas.boards[player.id], player.id)
                }
            });
            this.addShiftControls(gamedatas)
            
            Object.values(gamedatas.players).forEach(player => {
            if (gamedatas.gamestate.name != "choose_assistant" && gamedatas.players[player.id].assistant != "0") {
                    this.setup_assistant(gamedatas.players[player.id].assistant, player.id)
                }
            });


            
            // TODO: Set up your game interface here, according to "gamedatas"
            
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
            console.log("gamedatas")
            console.log(gamedatas)

            this.scoreSheet = new BgaScoreSheet.ScoreSheet(
                document.getElementById(`my-score-sheet`), // an empty div on your template to place the score sheet on
                {
                animationsActive: () => this.bgaAnimationsActive(), // so the animation doesn't trigger on replay fast mode
                playerNameWidth: 80,
                playerNameHeight: 30,
                entryLabelWidth: 120,
                entryLabelHeight: 20,
                classes: 'score-sheet-background',
                players: gamedatas.players,
                entries: [
                    {
                    property: 'completed',
                    label: _('Completed Quilt'),
                    labelClasses: 'entries-label',
                    },
                    {
                    property: 'symmetry',
                    label: _('Symmetrical quilt'),
                    labelClasses: 'entries-label',
                    },
                    {
                    property: 'patches',
                    label: _('Patch points'),
                    labelClasses: 'entries-label',
                    },
                    {
                    property: 'premium',
                    label: _('Premium points'),
                    labelClasses: 'entries-label',
                    },
                    {
                    property: 'patterns',
                    label: _('Pattern cards'),
                    labelClasses: 'entries-label',
                    },
                    {
                    property: 'total',
                    label: _('Total'),
                    labelClasses: 'entries-label',
                    scoresClasses: 'total',
                    width: 80,
                    height: 40,
                    },
                ],
                scores: gamedatas.endScores, // to defined if the game state is 99, else null, so the score displays directly on reload when the game is ended. If unset, the score sheet will be hidden by default.
                onScoreDisplayed: (property, playerId, score) => { // if you want to do something when a score is revealed
                    if (property === 'total') {
                    this.scoreCtrl[playerId].setValue(score);
                    }
                },
                }
            );    

            console.log( "Ending game setup" );
        },
        destroyAnimation: function (card_id) {
            let element = document.getElementById(`${card_id}`);
            if (element) {
                element.remove(); // Removes from DOM
            }
        },
        
        setup_assistant: function(assistant, id) {
            const board = dojo.query(`#player-table-${id} .quilt-controls-container`)[0]

            console.log(board)
            const assis = document.createElement("div")
            assis.classList.add(`${this.types[assistant].class}`, "card")
            board.appendChild(assis)

        },

       setup_board_cards: function(data, playerid) {
        const patterns = dojo.query(`#player-table-${playerid} .quilt-cont .patterns`)[0]
        const player_board = document.getElementById("player-table-"+playerid).querySelector(".quilt-board")
        player_board.querySelectorAll(".card").forEach(card => card.remove())
        Object.values(data).forEach(card => {
            const card_cont = document.createElement("div")
            card_cont.classList.add("card")
            card_cont.classList.add(this.types[card.type_arg].class)
            card_cont.id = card.id
            card_cont.setAttribute("location", card.location_arg)
            card_cont.setAttribute("arg", "player-table-"+playerid)
            card_cont.setAttribute("type", card.type_arg)
            if (card.location_arg != "0") {
                card_cont.style.left = this.locations[card.location_arg].x + "px"
                card_cont.style.top = this.locations[card.location_arg].y + "px"
                player_board.appendChild(card_cont) 
            } else {
                patterns.appendChild(card_cont)
            }
        })
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

            case 'choose_assistant':
                this.setUpAssistants(args)
                break;
           
            case 'plan':
                this.selectPatterns(args)
                break;
            case 'plan2':
                this.selectPatterns(args)
                break;
            case 'playerTurn':
                this.animateCards(args)
                break;
            case 'playerTurn2':
                this.animateCards(args)
                break;
            case 'postEnd':
                this.animateCards(args)
                break;
            case 'choose':
                if (this.player_id == args.active_player) {
                    this.selectableBlocks = args.args
                    args.args.forEach((item) => {const card = document.getElementById(item.id)
                        card.classList.add("selectable-card")
                        card.boundSelectPlan = (event) => this.chooseBlocks(event, 3, 2, document.querySelector(".pattern-board"))
                        card.addEventListener("click", card.boundSelectPlan)})
                }
                break;
            case 'choose2':
                if (this.player_id == args.active_player) {
                    this.selectableBlocks = args.args
                    args.args.forEach((item) => {const card = document.getElementById(item.id)
                        card.classList.add("selectable-card")
                        card.boundSelectPlan = (event) => this.chooseBlocks(event, 3, 2, document.querySelector(".pattern-board"))
                        card.addEventListener("click", card.boundSelectPlan)})
                }
                break;
            case 'return':
                if (this.player_id == args.active_player) {
                    this.selectableBlocks = args.args
                    args.args.forEach((item) => {const card = document.getElementById(item.id)
                        card.classList.add("selectable-card")
                        card.boundSelectPlan = (event) => this.chooseBlocks(event, 4, 1, dojo.query(".quilt-board", "player-table-" + this.player_id)[0])
                        card.addEventListener("click", card.boundSelectPlan)})
                }
                break;
            case 'return2':
                if (this.player_id == args.active_player) {
                    this.selectableBlocks = args.args
                    args.args.forEach((item) => {const card = document.getElementById(item.id)
                        card.classList.add("selectable-card")
                        card.boundSelectPlan = (event) => this.chooseBlocks(event, 4, 1, dojo.query(".quilt-board", "player-table-" + this.player_id)[0])
                        card.addEventListener("click", card.boundSelectPlan)})
                }
                break;
            case 'returnBlock':
                if (this.player_id == args.active_player) {
                this.setUpReturnLocations(args)
                }
                this.animateCards(args)
                break;
            case 'returnBlock2':
                if (this.player_id == args.active_player) {
                this.setUpReturnLocations(args)
                }
                this.animateCards(args)
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
                // Clear previous temporary cards
                dojo.query('.temp-card', this.board).forEach(dojo.destroy);
                dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
                break;
            case 'choose2':
                this.removePatterns()
                // Clear previous temporary cards
                dojo.query('.temp-card', this.board).forEach(dojo.destroy);
                dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
                break;
            case 'return':
                this.removePatterns()
                break;
            case 'return2':
                this.removePatterns()
                break;
            case 'returnBlock':
                this.removePatterns()
                break;
            case 'returnBlock2':
                this.removePatterns()
                break;
            case 'choose_assistant':
                this.removePatterns()
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
                    if (this.options == "1" || this.playerCount == 1) {
                        this.statusBar.addActionButton(_('Plan'), () => this.bgaPerformAction("plan"))
                    }
                    this.statusBar.addActionButton(_('Choose'), () => this.bgaPerformAction("choose"))
                    this.statusBar.addActionButton(_('Return'), () => this.bgaPerformAction("return"))
                    break;
                 case 'playerTurn2':
                    if (this.options == "1" || this.playerCount == 1) {
                        this.statusBar.addActionButton(_('Plan'), () => this.bgaPerformAction("plan"))
                    }
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
                    this.statusBar.addActionButton(_('Confirm Selection'), () => this.confirmReturnCards(), {id: 'confirm_selection'})
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
                    this.statusBar.addActionButton(_('Confirm Selection'), () => this.confirmReturnCards(), {id: 'confirm_selection'})
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

            addShiftControls: function(gamedatas) {
                Object.values(gamedatas.players).forEach(player => {
                    const quiltBoard = dojo.query(`#player-table-${player.id} .quilt-cont`)[0];
                    if (!quiltBoard) return;
                    
                    // Only add controls for the current player
                   
                        // Create the controls container
                        const controlsContainer = document.createElement('div');
                        controlsContainer.classList.add('quilt-controls-container');
                        quiltBoard.appendChild(controlsContainer);
                        
                        // Create direction buttons
                        const directions = ['up', 'down', 'left', 'right'];

                        
                        controlsContainer.classList.add('quilt-controls-container');
                        quiltBoard.appendChild(controlsContainer);

                        if (this.playerId == player.id) {
                            const shift_cont = document.createElement('div');
                            shift_cont.classList.add("controls")
                            // TODO add a class for this 
                            //shift_cont.classList.add()
                            controlsContainer.appendChild(shift_cont)
                            directions.forEach(direction => {
                                const button = document.createElement('div');
                                button.classList.add(`move${direction}`);
                                // button.innerHTML = `<span class="shift-icon">${icons[direction]}</span>`;
                                button.title = `Shift Quilt ${direction.charAt(0).toUpperCase() + direction.slice(1)}`;
                                button.addEventListener('click', () => this.shiftQuilt(direction));
                                shift_cont.appendChild(button);
                            });
                    }
                        
                        // Initial button state update
                        //updateShiftButtonStates();
                    
                });
            },
            shiftQuilt: function(direction) {
                console.log(`Shifting quilt ${direction}`);
                
                const left = [208, 212, 216, 220]
                const top = [208, 209, 210, 211]
                const right = [211, 215, 219, 223]
                const bottom = [220, 221, 222, 223]
                let args = {"args":{"animation":{}}}

                const cards = dojo.query(`#player-table-${this.playerId} .quilt-board .card`)
                let shift = true

                if (this.isShiftEnabled && cards.length > 0) {

                    if (direction == "left") {
                        cards.forEach(card => {
                            let loc = parseInt(card.getAttribute("location"))
                            if (left.includes(loc))
                            {
                                shift=false
                            }
                        });
                        if (shift) {
                            index = 0
                            cards.forEach(card => {
                                let loc = parseInt(card.getAttribute("location"))
                                args.args.animation[index] = {"card_id":card.id, "target":`player-table-${this.playerId}`, "loc":loc-1}
                                index ++
                            })
                            this.animateCards(args, 0)
                        }
                    } else if (direction == "up") {
                        cards.forEach(card => {
                            loc = parseInt(card.getAttribute("location"))
                            if (top.includes(loc))
                            {
                                shift=false
                            }
                        });
                        if (shift) {
                            index = 0
                            cards.forEach(card => {
                                let loc = parseInt(card.getAttribute("location"))
                                args.args.animation[index] = {"card_id":card.id, "target":`player-table-${this.playerId}`, "loc":loc-4}
                                index ++
                            })
                            this.animateCards(args, 0)
                        }
                    } else if (direction == "right") {
                        cards.forEach(card => {
                            loc = parseInt(card.getAttribute("location"))
                            if (right.includes(loc))
                            {
                                shift=false
                            }
                        });
                        if (shift) {
                            index = 0
                            cards.forEach(card => {
                                let loc = parseInt(card.getAttribute("location"))
                                args.args.animation[index] = {"card_id":card.id, "target":`player-table-${this.playerId}`, "loc":loc+1}
                                index ++
                            })
                            this.animateCards(args, 0)
                        }
                    } else if (direction == "down") {
                        cards.forEach(card => {
                            loc = parseInt(card.getAttribute("location"))
                            if (bottom.includes(loc))
                            {
                                shift=false
                            }
                        });
                        if (shift) {
                            index = 0
                            cards.forEach(card => {
                                let loc = parseInt(card.getAttribute("location"))
                                args.args.animation[index] = {"card_id":card.id, "target":`player-table-${this.playerId}`, "loc":loc+4}
                                index ++
                            })
                            this.animateCards(args, 0)
                        }
                    }
                    if (shift) {
                    this.isShiftEnabled = false
                    this.ajaxcall(
                        "quiltable/quiltable/ajax_shiftQuilt.html",  // Path to your PHP handler
                        { 
                            shiftDirection: direction 
                        },
                        this,
                        function (result) {
                            console.log("Success!", result);
                        }
                    );
                    }
                
                }
                
                // Here you would call your server action
                // For example:
                // gameui.ajaxcall(
                //     "/yourGame/yourGame/shiftQuilt.html",
                //     { lock: true, direction: direction },
                //     gameui,
                //     function(result) {},
                //     function(is_error) {}
                // );
                
                // For demonstration purposes, let's simulate updating button states
                // In a real implementation, you would update this based on server response
                //updateShiftButtonStates();
            },

            getGridElement: function(gridSelector, row, col, colsPerRow) {
                const index = (row - 1) * colsPerRow + (col - 1); // Convert row & col to zero-based index
                return document.querySelector(`${gridSelector} > :nth-child(${index + 1})`);
            },
            

            animateCards: function(args, delay_inc=50) {
                console.log(args)
                delay = 0
                Object.values(args.args.animation).forEach(element => {
                    let target
                    const original = document.getElementById(element.card_id)
                    const card = original.cloneNode(true)
                    card.style.zIndex = 300
                    card.id = element.card_id+100
                    
                    // Append to the correct parent
                    if (original.hasAttribute("arg")) {
                        document.getElementById(original.getAttribute("arg")).querySelector(".quilt-board").appendChild(card)
                    } else {
                        document.getElementById("pattern-board").appendChild(card)
                    }
                    original.style.display = "none"
            
                    // Flipping logic for cards
                    if (element.flip > 0) {
                        target = document.getElementById("pattern-board");
                        const gridElement = this.getGridElement("#pattern-board", this.locations[element.loc].row-1, this.locations[element.loc].col-1, 4)
                        
                        // Enhanced flip animation
                        this.createEnhancedFlipAnimation(card, original, gridElement, element, delay)
                    } else if (element.loc == "0") {
                        // Existing pattern placement logic remains the same
                        target = dojo.query("#"+element.target + " .patterns")[0]
                        const placeholder = dojo.create("div", {className: "temp-pattern", id: "placeholder"}, target)
            
                        let patternAnimation = this.slideToObject(card, placeholder, 500, delay).play();
                        dojo.connect(patternAnimation, "onEnd", () => {
                            target.appendChild(original)
                            original.setAttribute("location", element.loc)
                            original.style.display = ""
                            original.style.position = "static"
                            this.destroyAnimation(element.card_id+100);
                            placeholder.remove()
                        })
                    } else {
                        // Existing movement logic remains the same
                        target = element.target !== "pattern-board" ? document.getElementById(element.target).querySelector(".quilt-board") : document.getElementById("pattern-board");
                        let animation = this.slideToObjectPos(card, target, this.locations[element.loc].x, this.locations[element.loc].y, 500, delay).play();
                        dojo.connect(animation, "onEnd", () => {
                            target.appendChild(original)
                            original.setAttribute("location", element.loc)
                            original.style.top = this.locations[element.loc].y+ "px"
                            original.style.left = this.locations[element.loc].x + "px"
                            original.style.display = ""
                            this.destroyAnimation(element.card_id+100);
                        });
                    }
            
                    // Set or remove arg attribute
                    if (element.target != "pattern-board") {
                        original.setAttribute("arg", element.target)
                    } else {
                        original.removeAttribute("arg")
                    }
                    delay += delay_inc
                });
            },
            
            createEnhancedFlipAnimation: function(card, original, gridElement, element, delay) {
                // Prepare the card for transformation
                card.style.transition = 'transform 0.5s';
                card.style.transformStyle = 'preserve-3d';
                card.style.backfaceVisibility = 'hidden';
            
                // Slide and flip animation
                let slideAnimation = this.slideToObject(card, gridElement, 500, delay)
                dojo.connect(slideAnimation, "onEnd", () => {
                    // Perform the flip in a single step
                    card.style.transform = 'rotateY(360deg)';
                    
                    // Use a timeout to change classes after half the animation
                    setTimeout(() => {
                        // Change the card's classes and attributes
                        const currentClasses = card.classList;
                        const originalClasses = original.classList;
                        
                        // Remove old type classes
                        currentClasses.remove(this.types[this.types[element.flip].other_side].class);
                        originalClasses.remove(this.types[this.types[element.flip].other_side].class);
                        
                        // Add new type classes
                        currentClasses.add(this.types[element.flip].class);
                        originalClasses.add(this.types[element.flip].class);
                        
                        // Update type and location attributes
                        original.style.zIndex = ""
                        original.setAttribute("type", element.flip)
                        original.setAttribute("location", element.loc)
                    }, 250);  // Halfway through the animation
            
                    // Final placement and cleanup
                    setTimeout(() => {
                        original.style.top = this.locations[element.loc].y + "px";
                        original.style.left = this.locations[element.loc].x + "px";
                        original.style.display = "block";
                        
                        // Reset transform and clean up
                        card.style.transform = '';
                        this.destroyAnimation(card.id);
                    }, 500);  // Full animation duration
                });
                
                // Start the slide and flip sequence
                slideAnimation.play();
            },
        
       returnCard: function (event) {
        this.bgaPerformAction("confirmReturn", { 
            loc: event.target.id
        }).then(() => {});
       },


       setUpAssistants: function(args) {
        console.log("### these are the args for the assitant cards")
        console.log(args)
        const element = document.getElementById("storage-item");
        element.innerHTML = "";

        has_selected = args.args[this.player_id].pop()
        
        args.args[this.player_id].forEach(option => {
            const display = document.createElement("div");
            display.id = "assistant_"+ option;
            display.style.position = "static"
            display.classList.add("card", this.types[option].class, "selectable-card");
            display.setAttribute("type", option)
            element.appendChild(display);

            if (option == has_selected) {
                display.classList.add("selected")
            }
            display.boundSelectAssistant = this.selectAssistant.bind(this);
            
            display.addEventListener("click", display.boundSelectAssistant)
        });
    
            const storage = dojo.query(".storage")[0];
            if (storage) {
              storage.style.display = "block"
                }
    
       },

       selectAssistant: function(event) {
            child = event.target
            parent = child.parentElement
            value = child.getAttribute("type")
            parent.querySelectorAll(".selected").forEach(element => {
                element.classList.remove("selected")
            });
            event.target.classList.add("selected")
            this.ajaxcall(
                    "quiltable/quiltable/ajax_chooseAssistant.html",
                    {
                        option: value
                    },
                    this,
                    function (result) {
                        console.log("Success!", result);
                    }
            );
        //     this.bgaPerformAction("ajax_chooseAssistant", { 
        //     option: event.target.getAttribute("type")
        // }).then(() => {});


        if (event.target.boundSelectAssistant) {
                    event.target.removeEventListener('click', event.boundSelectAssistant);
                    delete event.target.boundSelectAssistant
        }

       },


       setUpReturnLocations: function (args) {
            for (let i=224; i<= 243; i++) {
                const pattern_area = document.querySelector(".pattern-board")
                const newElement = dojo.create("div", {
                    className: "possible-card",
                    id: i
                }, pattern_area);
                newElement.style.left = this.locations[i].x + "px"
                newElement.style.top = this.locations[i].y + "px"
                newElement.boundSelectPlan = this.returnCard.bind(this)
                newElement.addEventListener("click", newElement.boundSelectPlan)
            }
            
                const element = document.getElementById("storage-item");
                element.innerHTML = "";
            
                const originalCard = document.getElementById(args.args.card.card_id);
                if(originalCard){
                    const display = originalCard.cloneNode(true);
                    display.id = "return";
                    display.style.position = "static"
                    display.classList.add("card-clone");
                    element.appendChild(display);
            
                    const storage = dojo.query(".storage")[0];
                    if (storage) {
                      storage.style.display = "block"
                    }
                }
              

       },

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

       confirmReturnCards: function () {
            const cards = dojo.query(".card.selectable-card.selected", "player-table-" + this.player_id);
            const cardIds = []

            cards.forEach(card => {

                cardIds.push(parseInt(card.id))
            })

            this.bgaPerformAction("returnBlocks", { 
                cards: JSON.stringify(cardIds)
            }).then(() => {}); 

       },
       removePatterns: function () {
            const cards = document.querySelectorAll('.card, .possible-card')
            this.selectedCards = []
            cards.forEach(card => {
                card.classList.remove('selectable-card')
                card.classList.remove('selected')
                if (card.boundSelectPlan) {
                    card.removeEventListener('click', card.boundSelectPlan);
                    delete card.boundSelectPlan
                }
                if (card.classList.contains("possible-card")) {
                    card.remove()
                }
            });

            const storage = dojo.query(".storage")[0];
                    if (storage) {
                      storage.style.display = "none"
                    }
       },



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
        
        // Store board dimensions for boundary checks
        this.calculateBoardDimensions();
        
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
                'style': `
                    touch-action: none;
                    will-change: transform;`,
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
        dojo.style('confirm_placement', 'display', 'inline-block');
        dojo.style('confirm_selection', 'display', 'none');
        
        // Add visual hint for valid placement
        this.showMessage(_("Drag cards to position and rotate them. At least one card must be adjacent to an existing card."), "info");
        // Snap all cards to grid
        this.snapAllCardsToGrid();
        
        // Keep cards within boundaries
        this.keepCardsWithinBoundaries();
        
        // Update center point
        this.updateGroupCenter();
        
        // Update controls position
        this.updateControlsPosition();
        
        // Validate initial placement
        this.tempCards.forEach(card => this.validateTempCardPlacement(card))
        this.synchronizeValidationState()
    },

    // Calculate and store board dimensions for boundary checks
    calculateBoardDimensions: function() {
        const boardRect = this.board.getBoundingClientRect();
        const boardStyle = window.getComputedStyle(this.board);
        
        // Get the board grid dimensions
        const rootStyles = getComputedStyle(document.documentElement);
        const cardSize = parseInt(rootStyles.getPropertyValue('--size'));
        const gap = parseInt(rootStyles.getPropertyValue('--gap'));
        
        // Calculate grid dimensions (assuming 9x9 grid)
        // You may need to adjust this based on your board size
        const gridSize = 4; // typical quilt board size
        const gridWidth = cardSize * gridSize + gap * (gridSize - 1);
        const gridHeight = gridWidth; // assuming square board
        
        // Store these dimensions for boundary checking
        this.boardBoundaries = {
            minX: 0,
            minY: 0,
            maxX: gridWidth - cardSize,
            maxY: gridHeight - cardSize,
            cardSize: cardSize,
            gap: gap,
            gridSize: gridSize
        };
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
    
    // Connect rotation handler for both click and touch
    dojo.connect(rotateButton, 'onclick', (evt) => {
        evt.preventDefault();
        evt.stopPropagation();
        this.rotateAllCards();
    });
    
    dojo.connect(rotateButton, 'ontouchend', (evt) => {
        evt.preventDefault();
        evt.stopPropagation();
        this.rotateAllCards();
    });
    
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
        const newLeft = parseFloat((this.cardGroupCenter.x + newRelX - (cardSize / 2)).toFixed(1));
        const newTop = parseFloat((this.cardGroupCenter.y + newRelY - (cardSize / 2)).toFixed(1));
        
        // Snap to grid
        const snappedPosition = this.snapToGrid(newLeft, newTop);
        
        // Apply position
        cardInfo.element.style.left = snappedPosition.left + 'px';
        cardInfo.element.style.top = snappedPosition.top + 'px';
    });
    
    // Enforce boundaries for all cards after rotation
    this.keepCardsWithinBoundaries();
    
    // Validate all placements
    this.tempCards.forEach(card => this.validateTempCardPlacement(card))
    this.synchronizeValidationState()
    
    // Update controls position
    this.updateControlsPosition();
},

// Make the entire group draggable (with touch support)
makeGroupDraggable: function() {
    // Initial state for dragging
    let isDragging = false;
    let startX, startY;
    let cardOffsets = [];
    
    const startDrag = (evt) => {
        // Get the initial event coordinates (works for both mouse and touch)
        const pageX = evt.type.includes('touch') ? evt.touches[0].pageX : evt.pageX;
        const pageY = evt.type.includes('touch') ? evt.touches[0].pageY : evt.pageY;
        
        isDragging = true;
        startX = pageX;
        startY = pageY;
        
        // Store current positions of all cards
        cardOffsets = this.tempCards.map(c => ({
            element: c,
            offsetLeft: parseInt(c.style.left),
            offsetTop: parseInt(c.style.top)
        }));
        
        // Prevent default behavior for touch events to disable scrolling
        if (evt.type.includes('touch')) {
            evt.preventDefault();
        }
    };
    
    const moveDrag = (evt) => {
        if (!isDragging) return;
        
        // Get the event coordinates (works for both mouse and touch)
        const pageX = evt.type.includes('touch') ? evt.touches[0].pageX : evt.pageX;
        const pageY = evt.type.includes('touch') ? evt.touches[0].pageY : evt.pageY;
        
        const deltaX = pageX - startX;
        const deltaY = pageY - startY;
        
        // Move all cards by the same delta
        cardOffsets.forEach(cardOffset => {
            cardOffset.element.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
        });
        
        // Update controls position while dragging
        //this.updateControlsPosition();
        
        // Prevent default behavior for touch events to disable scrolling
        if (evt.type.includes('touch')) {
            evt.preventDefault();
        }
    };
    
    const endDrag = (evt) => {
        if (!isDragging) return;
        isDragging = false;
        // Convert transform to actual positions
        // Convert transform to actual positions without animation
        // Disable transitions temporarily
        this.tempCards.forEach(card => card.style.transition = 'none');
        cardOffsets.forEach(cardOffset => {
            const currentTransform = cardOffset.element.style.transform;
            const match = currentTransform.match(/translate\((-?\d+(?:\.\d+)?)px,\s*(-?\d+(?:\.\d+)?)px\)/);
            
            if (match) {
                const deltaX = parseFloat(match[1]);
                const deltaY = parseFloat(match[2]);
                
                // Clear transform first, then update position in same frame
                cardOffset.element.style.transform = '';
                cardOffset.element.style.left = (cardOffset.offsetLeft + deltaX) + 'px';
                cardOffset.element.style.top = (cardOffset.offsetTop + deltaY) + 'px';
            }
        });
        // Re-enable transitions after a frame
        requestAnimationFrame(() => {
            this.tempCards.forEach(card => card.style.transition = '');
        });

        // Snap all cards to grid
        // Snap all cards to grid without animation
        this.tempCards.forEach(card => card.style.transition = 'none');
        this.snapAllCardsToGrid();
        // Force a reflow to apply the snap immediately
        this.tempCards[0].offsetHeight;
        this.tempCards.forEach(card => card.style.transition = '');
        
        // Keep cards within boundaries
        this.keepCardsWithinBoundaries();
        
        // Update center point
        this.updateGroupCenter();
        
        // Validate all card placements
        this.tempCards.forEach(c => this.validateTempCardPlacement(c))
        this.synchronizeValidationState()
        
        // Update controls position
        this.updateControlsPosition();
        
        // Prevent default behavior for touch events
        if (evt && evt.type.includes('touch')) {
            evt.preventDefault();
        }
    };
    
    // Add event listeners to all cards for both mouse and touch events
    this.tempCards.forEach(card => {
        // Mouse events
        dojo.connect(card, 'onmousedown', (evt) => {
            evt.preventDefault();
            startDrag(evt);
            
            // Add document-level handlers
            const moveHandle = dojo.connect(document, 'onmousemove', moveDrag);
            const upHandle = dojo.connect(document, 'onmouseup', (upEvt) => {
                endDrag(upEvt);
                dojo.disconnect(moveHandle);
                dojo.disconnect(upHandle);
            });
        });
        
        // Touch events
        dojo.connect(card, 'touchstart', (evt) => {
            startDrag(evt);
            
            // Add document-level handlers
            const touchMoveHandle = dojo.connect(document, 'touchmove', moveDrag);
            const touchEndHandle = dojo.connect(document, 'touchend', (endEvt) => {
                endDrag(endEvt);
                document.removeEventListener('touchmove', moveDrag);
                document.removeEventListener('touchend', endEvt);
            });
            
            // Prevent scrolling while dragging
            evt.preventDefault();
        });
    });
    
    // If BGA's framework provides a touch manager, use it
    if (this.framework && this.framework.touchManager) {
        // This is a BGA specific integration - uncomment if available
        // this.framework.touchManager.addDragEvents(this.tempCards, startDrag, moveDrag, endDrag);
    }
},

// Ensure cards stay within the board boundaries
keepCardsWithinBoundaries: function() {
    if (!this.boardBoundaries) return;
    
    // Check if any card is outside the boundaries
    let isOutOfBounds = false;
    let minOffsetX = 0, minOffsetY = 0, maxOffsetX = 0, maxOffsetY = 0;
    
    this.tempCards.forEach(card => {
        const left = parseInt(card.style.left);
        const top = parseInt(card.style.top);
        
        // Check if card is outside boundaries
        if (left < this.boardBoundaries.minX) {
            minOffsetX = Math.min(minOffsetX, left - this.boardBoundaries.minX);
            isOutOfBounds = true;
        }
        if (top < this.boardBoundaries.minY) {
            minOffsetY = Math.min(minOffsetY, top - this.boardBoundaries.minY);
            isOutOfBounds = true;
        }
        if (left > this.boardBoundaries.maxX) {
            maxOffsetX = Math.max(maxOffsetX, left - this.boardBoundaries.maxX);
            isOutOfBounds = true;
        }
        if (top > this.boardBoundaries.maxY) {
            maxOffsetY = Math.max(maxOffsetY, top - this.boardBoundaries.maxY);
            isOutOfBounds = true;
        }
    });
    
    // If any card is out of bounds, adjust all cards
    if (isOutOfBounds) {
        this.tempCards.forEach(card => {
            let left = parseInt(card.style.left);
            let top = parseInt(card.style.top);
            
            // Apply corrections
            if (minOffsetX < 0) left -= minOffsetX;
            if (maxOffsetX > 0) left -= maxOffsetX;
            if (minOffsetY < 0) top -= minOffsetY;
            if (maxOffsetY > 0) top -= maxOffsetY;
            
            // Ensure we're still within bounds after all adjustments
            left = Math.max(this.boardBoundaries.minX, Math.min(left, this.boardBoundaries.maxX));
            top = Math.max(this.boardBoundaries.minY, Math.min(top, this.boardBoundaries.maxY));
            
            // Apply corrected position
            card.style.left = left + 'px';
            card.style.top = top + 'px';
        });
        
        // Update the center point
        this.updateGroupCenter();
    }
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
    
    // Validate all temp cards individually
    this.tempCards.forEach(card => this.validateTempCardPlacement(card))
    this.synchronizeValidationState()
    
    // Check if any card is adjacent to existing cards (if not first placement)
    const isFirstPlacement = dojo.query('.card', this.board).length == 0;
    const isAdjacent = isFirstPlacement || this.checkAnyCardAdjacentToExisting();
    
    if (!isAdjacent) {
        this.showMessage(_("At least one card must be adjacent to an existing card."), "error");
        return;
    }
    
    const validCards = dojo.query('.temp-card.valid-placement', this.board);
    console.log("Valid cards:", validCards.length, "of", this.tempCards.length);
    
    if (validCards.length !== this.tempCards.length) {
        this.showMessage(_("Invalid placement. Cards must be within bounds and not overlap with existing cards."), "error");
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
    dojo.style('confirm_selection', 'display', 'inline-block');
},

// Improved adjacency check using grid coordinates
checkAdjacentToExistingCards: function(tempCard) {
    const boardCards = dojo.query('.card', this.board);
    if (boardCards.length === 0) {
        // If no cards yet, any position is valid
        return true;
    }
    
    // Calculate grid position for the temp card
    const left = parseInt(tempCard.style.left);
    const top = parseInt(tempCard.style.top);
    
    const rootStyles = getComputedStyle(document.documentElement);
    const cardSize = parseInt(rootStyles.getPropertyValue('--size'));
    const gap = parseInt(rootStyles.getPropertyValue('--gap'));
    const cellSize = cardSize + gap;
    
    const tempCol = Math.round(left / cellSize) + 1;
    const tempRow = Math.round(top / cellSize) + 1;
    
    // Check if any existing card is adjacent to this temp card
    return Array.from(boardCards).some(boardCard => {
        // Get location attribute and corresponding data
        const locationAttr = boardCard.getAttribute("location");
        if (!locationAttr || !this.locations[locationAttr]) {
            return false;
        }
        
        const boardLocation = this.locations[locationAttr];
        // -1 for the extra row and column in material for the replacement of tiles
        const boardRow = boardLocation.row-1;
        const boardCol = boardLocation.col-1;
        
        // Two cells are adjacent if they share a side
        // This means either:
        // 1. Same row, columns differ by 1
        // 2. Same column, rows differ by 1
        
        const sameRow = boardRow === tempRow;
        const sameCol = boardCol === tempCol;
        const adjacentCol = Math.abs(boardCol - tempCol) === 1;
        const adjacentRow = Math.abs(boardRow - tempRow) === 1;
        
        // Cards are adjacent if they're in the same row with adjacent columns
        // OR in the same column with adjacent rows
        return (sameRow && adjacentCol) || (sameCol && adjacentRow);
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

    // send move to server
    this.bgaPerformAction("placeBlocks", { 
        actionArgs: placementData
    }).then(() => {});
    
    console.log(placementData)
},
getCardLocation: function(cardId, area) {
    const loc = this.locations[cardId]

    const cards = {};
    const x = loc.x
    const y = loc.y
    const row = loc.row;
    const col = loc.col;
    cards[cardId] = { x, y, row, col };

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

// Helper to check if any card in the group is adjacent to existing cards
checkAnyCardAdjacentToExisting: function() {
    // Skip if no existing cards (first placement)
    const existingCards = dojo.query('.card', this.board);
    if (existingCards.length === 0) {
        return true; // First placement is always valid
    }
    
    // Check if any temp card is adjacent to an existing card
    for (let i = 0; i < this.tempCards.length; i++) {
        if (this.checkAdjacentToExistingCards(this.tempCards[i])) {
            return true;
        }
    }
    
    return false;
},


// Improved card placement validation
// Improved card placement validation using location attributes
validateTempCardPlacement: function(card) {
    let isValid = true;
    
    // Check if card is within board boundaries
    const left = parseInt(card.style.left);
    const top = parseInt(card.style.top);
    
    // Calculate the grid position of this temp card
    const rootStyles = getComputedStyle(document.documentElement);
    const cardSize = parseInt(rootStyles.getPropertyValue('--size'));
    const gap = parseInt(rootStyles.getPropertyValue('--gap'));
    const cellSize = cardSize + gap;
    
    const col = Math.round(left / cellSize) + 1;
    const row = Math.round(top / cellSize) + 1;
    
    // Check boundaries
    if (row < 1 || row > 4 || col < 1 || col > 4) {
        isValid = false;
    }
    
    // Check if this grid position is already occupied by an existing card
    if (isValid) {
        // Get all existing cards on the board
        const existingCards = dojo.query('.card', this.board);
        
        for (let i = 0; i < existingCards.length; i++) {
            const existingCard = existingCards[i];
            const existingLocationAttr = existingCard.getAttribute("location");
            
            // Skip if location attribute is missing
            if (!existingLocationAttr) continue;
            
            // Get the location data for this card
            const existingLocation = this.locations[existingLocationAttr];
            
            // Skip if location data is missing
            if (!existingLocation) continue;
            
            // Compare row and column
            if (existingLocation.row-1 === row && existingLocation.col-1 === col) {
                isValid = false;
                break;
            }
        }
    }
    
    // Update card appearance based on validity
    dojo.toggleClass(card, 'valid-placement', isValid);

    
    
    return isValid;
},

// BGA compatibility for touch events
setupBGATouchSupport: function() {
    // Add this method to your game setup if using BGA framework
    if (this.framework && typeof this.framework.enableTouchPlugin === 'function') {
        // Enable BGA's touch plugin if available
        this.framework.enableTouchPlugin();
        
        // Add class to handle touch events
        dojo.addClass(document.body, 'touch-enabled');
    } else {
        // Fallback: Add passive touch handler to document to prevent scrolling during drag
        document.addEventListener('touchmove', function(e) {
            const target = e.target;
            if (target.classList.contains('temp-card') || 
                target.classList.contains('card-group-controls') || 
                target.closest('.temp-card') || 
                target.closest('.card-group-controls')) {
                e.preventDefault();
            }
        }, { passive: false });
    }
},

// Synchronize validation state across all cards in the group
synchronizeValidationState: function() {
    // First check if any card is invalid
    let anyInvalid = false;
    
    for (let i = 0; i < this.tempCards.length; i++) {
        if (!dojo.hasClass(this.tempCards[i], 'valid-placement')) {
            anyInvalid = true;
            break;
        }
    }
    
    // If any card is invalid, make all cards invalid
    if (anyInvalid || !this.checkAnyCardAdjacentToExisting()) {
        for (let i = 0; i < this.tempCards.length; i++) {
            dojo.toggleClass(this.tempCards[i], 'valid-placement', false);
        }
    }
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

        chooseBlocks: function (element, maxSelected, minSelected, pattern_board) {
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
            this.changeSelectables(maxSelected, minSelected, pattern_board);
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
        
        


        changeSelectables: function (maxSelected, minSelected, pattern_board) {
            let selectedCards = pattern_board.querySelectorAll(".selected"); // Use correct class name
            // Retrieving it correctly inside changeSelectables()
            let selectedLocations = Array.from(pattern_board.querySelectorAll(".selected"))
            .map(card => card.getAttribute("location")) // Now correctly retrieves the attribute
            .filter(loc => loc !== null) // Ensure location exists
            .map(loc => parseInt(loc, 10)); // Convert to integer safely

            console.log("Selected Locations:", selectedLocations); // Check if it works now

            console.log("length:  " +selectedCards.length)

            // for the button so that it only appears when the min selected are selected
            if (selectedCards.length >= minSelected) {
                dojo.style('confirm_selection', 'display', 'inline-block');
            } else {
                dojo.style('confirm_selection', 'display', 'none');
            } 


        
            let adjacentLocations = new Set();
        
            // Find all adjacent locations from selected cards
            selectedLocations.forEach(loc => {
                let adjacents = this.getAdjacentLocations(loc);
                //locations that should not be added if there is a deck
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
                    if ((adjacentLocations.has(loc) || card.classList.contains("selected")) && this.types[card.getAttribute("type")].type == "back") {
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
                // Inner 4x4 Grid (Original)
                208: [209, 212, 224, 242],
                209: [208, 210, 213, 225],
                210: [209, 211, 214, 226],
                211: [210, 215, 227, 229],
                212: [208, 213, 216, 241],
                213: [209, 212, 214, 217],
                214: [210, 213, 215, 218],
                215: [211, 214, 219, 230],
                216: [212, 217, 220, 240],
                217: [213, 216, 218, 221],
                218: [214, 217, 219, 222],
                219: [215, 218, 223, 231],
                220: [216, 221, 239, 237],
                221: [217, 220, 222, 236],
                222: [218, 221, 223, 235],
                223: [219, 222, 234, 232],
        
                // Outer Border (New)
                224: [208, 225, 243],
                225: [209, 224, 226],
                226: [210, 225, 227],
                227: [211, 226, 228],
                228: [227, 229],
                229: [211, 228, 230],
                230: [215, 229, 231],
                231: [219, 230, 232],
                232: [223, 231, 233],
                233: [232, 234],
                234: [223, 233, 235],
                235: [222, 234, 236],
                236: [221, 235, 237],
                237: [220, 236, 238],
                238: [237, 239],
                239: [220, 238, 240],
                240: [216, 239, 241],
                241: [212, 240, 242],
                242: [208, 241, 243],
                243: [242, 224], //243 is the corner. Added 224 and 228 as adjacent.
        
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
            this.bgaSetupPromiseNotifications();

            // dojo.subscribe('scoring', this, "notif_endScores")
            // dojo.subscribe('plan', this, "notif_plan")
            // dojo.subscribe('chooseTiles', this, "notif_choose")
            // dojo.subscribe('return', this, "notif_return")
            // dojo.subscribe('assistant', this, "notif_assistant")
            // dojo.subscribe('shift', this, "notif_quiltShift")
            //this.notifqueue.setSynchronous( 'shift', 3000 );
        },

        notif_endScores(args) {
            // console.log("endScores")
            // console.log(args)
            return this.scoreSheet.setScores(args.endScores, {
                startBy: this.playerId
            });
        },

        async notif_animation(args) {
            await this.animateCards(args)
        },

        notif_plan: function(args) {

        },
        notif_chooseTiles: function(args) {
            console.log(args)
        },

        notif_return: function(args) {

        },

        notif_assistant: function(args) {
            console.log(args)
            this.setup_assistant(args.card_arg, args.player_id)
        },
        notif_shift: function(args) {
            console.log("shifted")
            console.log(args)
            const id = Object.values(args)[0].location;
            console.log(id)
            if (id) {
                console.log("inside id")
                const cards = dojo.query(`#player-table-${id} .quilt-board .card`)
                cards.forEach(element => {
                    element.remove()
                })
                this.setup_board_cards(args, id)
                this.isShiftEnabled = true
            }
        },

        notif_showPoints: function(args) {
            console.log("POINTS")
            console.log(args)
        },
        
        // TODO: from this point and below, you can write your game notifications handling methods
        

        format_string_recursive: function(log, args) {
            // console.log("I'm inside ")
            var text = '';
            if (log != '') {
                var log = this.clienttranslate_string(log); // TRANSLATION HAPPENS HERE !!
                if (log === null) {
                // THE RED BANNER YOU GET IN CASE OF MISSING TRANSLATION
                this.showMessage('Missing translation for `' + log + '`', 'error');
                console.error('Missing translation for `' + log + '`', 'error');
                return '';
                }

                [1] // that's for later
                [2] // and for even later

                try {
                // THIS IS WERE SUBSTITUTION TAKES PLACE
                text = dojo.string.substitute(log, args);
                } catch (e) {
                console.error('Invalid or missing substitution argument for log message: ' + _884, 'error');
                text = log;
                }
            }
            return this.logInject(text);
        },

        logInject: function (log_entry) {
    // Check if there's JSON card data in the log entry
    const json_regex = /(\[{.*?\}\])/;
    const json_match = log_entry.match(json_regex);
    
    if (json_match) {
        // We have multiple cards as JSON
        try {
            const card_data = JSON.parse(json_match[1]);
            const card_html = this.getHTMLGroupForLog(card_data, 'cards');
            log_entry = log_entry.replace(json_match[1], card_html);
        } catch (e) {
            console.error('Failed to parse card data:', e);
        }
    } else {
        // Fall back to single card bracket notation: [card_name(card_type_arg)]
        const card_regex = /\[(.+?)\((\d+)\)\]/g;
        const cards_to_replace = log_entry.matchAll(card_regex);
        
        for (let card of cards_to_replace) {
            const match = card[0];
            const card_type_arg = card[2];
            const card_span = this.getHTMLForLog(card_type_arg, 'card');
            log_entry = log_entry.replace(match, card_span);
        }
    }
    
    return log_entry;
},

getHTMLForLog: function (item, type) {
    switch(type) {
        case 'card':
            this.log_span_num++;
            const item_type = 'logcard';
            return `<span id="${this.log_span_num}_item_${item}" class="${item_type} ${this.types[item]['class']}"></span>`;
    }
},

getHTMLGroupForLog: function (card_data, type) {
    switch(type) {
        case 'cards':
            // Find min row and col
            let minRow = Math.min(...card_data.map(c => c.row));
            let minCol = Math.min(...card_data.map(c => c.col));
            
            // Normalize to 1-based grid
            const normalized = card_data.map(c => ({
                ...c,
                gridRow: c.row - minRow + 1,
                gridCol: c.col - minCol + 1
            }));
            
            // Find grid dimensions
            const maxRow = Math.max(...normalized.map(c => c.gridRow));
            const maxCol = Math.max(...normalized.map(c => c.gridCol));
            
            // Build grid HTML
            this.log_span_num++;
            const grid_id = `log_grid_${this.log_span_num}`;
            
            let grid_html = `<span id="${grid_id}" class="logcard-grid" style="display: inline-grid; grid-template-columns: repeat(${maxCol}, 40px); grid-template-rows: repeat(${maxRow}, 40px); gap: 2px; vertical-align: middle; margin: 0 3px;">`;
            
            for (let card of normalized) {
                const card_class = this.types[card.type_arg]['class'];
                grid_html += `<span class="logcard ${card_class}" style="grid-row: ${card.gridRow}; grid-column: ${card.gridCol};"></span>`;
            }
            
            grid_html += '</span>';
            return grid_html;
    }
},



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
