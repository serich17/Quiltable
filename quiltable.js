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
    "ebg/counter"

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
            this.unconected = false
            this.gladys = false
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
            this.assistantHandler = this.assistant.bind(this);
            this.miniCounters = {};
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
         if (this.playerCount == 1) {
                const master = document.createElement("div")
                master.classList.add("card")
                master.id = "205"
                master.style.zIndex = 205
                master.classList.add(gamedatas.type_arg[205].class)
                master.style.display = "none"
                // master.setAttribute("location", gamedatas.master)
                master.setAttribute("type", "205")
                pattern_area.appendChild(master)

                if(gamedatas.master){
                    master.style.left = gamedatas.locations[gamedatas.master].x + "px"
                    master.style.top = gamedatas.locations[gamedatas.master].y + "px"
                    master.setAttribute("location", gamedatas.master)
                    master.style.display = "block"
                }
            }

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
                if (this.playerCount == 1) {
                    this.getPlayerPanelElement(player.id).insertAdjacentHTML('beforeend', `
                            <div id="miniCounter_${player.id}" class="mini-counter">
                                <div class="mini-card"></div>
                                <span id="miniCounterValue_${player.id}" class="mini-counter-value"></span>
                            </div>
                        `);
                    this.miniCounters[player.id] = new ebg.counter();
                    this.miniCounters[player.id].create(`miniCounterValue_${player.id}`);
                    this.miniCounters[player.id].setValue(parseInt(gamedatas.master_num)); // starting value
                }

                // example of adding a div for each player
                document.getElementById('player-tables').insertAdjacentHTML('beforeend', `
                    <div id="player-table-${player.id}">
                        <div class="title" style="color:#${player.color}">${player.name}</div>
                        <div class="quilt-cont">
                        <div class="quilt-board">
                            <div class="item" location="208"></div>
                            <div class="item" location="209"></div>
                            <div class="item" location="210"></div>
                            <div class="item" location="211"></div>
                        
                            <div class="item" location="212"></div>
                            <div class="item" location="213"></div>
                            <div class="item" location="214"></div>
                            <div class="item" location="215"></div>
                        
                            <div class="item" location="216"></div>
                            <div class="item" location="217"></div>
                            <div class="item" location="218"></div>
                            <div class="item" location="219"></div>
                        
                            <div class="item" location="220"></div>
                            <div class="item" location="221"></div>
                            <div class="item" location="222"></div>
                            <div class="item" location="223"></div>
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
                if (player.id == 999) {
                    return
                }
            if (gamedatas.gamestate.name != "chooseAssistant" && gamedatas.players[player.id].assistant != "0") {
                    this.setup_assistant(gamedatas.players[player.id].assistant, player.id)
                }
            });

            
            // TODO: Set up your game interface here, according to "gamedatas"
            
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
            console.log("gamedatas")
            console.log(gamedatas)
            console.log(gamedatas.endScores)
            const scorePlayers = { ...gamedatas.players };

            if (this.playerCount == 1) {
                scorePlayers["999"] = {
                id: "999",
                name: _("Quilt Master"),
                color: "#000000",
                };  
            }

            console.log(scorePlayers)

            if (this.options != 2) {
                this.scoreSheet = new BgaScoreSheet.ScoreSheet(
                    document.getElementById(`my-score-sheet`), // an empty div on your template to place the score sheet on
                    {
                    animationsActive: () => this.bgaAnimationsActive(), // so the animation doesn't trigger on replay fast mode
                    playerNameWidth: 150,
                    playerNameHeight: 30,
                    entryLabelWidth: null,
                    entryLabelHeight: null,
                    classes: 'score-sheet-background',
                    players: scorePlayers,
                    entries: [
                        {
                        property: 'completed',
                        label: '<div class="icon completed"></div>',
                        labelClasses: 'entries-label',
                        scoresClasses: 'entries-label'
                        },
                        {
                        property: 'premium',
                        label: '<div class="icon premium"></div>',
                        labelClasses: 'entries-label',
                        scoresClasses: 'entries-label'
                        },
                        {
                        property: 'patterns',
                        label: '<div class="icon patterns_icon"></div>',
                        labelClasses: 'entries-label',
                        scoresClasses: 'entries-label'
                        },
                        {
                        property: 'patches',
                        label: '<div class="icon patches"></div>',
                        labelClasses: 'entries-label',
                        scoresClasses: 'entries-label'
                        },
                        {
                        property: 'symmetry',
                        label: '<div class="icon symmetry"></div>',
                        labelClasses: 'entries-label',
                        scoresClasses: 'entries-label'
                        },
                        {
                        property: 'total',
                        label: _('Total'),
                        labelClasses: 'entries-label total',
                        scoresClasses: 'total',
                        width: 80,
                        height: 40,
                        },
                    ],
                    scores: gamedatas.endScores, // to defined if the game state is 99, else null, so the score displays directly on reload when the game is ended. If unset, the score sheet will be hidden by default.
                    onScoreDisplayed: (property, playerId, score) => { // if you want to do something when a score is revealed
                        if (property === 'total' && playerId != 999) {
                        this.scoreCtrl[playerId].setValue(score);
                        }
                    },
                    }
                );    

                cont = dojo.query("#my-score-sheet")[0]
                const header = document.createElement("h2")
                header.id= "score_header"
                header.textContent = _("Final Scores")
                dojo.place(header, cont, "first")
            }


            if (gamedatas.matches.matches && gamedatas.matches.matches[this.playerId]) {

                    data = gamedatas.matches.matches[this.playerId].matches

                    for (const key in data) {
                        const outerArray = data[key];
                        const patternDiv = dojo.byId(`${key}`);
                        if (!patternDiv) continue;

                        // Remove old listener if it exists
                        if (patternDiv._zoomListener) {
                            patternDiv.removeEventListener("click", patternDiv._zoomListener);
                        }

                        // Define the listener
                        const listener = () => {
                            let delay = 0;
                            outerArray.forEach((innerArray) => {
                                innerArray.forEach((value) => {
                                    const card = dojo.byId(`${value}`);
                                    if (!card) return;

                                    // Zoom in
                                    setTimeout(() => {
                                        dojo.style(card, "transition", "transform 0.3s ease");
                                        dojo.style(card, "transform", "scale(1.1)");
                                    }, delay);

                                    // Zoom out
                                    setTimeout(() => {
                                        dojo.style(card, "transform", "scale(1)");
                                    }, delay + 500);
                                });
                                delay += 500;
                            });
                        };

                        // Store the listener so we can remove it next time
                        patternDiv._zoomListener = listener;
                        patternDiv.addEventListener("click", listener);
                    }


                }




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
            const assis = document.createElement("div")
            assis.classList.add(`${this.types[assistant].class}`, "card")
            assis.setAttribute("assistant", assistant)
            board.appendChild(assis)
            if (assistant == 198) {
                this.unconected = true
            }
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

            case 'nextPlayer':
                if (args.args.matches && args.args.matches[this.playerId]) {

                    data = args.args.matches[this.playerId].matches

                    for (const key in data) {
                        const outerArray = data[key];
                        const patternDiv = dojo.byId(`${key}`);
                        if (!patternDiv) continue;

                        // Remove old listener if it exists
                        if (patternDiv._zoomListener) {
                            patternDiv.removeEventListener("click", patternDiv._zoomListener);
                        }

                        // Define the listener
                        const listener = () => {
                            let delay = 0;
                            outerArray.forEach((innerArray) => {
                                innerArray.forEach((value) => {
                                    const card = dojo.byId(`${value}`);
                                    if (!card) return;

                                    // Zoom in
                                    setTimeout(() => {
                                        dojo.style(card, "transition", "transform 0.3s ease");
                                        dojo.style(card, "transform", "scale(1.1)");
                                    }, delay);

                                    // Zoom out
                                    setTimeout(() => {
                                        dojo.style(card, "transform", "scale(1)");
                                    }, delay + 500);
                                });
                                delay += 500;
                            });
                        };

                        // Store the listener so we can remove it next time
                        patternDiv._zoomListener = listener;
                        patternDiv.addEventListener("click", listener);
                    }


                }
            break;

            case 'helperAction':
                if (args.args.corner) {
                    this.choose_corner(args.args.corner)
                }
                if (args.args.flip_info) {
                    console.log(args.args.flip_info)
                    if (args.args.flip_info.deck_id == null) {
                        this.slide(args.args.flip_info)
                    } else {
                        this.flip_cards(args.args.flip_info);
                    }
                }
                if (args.args.helper) {
                    this.helper(args.args.helper);
                }
                break;
            case 'playerTurn':
                this.gladys = false
                if (args.args.turn_num > 0 && this.isCurrentPlayerActive()) {
                    dojo.style('pass', 'display', 'inline-block');
                }
                this.removePatterns()
                // Clear previous temporary cards
                dojo.query('.temp-card', this.board).forEach(dojo.destroy);
                dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
                dojo.query('.selected, .selectable').forEach(dojo.destroy)

                if (args.args.use_assistant != 0 && this.isCurrentPlayerActive() && !this.unconected) {
                    //TODO set assistant to send request on click to server to return args for specific assistant
                    if (!dojo.byId("assistant_action")) {
                        this.statusBar.addActionButton(
                            _('Assistant'),
                            () => {

                                // Remove card listener + CSS when button is clicked
                                const card = dojo.query(`[assistant=${args.args.use_assistant}]`)[0];
                                if (card && card.boundAssistant) {
                                    card.removeEventListener("click", card.boundAssistant);
                                    delete card.boundAssistant;
                                    card.classList.remove("selectable-card");
                                }

                                // Now send the action
                                this.bgaPerformAction("actAssistantAction", { 
                                    assistant: args.args.use_assistant
                                });
                            },
                            { id: 'assistant_action' }
                        );
                    }

                    dojo.place('assistant_action', 'back', 'before');
                    dojo.style('assistant_action', 'display', 'inline-block')
                    card = dojo.query(`[assistant=${args.args.use_assistant}]`)[0]
                    card.boundAssistant = this.assistantHandler;
                    // this.statusBar.addActionButton(_('Assistant'), () => this.assistant())
                    card.addEventListener("click", this.assistantHandler)
                    setTimeout(() => {
                    card.classList.add("selectable-card")
                    }, 100);
                }

                if (this.isCurrentPlayerActive() && args.args.last_turn) {
                    $(`last_turn`).style.display = "inline-block";
                    $(`last_turn`).innerText = _("Last Turn");
                } else {
                    document.querySelectorAll('.lastTurnMarker')
                .forEach(div => div.style.display = "none");
                }

                
                break;

            case 'chooseAssistant':
                if (!this.isSpectator) {
                    this.setUpAssistants(args)
                }
                break;
            case 'returnBlock':
                if (this.isCurrentPlayerActive() && args.args[0] != 0) {
                    this.setUpReturnLocations(args)
                }
                break;
            case 'postEnd':
                this.removePatterns()
                // Clear previous temporary cards
                dojo.query('.temp-card', this.board).forEach(dojo.destroy);
                dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
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
            case 'playerTurn':
                cards = dojo.query(`[assistant=]`)
                cards.forEach(card => {
                    card.removeEventListener('click', card.boundAssistant);
                    delete card.boundAssistant
                    card.classList.remove("selectable-card")
                })
                break;
            case 'returnBlock':
                this.removePatterns()
                break;
            case 'chooseAssistant':
                this.removePatterns()
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        back: function() {
            this.reset_buttons()
            dojo.byId('confirm_placement') && dojo.style('confirm_placement', 'display', 'none')
            dojo.byId('back') && dojo.style('back', 'display', 'none')
            this.removePatterns()
            // Clear previous temporary cards
            dojo.query('.temp-card', this.board).forEach(dojo.destroy);
            dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
            dojo.byId('confirm_selection') && dojo.destroy('confirm_selection');
            this.updatePageTitle();
        },

        reset_buttons: function() {
            dojo.byId('plan_button') && dojo.style('plan_button', 'display', 'inline-block')
            dojo.byId('choose_button') && dojo.style('choose_button', 'display', 'inline-block')
            dojo.byId('return_button') && dojo.style('return_button', 'display', 'inline-block')
        },
        
    
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName, args );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                 case 'playerTurn':
                    this.statusBar.removeActionButtons()
                    if (this.options == "1" || this.playerCount == 1) {
                        this.statusBar.addActionButton(_('Plan'), () => this.bgaPerformAction("actPlan"), {id:'plan_button'})
                    }
                    this.statusBar.addActionButton(_('Choose'), () => this.bgaPerformAction("actChoose"), {id:'choose_button'})
                    this.statusBar.addActionButton(_('Return'), () => this.bgaPerformAction("actReturn"), {id:'return_button'})
                    this.statusBar.addActionButton(_('Finalize Placement'), () => this.finalizeCardPlacement(), {id: 'confirm_placement', style: 'display:none;'});
                    this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("actBack").then(()=>{
                        this.removePatterns()
                        // Clear previous temporary cards
                        dojo.query('.temp-card', this.board).forEach(dojo.destroy);
                        dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
                    }), {id:'back', color: 'secondary', style: 'display:none;'});
                    dojo.style('back', 'display', 'none');
                    this.statusBar.addActionButton(_('Pass'), () => this.bgaPerformAction("actPass"), {id:'pass', color: 'secondary', style: 'display:none;'});
                    dojo.style('pass', 'display', 'none');
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

            assistant: function(event) {
                if (event.target.boundAssistant) {
                    event.target.removeEventListener('click', event.target.boundAssistant);
                    delete event.target.boundAssistant
                    event.target.classList.remove("selectable-card")
                }
                // Remove the button when a card is clicked
                const btn = dojo.byId("assistant_action");
                if (btn) btn.remove();
                this.bgaPerformAction("actAssistantAction", { 
                        assistant: event.target.getAttribute("assistant")
                    })
            },

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
                let args = {"animation":{}}

                const cards = dojo.query(`#player-table-${this.playerId} .quilt-board .card`)
                let shift = true

                if (this.isShiftEnabled && cards.length > 0 && this.isCurrentPlayerActive()) {

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
                                args.animation[index] = {"card_id":card.id, "target":`player-table-${this.playerId}`, "loc":loc-1}
                                index ++
                            })
                            this.shift_animation(args)
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
                                args.animation[index] = {"card_id":card.id, "target":`player-table-${this.playerId}`, "loc":loc-4}
                                index ++
                            })
                            this.shift_animation(args)
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
                                args.animation[index] = {"card_id":card.id, "target":`player-table-${this.playerId}`, "loc":loc+1}
                                index ++
                            })
                            this.shift_animation(args)
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
                                args.animation[index] = {"card_id":card.id, "target":`player-table-${this.playerId}`, "loc":loc+4}
                                index ++
                            })
                            this.shift_animation(args)
                        }
                    }
                    if (shift) {
                    this.isShiftEnabled = false
                    this.bgaPerformAction("actShiftQuilt", { 
                        shiftDirection: direction
                    }).then(() => {});
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
            

            animateCards: function(resolve, element, delay=0, delete_card = false, player_id = null) {
                    let target
                    const original = document.getElementById(element.card_id) || dojo.query(`[assistant=${element.card_id}]`)[0]
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
                        if (element.loc == "0") {
                            target = dojo.query("#"+element.target + " .patterns")[0]
                            gridElement = dojo.create("div", {className: "temp-pattern", id: "placeholder"}, target)
                            original.setAttribute("location", element.loc)
                            original.setAttribute("type", element.flip)
                            original.style.position = "static"
                        } else {
                            if (element.target == "player-table-"+player_id) {
                                target = dojo.query(`#${element.target} .quilt-board`)[0]
                                gridElement = this.getGridElement(`#${element.target} .quilt-board`, this.locations[element.loc].row-1, this.locations[element.loc].col-1, 4)
                            } else {
                                target = document.getElementById("pattern-board");
                                gridElement = this.getGridElement("#pattern-board", this.locations[element.loc].row-1, this.locations[element.loc].col-1, 4)
                            }
                            
                        }
                        // Enhanced flip animation
                        this.createEnhancedFlipAnimation(resolve, card, original, gridElement, element,target, delay, delete_card, player_id)
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
                            if (delete_card) {
                                original.remove()
                            }
                            resolve()
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
                            if (delete_card) {
                                original.remove()
                            }
                            resolve()
                        });
                    }
            
                    // Set or remove arg attribute
                    if (element.target != "pattern-board") {
                        original.setAttribute("arg", element.target)
                    } else {
                        original.removeAttribute("arg")
                    }
            },
            
            createEnhancedFlipAnimation: function(resolve, card, original, gridElement, element,target, delay, delete_card, player_id) {
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
                        if (element.loc != "0") {
                            if (element.target == "player-table-"+player_id) {
                                target.appendChild(original)
                                original.style.position = "absolute"
                            }
                            original.style.top = this.locations[element.loc].y + "px";
                            original.style.left = this.locations[element.loc].x + "px";
                            original.style.display = "block";
                        } else {
                            target.appendChild(original)
                            original.setAttribute("location", element.loc)
                            original.setAttribute("type", element.flip)
                            original.style.display = ""
                            original.style.position = "static"
                            gridElement.remove()
                        }
                        
                        // Reset transform and clean up
                        card.style.transform = '';
                        this.destroyAnimation(card.id);
                        if (delete_card) {
                            original.remove()
                        }
                        resolve()
                    }, 500);  // Full animation duration
                });
                
                // Start the slide and flip sequence
                slideAnimation.play();
            },
        
       returnCard: function (event) {
        dojo.byId("205") && dojo.style("205", "z-index", 205)
        this.bgaPerformAction("actConfirmReturn", { 
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
            this.bgaPerformAction("actChooseAssistant", { 
                option: value
            }).then(() => {});


        if (event.target.boundSelectAssistant) {
                    event.target.removeEventListener('click', event.target.boundSelectAssistant);
                    delete event.target.boundSelectAssistant
        }

       },


       setUpReturnLocations: function (args) {
        console.log("SETUP RETURN")
        console.log(args)
        dojo.byId("205") && dojo.style("205", "z-index", 0)
        args = args.args
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
            
                const originalCard = document.getElementById(args.card.card_id);
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

       selectPatterns: function (args, sally=false) {
                if (this.isCurrentPlayerActive()) {
                  for (let i = 0; i < 4; i++) {
                    let deck_name = "deck_" + i;
                    if (args[deck_name] != null) {
                        const card = document.getElementById(args[deck_name])
                        card.classList.add("selectable-card")
                        card.boundSelectPlan = this.selectPlan.bind(this, sally);
                        card.addEventListener("click", card.boundSelectPlan)
                    }
                }

                }
       },


       // generated from chat gpt after I gave it a working function for a single separate block
       checkReturnAjacency: function(ids) {
        if (this.unconected) {
            return true;
        }

        // STEP 1: get all non-selected card locations
        const allCards = dojo.query('.card', this.board);
        const remaining = [];

        allCards.forEach(card => {
            const id = parseInt(card.id);
            if (!ids.includes(id)) {
                remaining.push(card.getAttribute("location"));
            }
        });

        // If 0 or 1 cards remain → trivially connected
        if (remaining.length <= 1) return true;

        // STEP 2: BFS from first card
        const visited = new Set();
        const queue = [remaining[0]];

        while (queue.length > 0) {
            const loc = queue.shift();
            if (visited.has(loc)) continue;

            visited.add(loc);

            // neighbors
            const adj = this.getAdjacentLocations(loc);

            adj.forEach(a => {
                // Only consider neighbors that are in remaining
                if (remaining.includes(String(a)) && !visited.has(String(a))) {
                    queue.push(String(a));
                }
            });
        }

    // STEP 3: connected if BFS reached all remaining cards
    return visited.size === remaining.length;
},


       confirmReturnCards: function () {
            const cards = dojo.query(".card.selectable-card.selected", "player-table-" + this.player_id);
            const cardIds = []

            cards.forEach(card => {
                cardIds.push(parseInt(card.id))
            })

            if (!this.checkReturnAjacency(cardIds)) {
                this.showMessage(_("You may not return blocks that will leave part of the quilt unconnected."), "error");
                return;
            }

            this.bgaPerformAction("actReturnBlocks", { 
                cards: JSON.stringify(cardIds)
            }).then(() => {
                dojo.byId('confirm_selection') && dojo.style('confirm_selection', 'display', 'none')
                dojo.byId('back') && dojo.style('back', 'display', 'none')
            }); 

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



       confirmSelection: function(min=2, max=3) {
        let selectedLocations = dojo.query(".selected");
        console.log("Selected cards:", selectedLocations);
        
        // Validate selection (2-3 cards)
        if (selectedLocations.length < min || selectedLocations.length > max) {
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
        
        if (selectedCards.length < min) {
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
        if (!this.areCardsAdjacent(selectedCards.map(card => card.location))&& !this.gladys) {
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

        dojo.query(".card").forEach((card) => {
            card.classList.remove("selectable-card")
            card.removeEventListener("click", card.boundSelectPlan)
        })
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
finalizeCardPlacement: function(billy=false) {
    console.log("Finalizing card placement");
    
    // Validate all temp cards individually
    this.tempCards.forEach(card => this.validateTempCardPlacement(card))
    this.synchronizeValidationState()
    
    // Check if any card is adjacent to existing cards (if not first placement)
    const isFirstPlacement = dojo.query('.card', this.board).length == 0;
    const isAdjacent = isFirstPlacement || this.checkAnyCardAdjacentToExisting();
    
    if (!isAdjacent) {
        if (this.gladys) {
            this.showMessage(_("All selected cards adjacent to an existing card."), "error");
        } else {
            this.showMessage(_("At least one card must be adjacent to an existing card."), "error");
        }
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
    this.sendCardPlacements(placements, billy);
    
    // Hide placement button, show selection button again
    dojo.byId("205") && dojo.style("205", "z-index", 205)
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
        return (sameRow && adjacentCol) || (sameCol && adjacentRow) || this.unconected;
    });
},

// Get location ID from row/col position
getLocationIdFromPosition: function(row, col) {
    // Based on your location mapping
    const baseId = 207; // ID offset
    return baseId + ((row - 1) * 4) + col;
},

// Send placements to server with proper ajaxcall
sendCardPlacements: function(placements, billy) {
    console.log("Sending placements to server:", placements);
    
    // send move to server
    this.bgaPerformAction("actPlaceBlocks", { 
        args: JSON.stringify(placements),// Format the placements for server
        billy: billy,
        gladys: this.gladys
    }).then(() => {
    });
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

    let connected = false
    
    // Check if any temp card is adjacent to an existing card
    for (let i = 0; i < this.tempCards.length; i++) {
        if (this.checkAdjacentToExistingCards(this.tempCards[i])) {
            connected = true;
        } else if (this.gladys) {
            return false;
        }
    }
    
    return connected;
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
    if ((anyInvalid || !this.checkAnyCardAdjacentToExisting()) && !this.unconected) {
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

        selectPlan: function(sally, element) {
            this.bgaPerformAction("actChoosePattern", { 
                card_id: parseInt(element.target.id),
                sally: sally
            }).then(() => {
                this.removePatterns()
                dojo.style('back', 'display', 'none')
                this.reset_buttons()
            });
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

            if (this.gladys) {
                all = [208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,232,233,234,235,236,237,238,239,240,241,242,243]
                all.forEach(v => adjacentLocations.add(v))
            }
        
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
        },

        action: function(event) {
            card = event.target
            this.bgaPerformAction("actTurnCard", {
                    loc: parseInt(card.getAttribute("location"))
                }).then(()=> {
                    card.classList.remove("selectable-card")
                    card.removeEventListener('click', card.boundClick)
                    delete card.boundClick
                    this.removePatterns()
                })
        },

        slide: function(args) {
            console.log(args)
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose where to slide card to be in valid location') : _('${actplayer} must choose where to slide card to be in valid location'), "")

            if (this.isSpectator || !this.isCurrentPlayerActive()) {
                    return
                }

            args.locations.forEach(loc => {
                const pattern_area = document.querySelector(".pattern-board")
                const newElement = dojo.create("div", {
                    className: "possible-card",
                    id: loc
                }, pattern_area);
                newElement.setAttribute("location", loc)
                newElement.style.left = this.locations[loc].x + "px"
                newElement.style.top = this.locations[loc].y + "px"

                newElement.boundClick = (event) => {
                    this.bgaPerformAction("actSlideValidate", {
                        loc: parseInt(event.target.getAttribute("location"))
                    })
                    dojo.query(".possible-card").forEach(loc => {
                            loc.classList.remove("selectable-card")
                            loc.removeEventListener('click', loc.boundClick)
                            delete loc.boundClick
                            loc.remove()
                        })
                    this.removePatterns()
                }
                newElement.addEventListener("click", newElement.boundClick)
            })
            
        },

        flip_cards: function(args) {
            console.log(args)
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose where to turn over card from deck') : _('${actplayer} must choose where to turn over card from deck'), "")

            if (this.isSpectator || !this.isCurrentPlayerActive()) {
                    return
                }
            args.locations.forEach(loc => {
                console.log(loc)
                const card = document.querySelector(`.pattern-board [location="${loc}"]`)
                card.classList.add("selectable-card")
                card.boundClick = (event) => {
                    args.locations.forEach(loc => {
                            const c = document.querySelector(`.pattern-board [location="${loc}"]`)
                            c.classList.remove("selectable-card")
                            c.removeEventListener('click', c.boundClick)
                            delete c.boundClick
                        })
                    c = event.target
                    this.removePatterns()
                    this.bgaPerformAction("actTurnCard", {
                        loc: parseInt(c.getAttribute("location"))
                    })
                }
                card.addEventListener("click", card.boundClick)
            })
            
                const element = document.getElementById("storage-item");
                element.innerHTML = "";

                let card_id = document.getElementById(args.card_id)
                card_id = card_id.getAttribute("type")
            
                const originalCard = document.createElement("div")
                const other_side = this.types[this.types[card_id]["other_side"]]["class"]
                originalCard.classList.add(other_side)
                originalCard.classList.add("card")
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

        choose_corner: function(args) {
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose a starting point for the quilt master') : _('${actplayer} must choose a starting point for the quilt master'), "")
            if (this.isSpectator) {
                return
            }
            this.hide_turn_buttons()
            args.forEach((i) => {
                const pattern_area = document.querySelector(".pattern-board")
                const newElement = dojo.create("div", {
                    className: "possible-card",
                    id: i
                }, pattern_area);
                newElement.style.left = this.locations[i].x + "px"
                newElement.style.top = this.locations[i].y + "px"
                newElement.boundSelectPlan = (element) => {
                    this.bgaPerformAction("actQuiltMaster", {
                    id: parseInt(element.target.id)
                })
                }
                newElement.addEventListener("click", newElement.boundSelectPlan)
            })   
        },
        notif_plQuilt: function(args) {
            console.log(args)
            const cards = dojo.query(".possible-card")
            cards.forEach(card => {
                card.remove()
            })
            const master = dojo.byId("205")
            master.style.left = this.locations[args.loc].x + "px"
            master.style.top = this.locations[args.loc].y + "px"
            master.setAttribute("location", args.loc)
            master.style.display = "block"
        },

        notif_flip_animation: function(args) {
            card = dojo.query(`[assistant=${args.id}]`)[0]

            card.style.transition = 'transform 0.5s';
            card.style.transformStyle = 'preserve-3d';
            card.style.backfaceVisibility = 'hidden';

            card.style.transform = 'rotateY(360deg)';

            setTimeout(() => {

                // Remove old type classes
                card.classList.remove(this.types[args.id].class);

                // Add new type classes
                card.classList.add(this.types[args.other].class);

                // Update type and location attributes
                card.setAttribute("assistant", args.other);
            }, 250);

            setTimeout(() => {
                // Reset transform and clean up
                card.style.transition = '';
                card.style.transform = '';
                }, 500);

        },

        notif_quilt_master: function (args) {
            return new Promise((resolveNotif) => {
                console.log(args)
                this.statusBar.setTitle(_('Quilt Master is moving...'), "");

                let animations = [];
                let delay = 0;

                // 1. Queue animations for deck cards
                Object.values(args.cards.animation).forEach(element => {
                    animations.push(
                        new Promise((resolveCard) => {
                            this.animateCards(resolveCard, element, delay, true);
                        }).then(()=>{
                            this.miniCounters[args.player_id].incValue(1)
                        })
                    );
                    delay += 500;
                });

                // 2. When all deck card animations finish → animate the master
                Promise.all(animations)
                    .then(() => {
                        return new Promise((resolveMaster) => {
                            Object.values(args.moveMaster.animation).forEach(element => {
                                this.animateCards(resolveMaster, element);
                            })
                        });
                    })
                    .then(() => {
                        // 3. FINAL: resolve the global notification promise
                        resolveNotif();
                    });
            });
        },



        helper: function(args) {
            this.statusBar.removeActionButtons()
            if (this.isSpectator || !this.isCurrentPlayerActive()) {
                return
            }
            this.statusBar.addActionButton(_('Allow'), () => this.bgaPerformAction("actMaddieOption", {option: 0}))
            this.statusBar.addActionButton(_('Give Pattern Instead'), () => {
                this.statusBar.removeActionButtons()
                this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("actHelperBack"), {id:'back', color: 'secondary', style: 'display:none;'})

                console.log(args.patterns)
                this.statusBar.setTitle( _('${you} must choose a pattern to give'), "")

                Object.keys(args.patterns).forEach(id => {
                    block = dojo.byId(id)
                    block.classList.add("selectable-card")
                    block.boundMaddie = (event) => {
                        Object.keys(args.patterns).forEach(id => {
                            block = dojo.byId(`${id}`)
                            block.classList.remove("selectable-card")
                            block.removeEventListener('click', block.boundMaddie)
                            delete block.boundMaddie
                        })
                        this.bgaPerformAction("actMaddieOption", {
                            option: event.target.id
                        })
                        
                        
                    }
                    block.addEventListener("click", block.boundMaddie)
                })

            })
            console.log("###HELPER NOTIF")
            console.log(args)
            this.statusBar.setTitle(
                dojo.string.substitute(_('${player_name} is trying to flip your assistant'), {
                    player_name: args.name
                })
            );
        
        
        },

        notif_maddie: function(args) {
            this.statusBar.removeActionButtons()
            Object.values(args).forEach(arg => {
                this.statusBar.addActionButton(arg.player_name, () => this.bgaPerformAction("actMaddie", {
                    tar_player: arg.player_id
                }))
            })
            this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("actBack").then(()=>{
                        this.removePatterns()
                        // Clear previous temporary cards
                        dojo.query('.temp-card', this.board).forEach(dojo.destroy);
                        dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
                    }), {id:'back', color: 'secondary', style: 'display:none;'});
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose a player to flip their assistant') : _('${actplayer} must choose a player to flip their assistant'), "")
        },

        notif_fix_assistants: function(args) {
            Object.values(args).forEach(arg =>{
                const old = dojo.query(`#player-table-${arg.player_id} [assistant]`)[0]
                const assis = document.createElement("div")
                assis.classList.add(`${this.types[arg.assistant].class}`, "card")
                assis.setAttribute("assistant", arg.assistant)
                dojo.place(assis, old, 'before')
                old.remove()
                if (arg.assistant == 198 && this.playerId == arg.player_id) {
                    this.unconected = true
                }
            })
        },

        notif_travis: function(args) {
            this.statusBar.removeActionButtons()
            Object.values(args).forEach(arg => {
                this.statusBar.addActionButton(arg.player_name, () => this.bgaPerformAction("actTravis", {
                    tar_player: arg.player_id
                }))
            })
            this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("actBack").then(()=>{
                        this.removePatterns()
                        // Clear previous temporary cards
                        dojo.query('.temp-card', this.board).forEach(dojo.destroy);
                        dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
                    }), {id:'back', color: 'secondary', style: 'display:none;'});
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose which other player to switch assistants with') : _('${actplayer} must choose which other player to switch assistants with'), "")
        },

        notif_gladys: function(args) {
            this.gladys = true
            this.hide_turn_buttons()
            dojo.style('choose_button', 'display', 'inline-block')
            dojo.style('return_button', 'display', 'inline-block')
            dojo.style('back', 'display', 'inline-block')
            
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose which action to use with assistant') : _('${actplayer} must choose action'), "")

        },


        turnOver: function() {
            let block = dojo.query("#pattern-board .selected, #player-tables .patterns .selected")
            let loc = dojo.query("#player-tables .quilt-board .selected")

            console.log(block)
            if (block.length > 0 && loc.length > 0) {
                this.bgaPerformAction("actSam", {
                        pattern: parseInt(block[0].id),
                        loc: parseInt(loc[0].getAttribute("location"))
            }).then(() => {
                dojo.query('.selected, .selectable-card').forEach(el => {
                    dojo.removeClass(el, 'selected');
                    dojo.removeClass(el, 'selectable-card');
                });
            });
            }
        },

        notif_sam: function(args) {
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must select a pattern and a place to turn it over') : _('${actplayer} must select a pattern and a place to turn it over'), "")
            patterns = args.patts
            board = args.items

            console.log(args)

            board.forEach(block => {
                if (block == null) {
                    return
                }
                block = dojo.query(`#player-table-${this.playerId} [location=${block}]`)[0]
                block.classList.add("selectable-card")
                block.boundAssistant = (event) => {
                    board.forEach(block => {
                        if (block == null) {
                            return
                        }
                        block = dojo.query(`#player-table-${this.playerId} [location=${block}]`)[0];
                        block.classList.remove("selectable-card")
                        block.removeEventListener('click', block.boundAssistant)
                        delete block.boundAssistant
                    })
                    event.target.classList.add("selected", "selectable-card")
                    this.turnOver()
                }
                block.addEventListener("click", block.boundAssistant)
            })
            Object.values(patterns).forEach(block => {
                if (block == null) {
                    return
                }
                block = dojo.byId(block)
                block.classList.add("selectable-card")
                block.boundAssistant = (event) => {
                    Object.values(patterns).forEach(block => {
                        if (block == null) {
                            return
                        }
                        block = dojo.byId(`${block}`)
                        block.classList.remove("selectable-card")
                        block.removeEventListener('click', block.boundAssistant)
                        delete block.boundAssistant
                    })
                    event.target.classList.add("selected", "selectable-card")
                    this.turnOver()
                }
                block.addEventListener("click", block.boundAssistant)
            })
            dojo.style('back', 'display', 'inline-block')
            this.hide_turn_buttons()
        },
        
        flip: function(event) {
            this.bgaPerformAction("actGranny", { 
                        id: event.target.id
            }).then(() => {})
        },

        notif_granny: function(args) {
            Object.keys(args).forEach(block => {
                block = dojo.byId(`${block}`)
                block.classList.add("selectable-card")
                block.boundGranny = (event) => {
                    Object.keys(args).forEach(block => {
                        block = dojo.byId(`${block}`)
                        block.classList.remove("selectable-card")
                        block.removeEventListener('click', block.boundGranny)
                        delete block.boundGranny
                    })
                    event.target.classList.add("selected", "selectable-card")
                    this.flip(event)
                }
                block.addEventListener("click", block.boundGranny)
            })
            dojo.style('back', 'display', 'inline-block')
            this.hide_turn_buttons()
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must select a quilt block to flip to a pattern') : _('${actplayer} must a quilt block to flip to a pattern'), "")
        },

        swap: function() {
            let block = dojo.query("#pattern-board .selected")
            let player_board = dojo.query("#player-tables .selected")

            if (block.length > 0 && player_board.length > 0) {
                this.bgaPerformAction("actSandra", { 
                        block: parseInt(block[0].id),
                        player_board: parseInt(player_board[0].id)
            }).then(() => {});
            }
        },

        notif_sandra: function(args) {
            blocks = args.blocks
            board = args.board

            Object.keys(board).forEach(block => {
                block = dojo.byId(`${block}`)
                block.classList.add("selectable-card")
                block.boundSandra = (event) => {
                    Object.keys(board).forEach(block => {
                        block = dojo.byId(`${block}`)
                        block.classList.remove("selectable-card")
                        block.removeEventListener('click', block.boundSandra)
                        delete block.boundSandra
                    })
                    event.target.classList.add("selected", "selectable-card")
                    this.swap()
                }
                block.addEventListener("click", block.boundSandra)
            })
            Object.keys(blocks).forEach(block => {
                block = dojo.byId(`${block}`)
                block.classList.add("selectable-card")
                block.boundSandra = (event) => {
                    Object.keys(blocks).forEach(block => {
                        block = dojo.byId(`${block}`)
                        block.classList.remove("selectable-card")
                        block.removeEventListener('click', block.boundSandra)
                        delete block.boundSandra
                    })
                    event.target.classList.add("selected", "selectable-card")
                    this.swap()
                }
                block.addEventListener("click", block.boundSandra)
            })
            dojo.style('back', 'display', 'inline-block')
            this.hide_turn_buttons()
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must select two blocks to swap') : _('${actplayer} must select blocks to swap'), "")
        },

        send_trade_request: function(id, player) {
            this.bgaPerformAction("acttim", { 
                        player_id: parseInt(player),
                        card_id: parseInt(id)
            })
        },

        trade_pattern: function(event, players) {
            card_id = event.target.id
            dojo.query('.selectable-card').forEach(card => {card.classList.remove("selectable-card")})
            event.target.classList.add("selectable-card", "selected")
            if (this.playerCount == 1) {
                this.send_trade_request(card_id, 1234)
            }
            this.statusBar.removeActionButtons()
            Object.values(players).forEach(player => {
                this.statusBar.addActionButton(player.player_name, () => this.send_trade_request(card_id, player.player_id))
            })
            this.statusBar.addActionButton(_('Back'), () => this.bgaPerformAction("actBack").then(()=>{
                        this.removePatterns()
                        // Clear previous temporary cards
                        dojo.query('.temp-card', this.board).forEach(dojo.destroy);
                        dojo.query('.card-group-controls', this.board).forEach(dojo.destroy);
                    }), {id:'back', color: 'secondary', style: 'display:none;'});
        },

        notif_tim: function(args) {
            Object.keys(args.cards).forEach(id => {
                pattern = dojo.byId(`${id}`)
                if(pattern) {
                    pattern.classList.add("selectable-card")
                    pattern.boundTim = (event) => this.trade_pattern(event, args.players)
                    pattern.addEventListener("click", pattern.boundTim)
                }
            })
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must select a pattern to trade for a turn') : _('${actplayer} must select a pattern to trade'), "")
            this.hide_turn_buttons()
            dojo.style('back', 'display', 'inline-block')
            console.log(args)
        },

        notif_sally: function(args) {
            this.selectPatterns(args, sally=true)
            this.hide_turn_buttons()
            dojo.style('back', 'display', 'inline-block')
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose a pattern') : _('${actplayer} must choose a pattern'), "")
        },

        notif_billy: function(args) {
            if (this.isCurrentPlayerActive()) {
                this.selectableBlocks = args
                args.forEach((item) => {const card = document.getElementById(item.id)
                    card.classList.add("selectable-card")
                    card.boundSelectPlan = (event) => this.chooseBlocks(event, 4, 1, document.querySelector(".pattern-board"))
                    card.addEventListener("click", card.boundSelectPlan)})
                this.hide_turn_buttons()
                this.statusBar.addActionButton(_('Confirm Selection'), () => this.confirmSelection(1,4), {id: 'confirm_selection'})
                dojo.style('confirm_selection', 'display', 'none')
                dojo.style('back', 'display', 'inline-block')
                dojo.place('confirm_selection', 'back', 'before');
                dojo.query('#confirm_placement').remove()
                this.statusBar.addActionButton(_('Finalize Placement'), () => this.finalizeCardPlacement(billy=true), {id: 'confirm_placement', style: 'display:none;'});
                dojo.place('confirm_placement', 'back', 'before');
            }
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose 1-4 quilt blocks') : _('${actplayer} must choose quilt blocks'), "")
        },

        hide_turn_buttons: function() {
            dojo.byId('plan_button') && dojo.style('plan_button', 'display', 'none')
            dojo.byId('choose_button') && dojo.style('choose_button', 'display', 'none')
            dojo.byId('choose_button') && dojo.style('return_button', 'display', 'none')
            dojo.byId('assistant_action') && dojo.style('assistant_action', 'display', 'none')
            dojo.byId('pass') && dojo.style('pass', 'display', 'none')
        },

        notif_return_args: function(args) {
            if (this.isCurrentPlayerActive()) {
                this.selectableBlocks = args
                args.forEach((item) => {const card = document.getElementById(item.id)
                    card.classList.add("selectable-card")
                    card.boundSelectPlan = (event) => this.chooseBlocks(event, 4, 1, dojo.query(".quilt-board", "player-table-" + this.player_id)[0])
                    card.addEventListener("click", card.boundSelectPlan)})
                
                this.hide_turn_buttons()
                this.statusBar.addActionButton(_('Confirm Selection'), () => this.confirmReturnCards(), {id: 'confirm_selection'})
                dojo.style('confirm_selection', 'display', 'none')
                dojo.style('back', 'display', 'inline-block')
                dojo.place('confirm_selection', 'back', 'before');
            }
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} may select 1-4 tiles to return') : _('${actplayer} may return tiles'), "")
        },

        notif_choose_args: function(args) {
            if (this.isCurrentPlayerActive()) {
                dojo.byId("205") && dojo.style("205", "z-index", 0)
                this.selectableBlocks = args
                args.forEach((item) => {const card = document.getElementById(item.id)
                    card.classList.add("selectable-card")
                    card.boundSelectPlan = (event) => this.chooseBlocks(event, 3, 2, document.querySelector(".pattern-board"))
                    card.addEventListener("click", card.boundSelectPlan)})
                this.hide_turn_buttons()
                this.statusBar.addActionButton(_('Confirm Selection'), () => this.confirmSelection(), {id: 'confirm_selection'})
                dojo.style('confirm_selection', 'display', 'none')
                dojo.style('back', 'display', 'inline-block')
                dojo.place('confirm_selection', 'back', 'before');
            }
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose quilt blocks') : _('${actplayer} must choose quilt blocks'), "")
        },

        notif_plan_args: function(args) {
            this.selectPatterns(args)
            this.hide_turn_buttons()
            dojo.style('back', 'display', 'inline-block')
            this.statusBar.setTitle(this.isCurrentPlayerActive() ? _('${you} must choose a pattern') : _('${actplayer} must choose a pattern'), "")
        },

        notif_endScores(args) {
            // console.log("endScores")
            // console.log(args)
            this.statusBar.setTitle( _('Calculating End Scores...'), "")
            if (this.options != 2) {
                return this.scoreSheet.setScores(args.endScores, {
                    startBy: this.playerId
                });
        }
        },

        notif_animation(args) {
            return new Promise((resolveNotif) => {
                this.statusBar.setTitle(_('Moving cards...'), "");
                let animations = [];
                let delay = 0;

                // 1. Queue animations for deck cards
                Object.values(args.animation).forEach(element => {
                    animations.push(
                        new Promise((resolveCard) => {
                            if (element.target == 1) {
                                console.log(element)
                                this.fadeOutAndDestroy(String(element.card_id), 500,0)
                                resolveCard()
                            } else {
                                this.animateCards(resolveCard, element, delay, false, args.player_id)
                            }
                        })
                    );
                    delay += 200;
                });

                // 2. When all deck card animations finish → animate the master
                Promise.all(animations)
                    .then(() => {
                        // 3. FINAL: resolve the global notification promise
                        resolveNotif();
                    });
            });
        },
        shift_animation(args) {
            return new Promise((resolveNotif) => {
                let animations = [];
                let delay = 0;

                // 1. Queue animations for deck cards
                Object.values(args.animation).forEach(element => {
                    animations.push(
                        new Promise((resolveCard) => {
                            this.animateCards(resolveCard, element, delay);
                        })
                    );
                });

                // 2. When all deck card animations finish → animate the master
                Promise.all(animations)
                    .then(() => {
                        // 3. FINAL: resolve the global notification promise
                        resolveNotif();
                    });
            });
        },

        notif_plan: function(args) {

        },
        notif_chooseTiles: function(args) {
            // return new Promise((resolveNotif) => {
            //     let animations = [];
            //     let delay = 0;

            //     // 1. Queue animations for deck cards
            //     Object.values(args.animation).forEach(element => {
            //         animations.push(
            //             new Promise((resolveCard) => {
            //                 this.animateCards(resolveCard, element, delay);
            //             })
            //         );
            //         delay += 0;
            //     });

            //     // 2. When all deck card animations finish → animate the master
            //     Promise.all(animations)
            //         .then(() => {
            //             // 3. FINAL: resolve the global notification promise
            //             resolveNotif();
            //         })
            // })
        },

        notif_return: function(args) {

        },

        notif_assistant: function(args) {
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

    //
    // PASS 1 — Replace JSON groups
    //
    const json_regex = /\[(\{.*?\})\]/g;  // finds any [ { ... } ] group
    const json_matches = [...log_entry.matchAll(json_regex)];

    for (let match of json_matches) {
        try {
            const card_data = JSON.parse(match[0]);
            const card_html = this.getHTMLGroupForLog(card_data, 'cards');
            log_entry = log_entry.replace(match[0], card_html);
        } catch (e) {
            console.error("JSON parse error:", e, match[0]);
        }
    }

    //
    // PASS 2 — Replace single cards like [CardName(193)]
    //
    const card_regex = /\[(.+?)\((\d+)\)\]/g;
    const card_matches = [...log_entry.matchAll(card_regex)];

    for (let card of card_matches) {
        const full = card[0];
        const typeArg = card[2];
        const card_span = this.getHTMLForLog(typeArg, 'card');
        log_entry = log_entry.replace(full, card_span);
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
