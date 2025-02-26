<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Quiltable implementation : © Sam Richardson samedr16@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * Quiltable game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: $this->checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!


$machinestates = [

    // The initial state. Please do not modify.

    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => ["" => 2]
    ),

    // Note: ID=2 => your first state

    2 => [
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must choose an action'),
        "descriptionmyturn" => clienttranslate('${you} must choose an action'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "possibleactions" => [
            // these actions are called from the front with bgaPerformAction, and matched to the function on the game.php file
            "plan", "choose", "return"],
        "transitions" => ["plan" => 5, "choose" => 6, "return" => 7]
    ],

    3 => [
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,
        "transitions" => ["endGame" => 99, "nextPlayer" => 2]
    ],

    4 => [
        "name" => "playerTurn2",
        "description" => clienttranslate('${actplayer} must choose a second action or pass'),
        "descriptionmyturn" => clienttranslate('${you} must choose a second action or pass'),
        "type" => "activeplayer",
        "possibleactions" => ["plan", "choose", "return", "actPass"],
        "transitions" => ["plan" => 8, "choose" => 9, "return" => 10, "pass" => 3]
    ],

    5 => [
        "name" => "plan",
        "description" => clienttranslate('${actplayer} must choose a pattern'),
        "descriptionmyturn" => clienttranslate('${you} must choose a pattern'),
        "type" => "activeplayer",
        "args" => "argPlan",
        "possibleactions" => ["choosePattern", "back"],
        "transitions" => ["back" => 2, "nextTurn" => 4]
    ],
    6 => [
        "name" => "choose",
        "description" => clienttranslate('${actplayer} must choose quilt blocks'),
        "descriptionmyturn" => clienttranslate('${you} must choose quilt blocks'),
        "type" => "activeplayer",
        "args" => "argChoose",
        "possibleactions" => ["chooseBlock", "back"],
        "transitions" => ["back" => 2, "nextTurn" => 4]
    ],
    7 => [
        "name" => "return",
        "description" => clienttranslate('${actplayer} must return 1-4 tiles'),
        "descriptionmyturn" => clienttranslate('${you} must return 1-4 tiles'),
        "type" => "activeplayer",
        "possibleactions" => ["returnTile", "back"],
        "transitions" => ["back" => 2, "nextTurn" => 4]
    ],
    8 => [
        "name" => "plan2",
        "description" => clienttranslate('${actplayer} must choose a pattern'),
        "descriptionmyturn" => clienttranslate('${you} must choose a pattern'),
        "type" => "activeplayer",
        "args" => "argPlan",
        "possibleactions" => ["choosePattern", "back"],
        "transitions" => ["back" => 4, "nextTurn" => 3]
    ],
    9 => [
        "name" => "choose2",
        "description" => clienttranslate('${actplayer} must choose quilt blocks'),
        "descriptionmyturn" => clienttranslate('${you} must choose quilt blocks'),
        "type" => "activeplayer",
        "args" => "argChoose",
        "possibleactions" => ["chooseBlock", "back"],
        "transitions" => ["back" => 4, "nextTurn" => 3]
    ],
    10 => [
        "name" => "return2",
        "description" => clienttranslate('${actplayer} must return 1-4 tiles'),
        "descriptionmyturn" => clienttranslate('${you} must return 1-4 tiles'),
        "type" => "activeplayer",
        "possibleactions" => ["returnTile", "back"],
        "transitions" => ["back" => 4, "nextTurn" => 3]
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ],

];



