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
            "my_first_game_variant" => 100,
            "my_second_game_variant" => 101,
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
        $this->notify->all("pass", clienttranslate('${player_name} passes'), [
            "player_id" => $player_id,
            "player_name" => $this->getActivePlayerName(), // remove this line if you uncomment notification decorator
        ]);

        // at the end of the action, move to the next state
        $this->gamestate->nextState("pass");
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
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // Give some extra time to the active player when he completed an action
        $this->giveExtraTime($player_id);
        
        $this->activeNextPlayer();

        // Go to another gamestate
        // Here, we would detect if the game is over, and in this case use "endGame" transition instead 
        $this->gamestate->nextState("nextPlayer");
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
            "SELECT `player_id` `id`, `player_score` `score` FROM `player`"
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
        }

        $this->refillPatternArea();





        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();
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

    function refillPatternArea() {
        $pattern_grid = [
            213 => [208, 209, 212], // Closest to top-left deck
            214 => [210, 211, 215], // Closest to top-right deck
            217 => [216, 220, 221], // Closest to bottom-left deck
            218 => [219, 222, 223]  // Closest to bottom-right deck
        ];
    
        $deck_neighbors = [
            'deck_0' => ['deck_1'],
            'deck_1' => ['deck_0', 'deck_2'],
            'deck_2' => ['deck_1', 'deck_3'],
            'deck_3' => ['deck_2'],
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
                    if (!empty($available_decks[$deck_id])) {
                        $source_deck = $deck_id;
                    } else {
                        foreach ($deck_neighbors[$deck_id] as $neighbor) {
                            if (!empty($available_decks[$neighbor])) {
                                $source_deck = $neighbor;
                                break;
                            }
                        }
                    }
    
                    if ($source_deck === null) continue;
    
                    // Get the card
                    $card_id = array_key_first($available_decks[$source_deck]);
                    $card = $available_decks[$source_deck][$card_id];
                    unset($available_decks[$source_deck][$card_id]);
    
                    // Look up the "other side" in $this->quilt_cards
                    $current_type_arg = $card['type_arg'];
                    $new_type_arg = $this->quilt_cards[$current_type_arg]['other_side'] ?? null;
    
                    if ($new_type_arg === null) {
                        throw new feException("Error: Unable to find other side for card type_arg {$current_type_arg}");
                    }
    
                    // Move to pattern area and update type_arg
                    $this->cards->moveCard($card_id, 'pattern_area', $loc);
                    $this->cards->DbQuery("UPDATE card SET card_type_arg = $new_type_arg WHERE card_id = $card_id");
    
                    break;
                }
            }
        }
    
        // Step 4: Refill empty deck positions
        foreach ($deck_positions as $pos => $deck_id) {
            if (!empty($available_decks[$deck_id])) continue;
    
            foreach ($deck_neighbors[$deck_id] as $neighbor) {
                if (!empty($available_decks[$neighbor])) {
                    $source_deck = $neighbor;
                    break;
                }
            }
    
            if (!isset($source_deck)) continue;
    
            $card_id = array_key_first($available_decks[$source_deck]);
            $card = $available_decks[$source_deck][$card_id];
            unset($available_decks[$source_deck][$card_id]);
    
            // Keep it as a deck card (do not flip)
            $this->cards->moveCard($card_id, $deck_id, $pos);
        }
    }
    
    



}
