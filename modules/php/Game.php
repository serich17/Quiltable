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
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */
declare(strict_types=1);

namespace Bga\Games\Quiltable;

use function PHPSTORM_META\type;

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");

class Game extends \Table
{
    private static array $CARD_TYPES;
    protected $cards;

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();
        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
        

        $this->initGameStateLabels([
            "my_first_global_variable" => 10,
            "my_second_global_variable" => 11,
            "return_block_index" => 13,
            "solo_options" => 100,
            "game_variants" => 101,
            "patches" => 102,
            "symmetry" => 103,
            "quilting_assistants" => 104
        ]);   

        

        
        

        self::$CARD_TYPES = [
            1 => [
                "card_name" => clienttranslate('Troll'), // ...
            ],
            2 => [
                "card_name" => clienttranslate('Goblin'), // ...
            ],
            // ...
        ];

        /* example of notification decorator.
        // automatically complete notification args when needed
        $this->notify->addDecorator(function(string $message, array $args) {
            if (isset($args['player_id']) && !isset($args['player_name']) && str_contains($message, '${player_name}')) {
                $args['player_name'] = $this->getPlayerNameById($args['player_id']);
            }
        
            if (isset($args['card_id']) && !isset($args['card_name']) && str_contains($message, '${card_name}')) {
                $args['card_name'] = self::$CARD_TYPES[$args['card_id']]['card_name'];
                $args['i18n'][] = ['card_name'];
            }
            
            return $args;
        });*/
    }

    /**
     * Player action, example content.
     *
     * In this scenario, each time a player plays a card, this method will be called. This method is called directly
     * by the action trigger on the front side with `bgaPerformAction`.
     *
     * @throws BgaUserException
     */
    function chooseAssistant($player_id, $option) {
        $options = $this->argChooseAssistant();
        if (in_array($option, $options[$player_id])) {
            $this->DbQuery("UPDATE player SET assistant = $option WHERE player_id = $player_id");
        }
    // Save choice in DB
        

        // Notify table
        // self::notifyAllPlayers("optionChosen", clienttranslate('${player_name} chose an option'), array(
        //     "player_id" => $player_id,
        //     "player_name" => $this->getPlayerNameById($player_id),
        //     "option" => $option
        // ));

        // Mark this player as done
        $this->activeNextPlayer();
        $this->gamestate->setPlayerNonMultiactive($player_id, "next");

        // If all players are done, notify players
        if (!$this->gamestate->isMultiactiveState() && ($this->getGameStateValue("game_variants") == 1 && $this->getGameStateValue("quilting_assistants") == 1)) {
            $players = $this->getCollectionFromDB("SELECT player_id, player_no, assistant FROM player ORDER BY player_no ASC");
            foreach($players as $playerId => $info) {
                $card_arg = $info["assistant"];
                $card_name = $this->quilt_cards[$card_arg]["name"];
                $this->notify->all("assistant", clienttranslate('${player_name} chooses [${card_name}(${card_arg})] as assistant'),
                array(
                    "player_id" => $playerId,
                    "card_arg" => $card_arg,
                    "card_name" => $card_name,
                    "player_name" => $this->getPlayerNameById($playerId)
                ));
                }
            }
            
    }

    public function actPlayCard(int $card_id): void
    {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // check input values
        $args = $this->argPlayerTurn();
        $playableCardsIds = $args['playableCardsIds'];
        if (!in_array($card_id, $playableCardsIds)) {
            throw new \BgaUserException('Invalid card choice');
        }

        // Add your game logic to play a card here.
        $card_name = self::$CARD_TYPES[$card_id]['card_name'];

        // Notify all players about the card played.
        $this->notify->all("cardPlayed", clienttranslate('${player_name} plays ${card_name}'), [
            "player_id" => $player_id,
            "player_name" => $this->getActivePlayerName(), // remove this line if you uncomment notification decorator
            "card_name" => $card_name, // remove this line if you uncomment notification decorator
            "card_id" => $card_id,
            "i18n" => ['card_name'], // remove this line if you uncomment notification decorator
        ]);

        // at the end of the action, move to the next state
        $this->gamestate->nextState("playCard");
    }

    public function actPass(): void
    {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // Notify all players about the choice to pass.
        $this->notify->all("pass", clienttranslate('${player_name} passes second action'), [
            "player_id" => $player_id,
            "player_name" => $this->getActivePlayerName(), // remove this line if you uncomment notification decorator
        ]);

        $this->DbQuery("DELETE FROM animation");

        // at the end of the action, move to the next state
        $this->gamestate->nextState("pass");
    }

    public function plan(): void {
        $this->checkAction("plan");
        if (!$this->checkPlanAvailablility()) {
            throw new \BgaUserException('No pattern cards left in deck');
        }

        $this->DbQuery("DELETE FROM animation");
        $this->gamestate->nextState("plan");
    }
    public function choose() {
        $this->checkAction("choose");
        $this->DbQuery("DELETE FROM animation");
        $this->gamestate->nextState("choose");
    }
    public function return() {
        $this->checkAction("return");
        $this->DbQuery("DELETE FROM animation");

        if (count($this->cards->getCardsOfTypeInLocation("back", null, $this->getCurrentPlayerId(), null)) < 1) {
            throw new \BgaUserException('You don\'t have cards to return');
        }
        if (!$this->checkReturnAvailablility()) {
            throw new \BgaUserException('There aren\'t any spots available to return tiles');
        }

        $this->gamestate->nextState("return");
    }
    public function back() {
        $this->checkAction("back");
        $this->gamestate->nextState("back");
    }

    public function choosePattern(int $card_id) {
        if(!$this->checkIfTopCard($card_id)) {                                
            // Throw an exception to stop further processing
            throw new \BgaUserException('Invalid card choice');
        }

        $this->cards->moveCard($card_id, $this->getActivePlayerId());

        $this->notify->all("plan", clienttranslate('${player_name} draws pattern card [${card_name}(${card_arg})]'),
                array(
                    "player_id" => $this->getActivePlayerId(),
                    "card_arg" => $this->cards->getCard($card_id)["type_arg"],
                    "card_name" => "pattern card",
                    "player_name" => $this->getPlayerNameById($this->getActivePlayerId())
                ));

        $target = "player-table-" . $this->getActivePlayerId();

        $this->notify->all("animation", "", ["args"=>["animation"=>[$card_id => ["card_id"=>$card_id, "target"=>$target, "loc"=>0, "flip"=>0]]]]);

        
        $this->refillPatternArea();

        $this->gamestate->nextState("nextTurn");
    }

    public function placeBlocks($args) {
        $this->checkAction("placeBlocks");
        $player_id = (int)$this->getActivePlayerId();
        
        if (!$this->validatePlayerCards($args)) {
            throw new \BgaUserException('Invalid card placement');
        }

        
        $animation = [];
        $animation["animation"] = [];

        foreach ($args as $arg) {
            $this->cards->moveCard($arg["cardId"], $player_id, $arg["locationId"]);

            $card_id = $arg["cardId"];
            $loc = $arg["locationId"];
            $target = "player-table-" . $this->getActivePlayerId();
            
            $animation["animation"][$card_id] = ["card_id"=>$card_id, "target"=>$target, "loc"=>$loc, "flip"=>0];

            $this->DbQuery("INSERT INTO animation (card_id, target, loc) VALUES ($card_id, '$target', $loc)");
        }
        // $this->notify->all("animation", "", ["args"=>$animation]);

        $card_data = array();
        foreach ($args as $tile) {
            $card = $this->cards->getCard($tile['cardId'])["type_arg"];
            $location = $this->cards->getCard($tile['cardId'])["location_arg"];
            $card_data[] = array(
                "type_arg" => $card,
                "location_arg" => $location,
                "row" => $this->quilt_cards[$location]['row'],
                "col" => $this->quilt_cards[$location]['col']
            );
        }

        $this->notify->All("chooseTiles",
            clienttranslate('${player_name} adds ${card_arg} to quilt'),
            [
                "player_name" => $this->getActivePlayerName(),
                "card_arg" => json_encode($card_data) // This will be substituted as a string
            ]);



        $this->refillPatternArea();

        $this->gamestate->nextState("nextTurn");

    }

    public function returnBlocks($cards) {
        $this->checkAction("returnBlocks");
        $player_id = (int)$this->getActivePlayerId();
    
        // Clear previous entries (if you want to allow fresh selection)
        self::DbQuery("DELETE FROM return_blocks");
    
        foreach ($cards as $card_id) {
            $card = $this->cards->getCard($card_id);
            if ($card && $card["location"] == $player_id) {
                self::DbQuery("INSERT INTO return_blocks (card_id, location) VALUES ({$card['id']}, '{$card['location']}')");
            } else {
                throw new \BgaUserException('One or more selected cards don\'t exist');
            }
        }
    
        // Reset the internal pointer to the first card
        $this->setGameStateValue("return_block_index", 0);
    
        $this->gamestate->nextState("returnBlock");
    }
    
    
    public function confirmReturn($loc) {
        $this->checkAction("confirmReturn");
    
        if (!($loc > 223 && $loc < 244)) {
            throw new \BgaUserException('Invalid placement');
        }

        if (!$this->checkReturnAdjacency($loc)) {
            throw new \BgaUserException('Must be adjacent to other cards');
        }
    
        $player_id = (int)$this->getActivePlayerId();
        $index = $this->getGameStateValue("return_block_index");
    
        $tile = self::getObjectFromDB("SELECT * FROM return_blocks ORDER BY id ASC LIMIT 1 OFFSET $index");
    
        if (!$tile) {
            throw new \BgaUserException('No tile to return');
        }
        if ($this->cards->countCardInLocation("pattern_area", $loc) > 0) {
            throw new \BgaUserException('A card is in this spot already');
        }
    
        // Move the card to the new location
        $this->cards->moveCard($tile['card_id'], "pattern_area", $loc);

        $this->notify->all("return", clienttranslate('${player_name} returns [${card_name}(${card_arg})]'),
                array(
                    "player_id" => $player_id,
                    "card_arg" => $this->cards->getCard($tile['card_id'])["type_arg"],
                    "card_name" => "patch card",
                    "player_name" => $this->getPlayerNameById($player_id)
                ));


        $card_id = $tile["card_id"];
        $target = "pattern-board";
        $this->DbQuery("DELETE FROM animation");
        $this->DbQuery("INSERT INTO animation (card_id, target, loc) VALUES ($card_id, '$target', $loc)");
        
        $this->gamestate->nextState("checkReturn");
        
    }


    public function shiftQuilt($direction) {
        $left = [208, 212, 216, 220];
        $top = [208, 209, 210, 211];
        $right = [211, 215, 219, 223];
        $bottom = [220, 221, 222, 223];
        $shift = true;
        $allCards = $this->cards->getCardsOfTypeInLocation("back", null, $this->getCurrentPlayerId(), null);
        $animation = [];

        if ($direction == "left") {
            foreach ($left as $loc) {
                if($this->cards->countCardInLocation( $this->getCurrentPlayerId(),  $loc) > 0) {
                    $shift = false;
                }
            }
            if ($shift) {
                foreach($allCards as $index => $card) {
                    $this->cards->moveCard($card['id'], $this->getCurrentPlayerId(), intval($card['location_arg'])-1);
                    $animation[] = ["card_id" => $card["id"], "target" => "player-table-".$this->getCurrentPlayerId(), "loc" => intval($card['location_arg'])-1];
                }
            }

        } else if ($direction == "up") {
            foreach ($top as $loc) {
                if($this->cards->countCardInLocation( $this->getCurrentPlayerId(),  $loc) > 0) {
                    $shift = false;
                }
            }
            if ($shift) {
                foreach($allCards as $index => $card) {
                    $this->cards->moveCard($card['id'], $this->getCurrentPlayerId(), intval($card['location_arg'])-4);
                    $animation[] = ["card_id" => $card["id"], "target" => "player-table-".$this->getCurrentPlayerId(), "loc" => intval($card['location_arg'])-4];
                }
            }
        } else if ($direction == "right") {
            foreach ($right as $loc) {
                if($this->cards->countCardInLocation( $this->getCurrentPlayerId(),  $loc) > 0) {
                    $shift = false;
                }
            }
            if ($shift) {
                foreach($allCards as $index => $card) {
                    $this->cards->moveCard($card['id'], $this->getCurrentPlayerId(), intval($card['location_arg'])+1);
                    $animation[] = ["card_id" => $card["id"], "target" => "player-table-".$this->getCurrentPlayerId(), "loc" => intval($card['location_arg'])+1];
                }
            }
        } else if ($direction == "down") {
            foreach ($bottom as $loc) {
                if($this->cards->countCardInLocation( $this->getCurrentPlayerId(),  $loc) > 0) {
                    $shift = false;
                }
            }
            if ($shift) {
                foreach($allCards as $index => $card) {
                    $this->cards->moveCard($card['id'], $this->getCurrentPlayerId(), intval($card['location_arg'])+4);
                    $animation[] = ["card_id" => $card["id"], "target" => "player-table-".$this->getCurrentPlayerId(), "loc" => intval($card['location_arg'])+4];
                }
            }
        }
        # Real args
        $this->notify->all("shift", "", $this->cards->getCardsOfTypeInLocation("back", null, $this->getCurrentPlayerId(), null));
        $this->notify->all("showPoints", "", $this->calculatePoints());

    }
    
    

    /**
     * Game state arguments, example content.
     *
     * This method returns some additional information that is very specific to the `playerTurn` game state.
     *
     * @return array
     * @see ./states.inc.php
     */
    public function argPlayerTurn(): array
    {
        // Get some values from the current game situation from the database.

        return [
            "playableCardsIds" => [1, 2],
        ];
    }

    public function argChooseAssistant() {
        $players = $this->loadPlayersBasicInfos();
        $player_choices = [];
        $activePlayerIds = $this->gamestate->getActivePlayerList();
        foreach ($players as $player_id => $info) {
            $first_choice = strval($this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id"));
            $second_choice = strval($this->quilt_cards[$first_choice]["other_side"]);
            $chosen = '0';
            if (!in_array($player_id, $activePlayerIds)) {
                $chosen = $first_choice;
            }
            $player_choices[$player_id] = [
                $first_choice,
                $second_choice,
                $chosen
            ];
        }

        return $player_choices;
        
    }


    public function argPlan() {
            $top_cards = [];
            
            for ($i = 0; $i < 4; $i++) {
                $deck_name = "deck_{$i}";
        
                // Get the top card from this deck (smallest location_order is on top)
                $cards = $this->cards->getCardsInLocation($deck_name, null, 'card_id ASC');
        
                // Store the top card ID if the deck is not empty
                $top_cards[$deck_name] = !empty($cards) ? reset($cards)['id'] : null;
            }
            return $top_cards; // Example output: ['deck_0' => 5, 'deck_1' => 12, 'deck_2' => 8, 'deck_3' => null]
    }

    public function argChoose() {
        return array_values($this->cards->getCardsInLocation("pattern_area"));
    }
    
    public function argReturn() {
        return array_values($this->cards->getCardsOfTypeInLocation("back", null, (string)$this->getActivePlayerId(), null));
    }

    public function argReturnTile() {
        $player_id = (int)$this->getActivePlayerId();
        $index = $this->getGameStateValue("return_block_index");

        $cards = [];
        $cards["card"] = self::getObjectFromDB("SELECT card_id, location FROM return_blocks ORDER BY id ASC LIMIT 1 OFFSET $index");
    
        return array_merge($cards, $this->argAnimation());
    }

    public function argAnimation() {
        $cards = [];
        $cards["animation"] = $this->getCollectionFromDB("SELECT card_id, target, loc, flip FROM animation");
        return $cards;
    }

    public function stPostEnd() {
        $this->notify->all("endScores", "",[ 'endScores' => $this->calculatePoints() ]);
        $this->gamestate->nextState("endGame");
    }


    
    
    
    

    /**
     * Compute and return the current game progression.
     *
     * The number returned must be an integer between 0 and 100.
     *
     * This method is called each time we are in a game state with the "updateGameProgression" property set to true.
     *
     * @return int
     * @see ./states.inc.php
     */
    public function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }

    /**
     * Game state action, example content.
     *
     * The action method of state `nextPlayer` is called everytime the current game state is set to `nextPlayer`.
     */
    public function stNextPlayer(): void {
        $this->refillPatternArea();

        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // Give some extra time to the active player when he completed an action
        $this->giveExtraTime($player_id);

        // Check end conditions and trigger last turns for other players
        $this->checkEndConditions($player_id);

        $nextPlayer = $this->getNextPlayer();
        $endTriggered = $this->getUniqueValueFromDB("SELECT endTriggered FROM player WHERE player_id = $nextPlayer");
        // add points to db
        if ($endTriggered == 1) {
            
            foreach($this->calculatePoints() as $id => $data) {
                $total = 0;
                foreach($data as $source => $point) {
                    if ($point == "N/A") {
                        continue;
                    }
                    $total += $point;
                }
                $this->DbQuery("UPDATE player SET player_score = $total WHERE player_id = $id");
            }

            $this->activeNextPlayer();
            $this->gamestate->nextState("postEnd");
        } else {
            $this->activeNextPlayer();
            // Go to another gamestate
            // Here, we would detect if the game is over, and in this case use "endGame" transition instead 
            $this->gamestate->nextState("nextPlayer");
        }
    }
    public function turnCards() {
        $this->refillPatternArea();
        $this->resetAnimation();
    }

    public function stCheckReturn() {
        $index = $this->getGameStateValue("return_block_index");

        // Check if there are more cards left to process
        $remaining = self::getUniqueValueFromDB("SELECT COUNT(*) FROM return_blocks");
    
        if ($index + 1 < $remaining) {
            // Increase the pointer
            $this->setGameStateValue("return_block_index", $index + 1);
            $this->gamestate->nextState("returnBlock");  // Continue the loop
        } else {
            $this->gamestate->nextState("nextTurn");  // Move to the next player
        }
    }

    /**
     * Migrate database.
     *
     * You don't have to care about this until your game has been published on BGA. Once your game is on BGA, this
     * method is called everytime the system detects a game running with your old database scheme. In this case, if you
     * change your database scheme, you just have to apply the needed changes in order to update the game database and
     * allow the game to continue to run with your new version.
     *
     * @param int $from_version
     * @return void
     */
    public function upgradeTableDb($from_version)
    {
//       if ($from_version <= 1404301345)
//       {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            $this->applyDbUpgradeToAllDB( $sql );
//       }
//
//       if ($from_version <= 1405061421)
//       {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            $this->applyDbUpgradeToAllDB( $sql );
//       }
    }

    /*
     * Gather all information about current game situation (visible by the current player).
     *
     * The method is called each time the game interface is displayed to a player, i.e.:
     *
     * - when the game starts
     * - when a player refreshes the game page (F5)
     */
    protected function getAllDatas(): array
    {
        $result = [];

        // WARNING: We must only return information visible by the current player.
        $current_player_id = (int) $this->getCurrentPlayerId();

        // Get information about players.
        // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
        $result["players"] = $this->getCollectionFromDb(
            "SELECT `player_id` `id`, `player_score` `score`, `assistant` FROM `player`"
        );

        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        // Retrieve all cards in decks
        $deck_positions = ["deck_0", "deck_1", "deck_2", "deck_3"];
        foreach ($deck_positions as $deck) {
            $result["decks"][$deck] = $this->cards->getCardsInLocation($deck);
        }
        // Retrieve all cards in pattern area
        $result["pattern_area"] = $this->cards->getCardsInLocation("pattern_area");


        $result["locations"] = array_slice($this->quilt_cards, 208, 36, true);

        $result["type_arg"] = array_slice($this->quilt_cards, 0, 208, true);

        $result["options"] = $this->getGameStateValue("game_variants");

        // return player boards
        $player_ids = array_keys($this->loadPlayersBasicInfos());

        foreach ($player_ids as $player_id) {
            $result["boards"][$player_id] = array_values($this->cards->getCardsInLocation($player_id));
        }
        

        $isEndScore = intval($this->gamestate->getCurrentMainStateId()) >= 98;
        $result['endScores'] = $isEndScore ? $this->calculatePoints() : null;

        return $result;
    }

    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName()
    {
        return "quiltable";
    }

    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = [])
    {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        // Init global values with their initial values.

        // Dummy content.
        $this->setGameStateInitialValue("my_first_global_variable", 0);

        // Init game statistics.
        //
        // NOTE: statistics used in this file must be defined in your `stats.inc.php` file.

        // Dummy content.
        // $this->initStat("table", "table_teststat1", 0);
        // $this->initStat("player", "player_teststat1", 0);

        // TODO: Setup the initial game situation here.

        // create new deck

        $cards = [];
        foreach ($this->quilt_cards as $card_id => $card) {
            // Only add pattern cards (even IDs) and within the card range (0-190)
            if ($card_id % 2 == 0 && $card_id <= 190) {
                $cards[] = [
                    'id' => $card_id,
                    'type' => $card['type'], // "pattern"
                    'type_arg' => $card['type_arg'], // Identifier
                    'location' => 'deck',
                    'location_arg' => 0,
                    'nbr' => 1 // Each card should have at least one instance
                ];
            }
        }


        $this->cards->createCards($cards, 'deck');
        $this->cards->shuffle('deck'); // Shuffle entire deck

        $playerCount = $this->getPlayersNumber();
        switch ($playerCount) {
            case 1:
                $num = 48;
                break;
            case 2:
                $num = 48;
                break;
            case 3:
                $num = 24;
                break;
            case 4:
                $num = 0;
                break;
            default:
                $num = 0;
        }

        // deal the cards into 4 decks
        $deck_positions = [213, 214, 217, 218]; // The defined positions in your materials file

        // Get all cards currently in the main deck
        $all_deck_cards = $this->cards->getCardsInLocation('deck', null, null);
        $card_ids = array_keys($all_deck_cards);

        // Divide the cards into 4 equal decks (24 cards each)
        $deck_size = count($cards) / 4;
        $deck_chunks = array_chunk($card_ids, $deck_size); 

        // Move cards to the respective decks and set their location_arg
        foreach ($deck_chunks as $i => $chunk) {
            $deck_id = "deck_{$i}";
            $position = $deck_positions[$i];

            foreach ($chunk as $card_id) {
                $this->cards->moveCard($card_id, $deck_id, $position);
            }
            $this->cards->pickCardsForLocation($num/4, $deck_id, 'out_of_play');
        }

        $this->refillPatternArea();


        if ($this->getPlayersNumber() == 1) {
            $assistants = [192, 194, 196, 198, 206];
        } else {
            $assistants = [192, 194, 196, 198, 200, 206];
        }


        // Set multi-active state for players to choose assistant if applicable
        if (($this->getGameStateValue("game_variants") != 2 && $this->getGameStateValue("quilting_assistants") == 1) || ($this->getPlayersNumber() == 1 && $this->getGameStateValue("quilting_assistants") == 1)) {
            # get random assistant cards the players can choose from
            $players = $this->loadPlayersBasicInfos();
            

            # set assigned assistant card to choose from
            foreach($players as $player_id => $info) {
                $randomIndex = bga_rand(0, count($assistants) - 1);
                $first_choice = $assistants[$randomIndex];
                unset($assistants[$randomIndex]);
                $assistants = array_values($assistants);
                $this->DbQuery("UPDATE player SET assistant = $first_choice WHERE player_id = $player_id");
                
            }

            # set all players active to choose assistant
            
            // $this->gamestate->setAllPlayersMultiactive();
            // $this->gamestate->nextState("assistant");
            

        } else {
            // Activate first player once everything has been initialized and ready.
            //$this->activeNextPlayer();
            // $this->gamestate->nextState("normal");
            // $this->activeNextPlayer();
        }


        
    }
    function stChooseAssistant() {
        # This function is ONLY for moving on to another state if they don't need to choose assistants. 
        if (!($this->getGameStateValue("game_variants") == 1 && $this->getGameStateValue("quilting_assistants") == 1) && !($this->getPlayersNumber() == 1 && $this->getGameStateValue("quilting_assistants") == 1)) {
            $this->activeNextPlayer();
            $this->gamestate->nextState("next");
            
        } else {
            $this->gamestate->setAllPlayersMultiactive();
        }
        
        

}


    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: pass).
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, otherwise it will fail with a
     * "Not logged" error message.
     *
     * @param array{ type: string, name: string } $state
     * @param int $active_player
     * @return void
     * @throws feException if the zombie mode is not supported at this game state.
     */
    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($state_name) {
                default:
                {
                    $this->gamestate->nextState("zombiePass");
                    break;
                }
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state["type"] === "multipleactiveplayer") {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }


    // UTILITY FUNCTIONS


    function getPremiumPoints($player_id) {
        $quilt = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);
        $points = 0;

        if ($this->getGameStateValue("game_variants") == 1 || $this->getPlayersNumber() == 1) {
            foreach ($quilt as $card_id => $card_info) {
                $points += intval($this->quilt_cards[$card_info["type_arg"]]["points"]);
            }
        } else {
            $points = "N/A";
        }
        return $points;
    }
    function getPatternPoints($player_id) {
        if ($this->getGameStateValue("game_variants") == 1 || $this->getPlayersNumber() == 1) {
            $quilt = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);
            $patterns = $this->cards->getCardsOfTypeInLocation("pattern", null, $player_id, null);
            $total = 0;

            # Build dynamic quilt
            $quilt_built = [[],[],[],[]];
            for($r=1;$r<=4;$r++) {
                for($c=1;$c<=4;$c++) {
                    $quilt_built[$r][$c] = null;
                }
            }

            foreach ($quilt as $quiltCardId => $patch) {
                # extract card args
                $arg = $patch["type_arg"];
                $location = $patch["location_arg"];

                # extract card data needed
                $name = $this->quilt_cards[$arg]["name"];
                    # subtract 1 because in material file they start at 2
                $row = $this->quilt_cards[$location]["row"]-1;
                $col = $this->quilt_cards[$location]["col"]-1;
                # put card name in new quilt location
                $quilt_built[$row][$col] = $name;
            }
            
            # single row or more for pattern rotations function
            foreach ($patterns as $patternCardId => $card) {
                $arg = $card["type_arg"];
                $card = $this->quilt_cards[$arg];
                $pattern = $card["pattern"];
                if (is_string($pattern[0])) {
                    # oneline pattern
                    $type = "one";
                } else {
                    # multiline pattern
                    $type = "multi";
                }


                # get all pattern rotations
                $rotations = $this->getPatternRotations($pattern, $type);

                # array for all matches of this pattern in all directions. The array contains a list of row/col for each critical point in the pattern match
                $matches = [];

                # 1. find all pattern matches and add to a list "matches"
                foreach ($rotations as $rotation) {
                    $row_breadth = count($rotation);
                    $col_breadth = count($rotation[0]);

                    for ($r=1;$r<=5-$row_breadth; $r++) {
                        for ($c=1;$c<=5-$col_breadth; $c++) {
                            $match = true;
                            $lookup = ["A"=>"", "B"=>"", "C"=>""];
                            $current_row = 0;
                            $match_data = [];
                            foreach ($rotation as $row_value) {
                                $current_col = 0;
                                $row = $current_row + $r;
                                foreach($row_value as $name) {
                                    $col = $current_col + $c;
                                    array_push($match_data, ["row"=>$row, "col"=>$col]);
                                    if ($name == "ANY") {
                                        $current_col++;
                                        continue;
                                    } else if (is_null($quilt_built[$row][$col])) {
                                        $match=false;
                                    }
                                     else if (in_array($name, ["A", "B", "C"])) {
                                        if ($lookup[$name] == "") {
                                            if (in_array($quilt_built[$row][$col], array_values($lookup))){
                                                $match = false;
                                            }
                                            else {
                                                $lookup[$name] = $quilt_built[$row][$col];
                                            }
                                        }
                                        else if($lookup[$name] != $quilt_built[$row][$col]) {$match = false;
                                        }           
                                    } else if ($name != $quilt_built[$row][$col]) {
                                        $match = false;
                                    }
                                    $current_col++;
                                }
                                $current_row++;
                            }
                            if ($match) {
                                array_push($matches, $match_data);
                            }
                        }
                    }
                }
                # 2. create a new list "overlap" with all patterns with at least 1 critical point overlap
                $maxValue = 5;
                while ($maxValue > 0) {
                    if (empty($matches)) {
                        break;
                    }
                    $overlap = [];
                    for ($i=0;$i<count($matches);$i++) {
                        $overlap[$i] = 0;
                    }
                    foreach ($matches as $index1 => $match1) {
                        foreach($matches as $index2 => $match2) {
                            #skip itself
                            if ($index1 == $index2) {continue;}
                            # add number of overlaps with this match
                            $overlap[$index1] += $this->overlaps($match1, $match2);
                        } 
                    }
                     # 3. select first pattern match in "overlap" with the max number of critical points overlapped with other matches
                    #  Then remove from "allMatches"
                    $maxIndex = array_keys($overlap, max($overlap))[0];
                    $maxValue = $overlap[$maxIndex];
                    if ($maxValue <= 0) {break;}
                    unset($matches[$maxIndex]);
                    $matches = array_values($matches);
                }
                # 5. Add count("allMatches")*patternPoint to total points to be returned
                $total += intval($card["points"])*count($matches);
            }       
            return $total;
        } else {
            return "N/A";
        }
        }
        #helper
        function overlaps($match1, $match2) {
            $overlap = 0;
            foreach ($match1 as $point1) {
                foreach($match2 as $point2) {
                    if ($point1["row"] == $point2["row"] && $point1["col"] == $point2["col"]) {
                        $overlap++;
                    }
                }
            }
            return $overlap;
        }

        #helper function
        function getPatternRotations($pattern, $type) {
            $rotations = [];
            if ($type == "one") {
                # original
                array_push($rotations, [$pattern]);
                # turn each item into an array to represent vertical
                array_push($rotations, array_map(fn($n) => [$n], $pattern));
                # reverse original
                array_push($rotations, [array_reverse($pattern)]);
                # reverse array from 2nd rotation
                array_push($rotations, array_reverse(array_map(fn($n) => [$n], $pattern)));
            } else {
                # original
                array_push($rotations, $pattern);

                # same indexes are new arrays reversed
                array_push($rotations, array_map(null, ...array_reverse($pattern)));

                # reverse original
                array_push($rotations, array_reverse(array_map(fn($array) => array_reverse($array), $pattern)));

                # same indexes are new arrays
                array_push($rotations, array_reverse(array_map(null, ...$pattern)));
            }

            return $rotations;
        }
    
    function getCompletedQuiltPoints($player_id) {
        if ($this->getGameStateValue("game_variants") == 2) {
            return "N/A";
        }
        if (count(array_keys($this->cards->getCardsOfTypeInLocation("back", null, $player_id, null))) == 16) {
            return 5;
        }
        else {
            return 0;
        }
    }
    function getSymmetryPoints($player_id) {
        if ($this->getGameStateValue("symmetry") == 2 && ($this->getPlayersNumber() == 1 || $this->getGameStateValue("game_variants") == 1)) {
            return "N/A";
        }

        $quilt = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);
        $total = 0;
        $isSymmetrical = false;
        
        // Build 2D grid representation of the quilt
        $grid = array();
        $max_row = 0;
        $max_col = 0;
        
        foreach ($quilt as $card_id => $card_data) {
            $location_arg = $card_data['location_arg'];
            $material_location = $this->quilt_cards[$location_arg];
            // We're using 1-based indexing for the grid
            $row = $material_location['row'] - 1; // Original row from file: already adjusted
            $col = $material_location['col'] - 1; // Original col from file: already adjusted
            $type_arg = $card_data['type_arg'];
            $card_name = $this->quilt_cards[$type_arg]['name'];
            
            if (!isset($grid[$row])) {
                $grid[$row] = array();
            }
            $grid[$row][$col] = array(
                'id' => $card_id,
                'name' => $card_name
            );
            
            $max_row = max($max_row, $row);
            $max_col = max($max_col, $col);
        }
        
        // Determine grid size 
        $size = 4;
        
        // Fill grid with empty spots or actual cards
        $normalized_grid = array();
        for ($i = 1; $i <= $size; $i++) {
            for ($j = 1; $j <= $size; $j++) {
                if (!isset($normalized_grid[$i])) {
                    $normalized_grid[$i] = array();
                }
                
                if (isset($grid[$i][$j])) {
                    $normalized_grid[$i][$j] = $grid[$i][$j];
                } else {
                    $normalized_grid[$i][$j] = array(
                        'id' => 'empty',
                        'name' => 'empty'
                    );
                }
            }
        }

        // Check symmetry in all four directions
        $horizontal = $this->checkHorizontalSymmetry($normalized_grid, $size);
        $vertical = $this->checkVerticalSymmetry($normalized_grid, $size);
        $diagonalLeft = $this->checkDiagonalSymmetryLeft($normalized_grid, $size);
        $diagonalRight = $this->checkDiagonalSymmetryRight($normalized_grid, $size);
        
        $isSymmetrical = $horizontal || $vertical || $diagonalLeft || $diagonalRight;
        
        if ($isSymmetrical && count($quilt) > 0) {
            $total = 15;
        }


        if ($this->getGameStateValue("game_variants") == 2 && $this->getPlayersNumber() != 1) {   
            $cardsInQuilt = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);         
           if (count(array_keys($cardsInQuilt)) >= 16 && $isSymmetrical) {
            return 15;
           }
           else {return 0;}
        }
        
        return $total;
    }
    
    // Check horizontal symmetry (folding top to bottom)
    function checkHorizontalSymmetry($grid, $size) {
        for ($i = 1; $i <= $size / 2; $i++) {
            for ($j = 1; $j <= $size; $j++) {
                $opposite = $size+1 - $i;
                
                // If both cells have cards (not empty), they must match
                if ($grid[$i][$j]['name'] != 'empty' && $grid[$opposite][$j]['name'] != 'empty') {
                    if ($grid[$i][$j]['name'] != $grid[$opposite][$j]['name']) {
                        return false;
                    }
                }
                // If one or both are empty, that's fine
            }
        }
        return true;
    }
    
    // Check vertical symmetry (folding left to right)
    function checkVerticalSymmetry($grid, $size) {
        for ($i = 1; $i <= $size; $i++) {
            for ($j = 1; $j <= $size / 2; $j++) {
                $opposite = $size+1 - $j;
                
                // If both cells have cards (not empty), they must match
                if ($grid[$i][$j]['name'] != 'empty' && $grid[$i][$opposite]['name'] != 'empty') {
                    if ($grid[$i][$j]['name'] != $grid[$i][$opposite]['name']) {
                        // For debugging
                        //$this->debug_log("Vertical mismatch: [$i][$j]=".$grid[$i][$j]['name']." vs [$i][$opposite]=".$grid[$i][$opposite]['name']);
                        return false;
                    }
                }
                // If one or both are empty, that's fine
            }
        }
        return true;
    }
    
    // Check diagonal symmetry (top-left to bottom-right)
    function checkDiagonalSymmetryLeft($grid, $size) {
        for ($i = 1; $i <= $size; $i++) {
            for ($j = 1; $j <= $size; $j++) {
                // Skip the cells on the main diagonal
                if ($i == $j) {
                    continue;
                }
                
                // If both cells have cards (not empty), they must match
                if ($grid[$i][$j]['name'] != 'empty' && $grid[$j][$i]['name'] != 'empty') {
                    if ($grid[$i][$j]['name'] != $grid[$j][$i]['name']) {
                        return false;
                    }
                }
                // If one or both are empty, that's fine
            }
        }
        return true;
    }
    
    // Check diagonal symmetry (top-right to bottom-left)
    function checkDiagonalSymmetryRight($grid, $size) {
        for ($i = 1; $i <= $size; $i++) {
            for ($j = 1; $j <= $size; $j++) {
                $opposite_i = $size+1 - $j;
                $opposite_j = $size+1 - $i;
                
                // Skip cells on the diagonal
                if ($i + $j == $size+1) {
                    continue;
                }
                
                // If both cells have cards (not empty), they must match
                if ($grid[$i][$j]['name'] != 'empty' && $grid[$opposite_i][$opposite_j]['name'] != 'empty') {
                    if ($grid[$i][$j]['name'] != $grid[$opposite_i][$opposite_j]['name']) {
                        return false;
                    }
                }
                // If one or both are empty, that's fine
            }
        }
        return true;
    }
    
    function getPatchesPoints($player_id) {
        if ($this->getGameStateValue("game_variants") == 2 || $this->getGameStateValue("patches") == 2) {
            return "N/A";
        }
        $total = 0;
        $patchPoints = 3;
        $gridSize = 4; 
        
        $quilt = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);
        $grid = array_fill(1, $gridSize, array_fill(1, $gridSize, null));
        
        // Populate grid with card data
        foreach ($quilt as $card_id => $card_data) {
            $location_arg = $card_data['location_arg'];
            $material_location = $this->quilt_cards[$location_arg];
            
            $row = $material_location['row'] - 1;
            $col = $material_location['col'] - 1;
            
            $type_arg = $card_data['type_arg'];
            $grid[$row][$col] = $this->quilt_cards[$type_arg];
        }
        
        // Check horizontal matches
        for ($r = 1; $r <= $gridSize; $r++) {
            for ($c = 1; $c < $gridSize; $c++) {
                if (!$grid[$r][$c] || !$grid[$r][$c + 1]) continue; // Skip empty spaces
                
                $tileA = $grid[$r][$c];
                $tileB = $grid[$r][$c + 1];
                
                if (
                    ($tileA['color'] === $tileB['color']) &&
                    (($tileA['position'] === 'middle' && $tileB['position'] === 'middle') ||
                    ($tileA['position'] === 'right' && $tileB['position'] === 'left') ||
                    ($tileA['position'] === 'left' && $tileB['position'] === 'right'))
                ) {
                    $total += $patchPoints;
                }
            }
        }
        
        // Check vertical matches
        for ($c = 1; $c <= $gridSize; $c++) {
            for ($r = 1; $r < $gridSize; $r++) {
                if (!$grid[$r][$c] || !$grid[$r + 1][$c]) continue; // Skip empty spaces
                
                $tileA = $grid[$r][$c];
                $tileB = $grid[$r + 1][$c];
                
                if (
                    ($tileA['color'] === $tileB['color']) &&
                    (($tileA['position'] === 'middle' && $tileB['position'] === 'middle') ||
                    ($tileA['position'] === 'right' && $tileB['position'] === 'left') ||
                    ($tileA['position'] === 'left' && $tileB['position'] === 'right'))
                ) {
                    $total += $patchPoints;
                }
            }
        }
        return $total;
    }
    


    // Used to calculate all the points of player and return array player_id => pointType => points
function calculatePoints() {
    $scores = [];
    $players = $this->loadPlayersBasicInfos();

    foreach ($players as $player_id => $data) {
        $scores[$player_id] = [
                'premium'   => $this->getPremiumPoints($player_id),
                'patterns'  => $this->getPatternPoints($player_id),
                'completed' => $this->getCompletedQuiltPoints($player_id),
                'symmetry'  => $this->getSymmetryPoints($player_id),
                'patches'   => $this->getPatchesPoints($player_id),
                'total' => $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id = $player_id")
        ];
    }

    return $scores;
}


    function getNextPlayer() {
        $currentPlayerNo = $this->getPlayerNoById($this->getActivePlayerId());

        if ($this->getPlayersNumber() == $currentPlayerNo) {
            $nextPlayer = $this->getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = 1");
        } else {
            $nextPlayer = $this->getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = $currentPlayerNo + 1");
        }
        return $nextPlayer;
    }

    function checkEndConditions($player_id) {
        if ($this->getGameStateValue("game_variants") == 2 && $this->getPlayersNumber() != 1) {
            $cardsInQuilt = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);
            
            if(count(array_keys($cardsInQuilt)) >= 16 && $this->getSymmetryPoints($player_id) == 15) {
                $this->DbQuery("UPDATE player SET endTriggered = 1 WHERE player_id = $player_id");
                $this->DbQuery("UPDATE player SET player_score = 15 WHERE player_id = $player_id");

                $this->gamestate->nextState("postEnd");
            }
        } else {
            $cardsInQuilt = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);
            
            if(count(array_keys($cardsInQuilt)) >= 16 && $this->getUniqueValueFromDB("SELECT COUNT(*) FROM player WHERE endTriggered = 1;") != 1) {
                $this->DbQuery("UPDATE player SET endTriggered = 1 WHERE player_id = $player_id");
            }
        }
    }

    function resetAnimation() {
        sleep(1);
        $this->DbQuery("DELETE FROM animation;");
    }

    function validatePlayerCards($args) {
        $allCardsPlayerTable = $this->cards->getCardsOfTypeInLocation("back", null, $this->getActivePlayerId(), null);
        foreach ($args as $arg) {
            $card_id = $arg["cardId"];
            $loc = $arg["locationId"];
            // validation
            // TODO update number validation for specific player cards
            if (!$this->cards->getCard($card_id)) {
                var_dump("#1");
                return false;
            }
            if ($this->cards->getCard($card_id)["location"] != "pattern_area") {
                var_dump("#2");
                return false;
            } if (count($args) < 2 || count($args) > 3) {
                var_dump("#3");
                return false;
            } if ($this->cards->countCardInLocation($this->getActivePlayerId(), $loc) > 0) {
                var_dump("#4");
                return false;
            }
        }
        // After all other checks...
    $positions = [];
    foreach ($args as $arg) {
        $pos = $this->getCardGridPosition($arg["locationId"]);
        $positions[] = $pos;
    }
    //var_dump($args);
    //var_dump($positions);

    // Build a set of valid positions to quickly look them up
    $positionSet = [];
    foreach ($positions as $p) {
        $key = $p['row'] . '-' . $p['col'];
        $positionSet[$key] = true;
    }

    // Start DFS or BFS from the first card
    $start = $positions[0];
    $stack = [[$start['row'], $start['col']]];
    $visited = [];

    while (!empty($stack)) {
        list($r, $c) = array_pop($stack);
        $key = "$r-$c";
        if (isset($visited[$key])) continue;
        $visited[$key] = true;

        // Check all 4 orthogonal neighbors
        foreach ([[-1,0],[1,0],[0,-1],[0,1]] as [$dr, $dc]) {
            $nr = $r + $dr;
            $nc = $c + $dc;
            $neighborKey = "$nr-$nc";
            if (isset($positionSet[$neighborKey]) && !isset($visited[$neighborKey])) {
                $stack[] = [$nr, $nc];
            }
        }
    }

    // Check if all positions were visited (i.e. connected)
    if (count($visited) != count($positions)) {
        var_dump("#5");
        return false;
    }

    // Check that at least one of the new cards is adjacent to an existing card on the player table
    $adjacentFound = false;
    
    foreach ($allCardsPlayerTable as $existingCard) {
        $existingPos = $this->quilt_cards[$existingCard['location_arg']];
        $erow = $existingPos['row']-1;
        $ecol = $existingPos['col']-1;

        
        foreach ($positions as $newPos) {
            $nrow = $newPos['row'];
            $ncol = $newPos['col'];

            if (
                ($nrow == $erow && abs($ncol - $ecol) == 1) ||
                ($ncol == $ecol && abs($nrow - $erow) == 1)
            ) {
                $adjacentFound = true;
                break 2;
            }
        }
        //var_dump($positions);
        //var_dump($allCardsPlayerTable);
    }

    if (!$adjacentFound && count($allCardsPlayerTable) > 0) {
        var_dump("#6");
        return false;
    }


            
        return true;
    }

    private function getCardGridPosition($locId) {
        $info = $this->quilt_cards[$locId];
        return [
            'row' => $info['row']-1, // stored or passed based on your card structure
            'col' => $info['col']-1,
        ];
    }    

    function checkIfTopCard($card_id) {
        for ($i = 0; $i < 4; $i++) {
            $deck_name = "deck_{$i}";
    
            // Get the top card from this deck (smallest location_order is on top)
            $cards = $this->cards->getCardsInLocation($deck_name, null, 'card_id ASC');
    
            if (!empty($cards)) {
                // Get the top card's id
                $top_card_id = reset($cards)['id'];
    
                // Check if the given card_id is the top card
                if ($top_card_id == $card_id) {
                    return true; // card_id is the top card in this deck
                }
            }
        }
    
        // If we reached here, card_id was not the top card in any deck
        return false;


    }
    

    function refillPatternArea() {
        $pattern_grid = [
            213 => [208, 209, 212], // Closest to top-left deck
            214 => [210, 211, 215], // Closest to top-right deck
            217 => [216, 220, 221], // Closest to bottom-left deck
            218 => [219, 222, 223]  // Closest to bottom-right deck
        ];
    
        $deck_neighbors = [
            'deck_0' => ['deck_1', 'deck_2', 'deck_3'],
            'deck_1' => ['deck_0', 'deck_3', 'deck_2'],
            'deck_2' => ['deck_3', 'deck_0', 'deck_1'],
            'deck_3' => ['deck_2', 'deck_1', 'deck_0'], 
        ];
        
    
        $deck_positions = [213 => 'deck_0', 214 => 'deck_1', 217 => 'deck_2', 218 => 'deck_3'];
    
        // Step 1: Identify empty locations
        $empty_spots = [];
        foreach (array_merge(...array_values($pattern_grid)) as $loc) {
            if (empty($this->cards->getCardsInLocation('pattern_area', $loc))) {
                $empty_spots[] = $loc;
            }
        }
    
        // Step 2: Find available decks
        $available_decks = [];
        foreach ($deck_positions as $pos => $deck_id) {
            $available_decks[$deck_id] = $this->cards->getCardsInLocation($deck_id);
        }
    
        // Step 3: Refill pattern area spots
foreach ($empty_spots as $loc) {
    foreach ($pattern_grid as $deck_pos => $locations) {
        if (in_array($loc, $locations)) {
            $deck_id = $deck_positions[$deck_pos];
            $source_deck = null;

            // Check if the deck has cards to refill spots (even if it's the last card)
            if (!empty($available_decks[$deck_id]) || count($available_decks[$deck_id]) > 0) {
                // If the deck has cards, it can refill its own spots
                $source_deck = $deck_id;
            } else {
                // If the deck is empty, check its neighboring decks
                foreach ($deck_neighbors[$deck_id] as $neighbor) {
                    if (!empty($available_decks[$neighbor])) {
                        $source_deck = $neighbor;
                        break; // If we found a deck with cards, break and use it
                    }
                }
            }

            // If no source deck was found, continue to the next spot
            if ($source_deck === null) continue;

            // If deck still has cards, and we need to refill a card in any spot
            if (!empty($available_decks[$source_deck]) || count($available_decks[$source_deck]) > 0) {
                // Get the card from the source deck
                $card_id = array_key_first($available_decks[$source_deck]);
                $card = $available_decks[$source_deck][$card_id];
                unset($available_decks[$source_deck][$card_id]);

                // Look up the "other side" in $this->quilt_cards
                $current_type_arg = $card['type_arg'];
                $new_type_arg = $this->quilt_cards[$current_type_arg]['other_side'] ?? null;

                if ($new_type_arg === null) {
                    throw new feException("Error: Unable to find other side for card type_arg {$current_type_arg}");
                }

                $new_card_type = $this->quilt_cards[$new_type_arg]["type"];

                // Move to pattern area and update type_arg
                $this->cards->moveCard($card_id, 'pattern_area', $loc);
                $this->cards->DbQuery("UPDATE card SET card_type_arg = $new_type_arg, card_type = '$new_card_type' WHERE card_id = $card_id");

                $this->DbQuery("INSERT INTO animation (card_id, target, loc, flip) VALUES ($card_id, 'pattern-board', $loc, $new_type_arg)");
            }
            break; // Exit after refilling the spot
        }
    }
}

// Step 4: Refill empty deck positions (if the deck is empty, but neighbors have cards)
foreach ($deck_positions as $pos => $deck_id) {
    if (!empty($available_decks[$deck_id])) continue; // Skip if deck still has cards
    
    // Look for cards in neighboring decks
    foreach ($deck_neighbors[$deck_id] as $neighbor) {
        if (!empty($available_decks[$neighbor])) {
            $source_deck = $neighbor;
            break;
        }
    }

    if (!isset($source_deck) || !isset($available_decks[$source_deck]) || empty($available_decks[$source_deck])) {
        self::debug("No available deck found for position: " . $pos);
        continue;
    }

    $card_id = array_key_first($available_decks[$source_deck]);
    
    if (!isset($available_decks[$source_deck][$card_id])) {
        self::debug("Card ID not found in deck: " . $source_deck);
        continue;
    }

    $card = $available_decks[$source_deck][$card_id];
    unset($available_decks[$source_deck][$card_id]);

    // Make sure the card is flipped when moved
    $current_type_arg = $card['type_arg'];
    $new_type_arg = $this->quilt_cards[$current_type_arg]['other_side'] ?? null;

    if ($new_type_arg === null) {
        throw new \BgaUserException("Error: Unable to find other side for card type_arg {$current_type_arg}"); //line 763
    }

    $new_card_type = $this->quilt_cards[$new_type_arg]["type"];

    // Ensure there aren't cards placed on top of other turned-over cards in the deck
    if ($this->cards->countCardInLocation("pattern_area", $pos) == 0) {
        $this->cards->moveCard($card_id, 'pattern_area', $pos);
        $this->cards->DbQuery("UPDATE card SET card_type_arg = $new_type_arg, card_type = '$new_card_type' WHERE card_id = $card_id");
        $this->DbQuery("INSERT INTO animation (card_id, target, loc, flip) VALUES ($card_id, 'pattern-board', $pos, $new_type_arg)");
        $this->refillPatternArea();
    }
}


    }

    public function checkReturnAdjacency($loc) {
        $corners = [228, 233, 238, 243];
        if (!in_array($loc, $corners)) {
            return true;
        }

        if ($loc == 243) {
            $plus = 224;
        } else {$plus = intval($loc) + 1;}
        
        $minus = intval($loc) - 1;

        if ($this->cards->countCardInLocation("pattern_area", $plus) > 0 || $this->cards->countCardInLocation("pattern_area", $minus) > 0) {
            return true;
        }

        return false;
    }
    
    public function checkReturnAvailablility() {
        for ($i = 224; $i < 244; $i++) {
            if ($this->cards->countCardInLocation("pattern_area", $i) < 1) {
                return true;
            }
        }

        return false;
    }

    public function checkPlanAvailablility() {
        $decks = ['deck_0', 'deck_1', 'deck_2', 'deck_3'];
        foreach ($decks as $deck) {
            if ($this->cards->countCardInLocation($deck) > 0) {
                return true;
            }
        }

        return false;
    }
    



}
