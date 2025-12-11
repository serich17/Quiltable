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
use \Bga\GameFramework\Actions\Types\JsonParam;


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
            "turn_counter" => 73,
            "use_assistant" => 74,
            "sally_counter" => 75,
            "main_player"=> 76,
            "helper_player" => 77,
            "which_transition" => 78,
            "quilt_master" => 79,
            "quilt_master_turns" => 80,
            "isQuiltTurn" => 81,
            "sally_only_used_pattern" => 82,
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
    function actChooseAssistant(int $option) {
        $player_id = $this->getCurrentPlayerId();
        $options = $this->argChooseAssistant();
        if (in_array($option, $options[$player_id])) {
            $this->DbQuery("UPDATE player SET assistant = $option WHERE player_id = $player_id");
        }
    // Save choice in DB
        

        // Mark this player as done

        // If all players are done, notify players
        if ((count($this->gamestate->getActivePlayerList()) == 1) && ($this->getGameStateValue("quilting_assistants") == 1)) {
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
        $this->activeNextPlayer();
        $player = $this->getActivePlayerId();
        $this->setGameStateValue("use_assistant", (int)$this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player"));
        if ($this->getPlayersNumber() == 1) {
            $this->gamestate->setPlayerNonMultiactive((int)$player_id, "helper");
        } else {
            $this->gamestate->setPlayerNonMultiactive((int)$player_id, "next");
        }
    }

    function actQuiltMaster(int $id) {
        $this->setGameStateValue("quilt_master", $id);
        $this->notify->all("plQuilt", "", ["loc"=>$id]);
        $this->gamestate->nextState("turn");
    }

function quiltMasterTurn() {
    $args = [];
    $key_pairs = [
         225 => 209, 226 => 210, 229 => 211,
         230 => 215, 231 => 219, 232 => 223,
         235 => 222, 236 => 221, 239 => 220,
         240 => 216, 241 => 212, 242 => 208,
    ];

    $keys   = array_keys($key_pairs);
    $values = array_values($key_pairs);
    $current_position = $this->getGameStateValue("quilt_master");
    $current_index = array_search($current_position, $keys);

    // Get the cards that he chooses
    $chosen = [];
    $num_to_take = $this->getGameStateValue("quilt_master_turns");
    
    $i = 0;  // Index in the circular array
    while (count($chosen) < $num_to_take) {
        $cycled_index = ($current_index + $i) % count($values);
        $next = $values[$cycled_index];
        
        // Only add if not already in chosen (skip duplicates)
        if (!in_array($next, $chosen)) {
            $chosen[] = $next;
        }
        
        $i++;  // Always move to the next position
        
        // Safety check to prevent infinite loop
        if ($i > count($values) * 2) {
            break;
        }
    }


    $animations = [];
    $animations["animation"] = [];
    foreach($chosen as $index => $id) {
        $cards = $this->cards->getCardsOfTypeInLocation("back", null, "pattern_area", $id);
        if (count($cards) == 1) {
            $card = array_values($cards)[0];
            $card_id = $card["id"];
            $target = "pattern-board";
            $animations["animation"][] = [
                "card_id" => $card_id,
                "target" => $target,
                "loc" => $current_position,
                "flip" => 0
            ];

            $this->cards->moveCard($card_id, "quiltMaster");
            $new_type_arg = $this->quilt_cards[$card["type_arg"]]["other_side"];
            $this->cards->DbQuery("UPDATE card SET card_type_arg = $new_type_arg, card_type = 'pattern' WHERE card_id = $card_id");
        }
    }
    $args["cards"] = $animations;

    $this->notify->all("return", clienttranslate('<b>Quilting Master</b> takes ${card_num} cards'),
        array("card_num" => $num_to_take)
    );      

    // Update new position for quilt_master
    $current_index = ($current_index + $i) % count($keys);
    $this->setGameStateValue("quilt_master", $keys[$current_index]);

    $args["moveMaster"] = [
        "animation" => [
            205 => [
                "card_id" => 205,
                "target" => "pattern-board",
                "loc" => $keys[$current_index],
                "flip" => 0
            ]
        ]
    ];
    $args["player_id"] = $this->getActivePlayerId();
    $this->notify->all("quilt_master", "", $args);
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

    public function acttim(int $player_id, int $card_id) {
        $active_player = (int)$this->getActivePlayerId();
        $player_asistant = $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $active_player");
        if ($this->getGameStateValue("use_assistant") == 0 || 194 != $player_asistant) {
            throw new \BgaUserException(_('Assistant not available'));
        }

        if ($this->getPlayerCount() == 1) {
            $this->cards->moveCard($card_id, "out_of_play");
            $target = 1;
        } else {
            $this->cards->moveCard($card_id, (string)$player_id);
            $target = "player-table-" . $player_id;
        }


        $this->notify->all("plan", clienttranslate('${player_name} uses assistant [Terrific Tim(194)] to trade [${card_name}(${card_arg})] for extra turn'),
                    array(
                        "player_id" => $active_player,
                        "card_arg" => $this->cards->getCard($card_id)["type_arg"],
                        "card_name" => "pattern card",
                        "player_name" => $this->getPlayerNameById($active_player)
                    ));

            $this->notify->all("animation", "", ["player_id"=> $this->getActivePlayerId(),"animation"=>[$card_id => ["card_id"=>$card_id, "target"=>$target, "loc"=>0, "flip"=>0]]]);
        
        $this->DbQuery("UPDATE player SET num_turns = num_turns + 1 WHERE player_id = $active_player");
        $this->gamestate->nextState("back");
    }

    public function actSandra(int $block, int $player_board) {
        $player_id = $this->getActivePlayerId();
        $block = $this->cards->getCard($block);
        $player_board = $this->cards->getCard($player_board);

        if ($player_board["location"] != $player_id || $block["location"] != "pattern_area") {
            throw new \BgaUserException(_('Invalid cards selected'));
        }

        $this->cards->moveCard($block["id"], $player_id, $player_board["location_arg"]);
        $this->cards->moveCard($player_board["id"], "pattern_area", $block["location_arg"]);

        $this->notify->all("plan", clienttranslate('${player_name} uses assistant [Swap Sandra(195)] to swap [${card_name1}(${card_arg1})] with [${card_name2}(${card_arg2})]'),
                    array(
                        "player_id" => $this->getActivePlayerId(),
                        "card_arg1" => $player_board["type_arg"],
                        "card_name1" => "pattern card",
                        "card_arg2" => $block["type_arg"],
                        "card_name2" => "pattern card",
                        "player_name" => $this->getPlayerNameById((int)$this->getActivePlayerId())
                    ));

        $this->notify->all("animation", "", ["player_id"=> $this->getActivePlayerId(),"animation"=>[
            $block["id"] => ["card_id"=>$block["id"], "target"=>"player-table-" . $player_id, "loc"=>$player_board["location_arg"], "flip"=>0],
            $player_board["id"] => ["card_id"=>$player_board["id"], "target"=>"pattern-cont", "loc"=>$block["location_arg"], "flip"=>0]
    ]]);
    $this->setGameStateValue("use_assistant", 0);
    $this->gamestate->nextState("nextPlayer");

    }

    public function actGranny(int $id) {
        $player_id = $this->getCurrentPlayerId();
        $card = $this->cards->getCard($id);
        if ($card["location"] != $player_id || $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id") != 196) {
            throw new \BgaUserException(_('Invalid option'));
        }
        $new_type_arg = $this->quilt_cards[$card["type_arg"]]["other_side"];

        $this->cards->moveCard($id, $player_id, 0);
        $this->cards->DbQuery("UPDATE card SET card_type_arg = $new_type_arg, card_type = 'pattern' WHERE card_id = $id");

        $this->notify->all("plan", clienttranslate('${player_name} uses assistant [Granny Smith(196)] to flip [${card_name1}(${card_arg1})] to pattern [${card_name2}(${card_arg2})]'),
                    array(
                        "player_id" => $this->getActivePlayerId(),
                        "card_arg1" => $card["type_arg"],
                        "card_name1" => "pattern card",
                        "card_arg2" => $new_type_arg,
                        "card_name2" => "pattern card",
                        "player_name" => $this->getPlayerNameById((int)$this->getActivePlayerId())
                    ));

        $this->notify->all("animation", "", ["player_id"=> $this->getActivePlayerId(),"animation"=>[
            $id => ["card_id"=>$id, "target"=>"player-table-" . $player_id, "loc"=>"0", "flip"=>$new_type_arg]
        ]]);

        $this->setGameStateValue("use_assistant", 0);
        $this->gamestate->nextState("nextPlayer");

    }

    function getAdjacentCards($player_id) {
        $cards = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);
        $locations = [];
        $adjacents = [
                208=> [209, 212],
                209=> [208, 210, 213],
                210=> [209, 211, 214],
                211=> [210, 215],
                212=> [208, 213, 216],
                213=> [209, 212, 214, 217],
                214=> [210, 213, 215, 218],
                215=> [211, 214, 219],
                216=> [212, 217, 220],
                217=> [213, 216, 218, 221],
                218=> [214, 217, 219, 222],
                219=> [215, 218, 223],
                220=> [216, 221],
                221=> [217, 220, 222],
                222=> [218, 221, 223],
                223=> [219, 222]];
        if (count($cards) == 0) {
            return array_keys($adjacents);
        }
        foreach($cards as $card) {
            $locations = array_merge($locations, $adjacents[intval($card["location_arg"])]);
        }
        // var_dump($locations);
        $locations = array_values(array_unique($locations));
        foreach($cards as $card) {
            if (in_array(intval($card["location_arg"]), $locations)) {
                $locations = array_values(array_diff($locations, [$card["location_arg"]]));
            }
        }
        return $locations;
    }

    public function actSam(int $pattern, int $loc) {
        $player_id = $this->getCurrentPlayerId();
        $card = $this->cards->getCard($pattern);
        if (count($this->cards->getCardsOfTypeInLocation("back", null, $player_id, $loc)) > 0 || (!$this->checkIfTopCard($pattern) && count($this->cards->getCardsOfTypeInLocation("pattern", null, $player_id, 0))<1)) {
            throw new \BgaUserException(_('Invalid card options'));
        }

        $new_type_arg = $this->quilt_cards[$card["type_arg"]]["other_side"];

        $this->cards->moveCard($pattern, $player_id, $loc);
        $this->cards->DbQuery("UPDATE card SET card_type_arg = $new_type_arg, card_type = 'back' WHERE card_id = $pattern");

        $this->notify->all("plan", clienttranslate('${player_name} uses assistant [Uncle Sam(197)] to flip pattern [${card_name1}(${card_arg1})] to block [${card_name2}(${card_arg2})] in quilt'),
                    array(
                        "player_id" => $this->getActivePlayerId(),
                        "card_arg1" => $card["type_arg"],
                        "card_name1" => "pattern card",
                        "card_arg2" => $new_type_arg,
                        "card_name2" => "pattern card",
                        "player_name" => $this->getPlayerNameById((int)$this->getActivePlayerId())
                    ));

        $this->notify->all("animation", "", ["player_id"=> $this->getActivePlayerId(),"animation"=>[
            $pattern => ["card_id"=>$pattern, "target"=>"player-table-" . $player_id, "loc"=>$loc, "flip"=>$new_type_arg]
        ]]);

        $this->setGameStateValue("use_assistant", 0);
        $this->gamestate->nextState("refill");

    }

    public function actTravis(string $tar_player) {
        $player_id = $this->getActivePlayerId();
        $player_assis = $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id");
        $tar_assis = $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $tar_player");
        if ($this->getGameStateValue("use_assistant") == 0 || $player_assis != 200 || !$tar_assis) {
            throw new \BgaUserException(_('Assistant not available'));
        }

        $this->DbQuery("UPDATE player SET assistant = $tar_assis WHERE player_id = $player_id");
        $this->DbQuery("UPDATE player SET assistant = $player_assis WHERE player_id = $tar_player");

        $this->notify->all("plan", clienttranslate('${player_name} swaps assistants with ${player_name1} [Tricky Travis(200)] ↔️ [Other Assistant(${card_arg1})]'),
                    array(
                        "player_id" => $this->getActivePlayerId(),
                        "card_arg1" => $tar_assis,
                        "player_name" => $this->getPlayerNameById((int)$this->getActivePlayerId()),
                        "player_name1" => $this->getPlayerNameById((int)$tar_player)
                    ));

        $this->notify->all("fix_assistants", "", $this->getCollectionFromDB("SELECT player_id, assistant FROM player"));

        $this->setGameStateValue("use_assistant", 0);
        $this->gamestate->nextState("nextPlayer");


    }

    public function actMaddieOption(int $option)
        {
            $player = $this->getActivePlayerId();
            $active_player = $this->getGameStateValue("main_player");
            if ($option == 0) {
                $card = $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player");
                $other_side = $this->quilt_cards[$card]["other_side"];
                $this->DbQuery("UPDATE player SET assistant = $other_side WHERE player_id = $player");
                
                $this->notify->all("plan", clienttranslate('${player_name} uses [Mayhem Maddie(201)] to make ${player_name1} flip their assistant [Other Assistant(${card_arg2})]  ↔️ [Other Assistant(${card_arg1})]'),
                    array(
                        "player_id" => $this->getActivePlayerId(),
                        "card_arg1" => $other_side,
                        "card_arg2" => $card,
                        "player_name" => $this->getPlayerNameById((int)$active_player),
                        "player_name1" => $this->getPlayerNameById((int)$player)
                    ));

                $this->notify->all("flip_animation", "", ["id"=>$card, "other"=>$other_side]);
            } else {
                $this->cards->moveCard($option, $active_player, 0);

                $this->notify->all("plan", clienttranslate('${player_name} uses [Mayhem Maddie(201)] to make ${player_name1} flip their assistant, but they counter with a pattern [Pattern Card(${card_arg1})]'),
                    array(
                        "player_id" => $this->getActivePlayerId(),
                        "card_arg1" => $this->cards->getCard($option)["type_arg"],
                        "player_name" => $this->getPlayerNameById((int)$active_player),
                        "player_name1" => $this->getPlayerNameById((int)$player)
                    ));

                $this->notify->all("animation", "", ["player_id"=> $this->getActivePlayerId(),"animation"=>[
                    $option => ["card_id"=>$option, "target"=>"player-table-" . $active_player, "loc"=>0, "flip"=>0]
                ]]);

            }




            // Move to a state where the main player continues
            $this->gamestate->nextState('transition');

        }

        function actHelperBack() {
            $this->gamestate->nextState("back");
        }


    public function actMaddie(string $tar_player) {
        $player_id = $this->getActivePlayerId();
        $tar_assis = $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $tar_player");
        if ($this->getGameStateValue("use_assistant") == 0 || $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id") != 201 || !$tar_assis) {
            throw new \BgaUserException(_('Assistant not available'));
        }

        $this->setGameStateValue("use_assistant", 0);
        
        // Remember both players
        $this->setGameStateValue("main_player", (int)$player_id);
        $this->setGameStateValue("helper_player", (int)$tar_player);

        $this->setGameStateValue("which_transition", 1);

        // Switch to helper state
        $this->gamestate->nextState("transition");
    }


    public function actAssistantAction(int $assistant) : void {
        $player_id = (int)$this->getActivePlayerId();
        $player_asistant = $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id");
        if ($this->getGameStateValue("use_assistant") == 0 || $assistant != $player_asistant || ($player_asistant == 192 && !$this->checkPlanAvailablility())) {
            throw new \BgaUserException(_('Assistant not available'));
        }
        switch ($assistant) {
            case 192:
                # Sneaky Sally
                $this->notify->player($player_id, "sally", "", $this->argPlan());
                break;
            case 193:
                # Big Billy
                $this->notify->player($player_id, "billy", "", $this->argChoose());
                break;
            case 194:
                # Terrific Tim
                $patterns = $this->cards->getCardsOfTypeInLocation("pattern", null, $this->getCurrentPlayerId(), null);
                $player_names = $this->loadPlayersBasicInfos();
                unset($player_names[$player_id]);
                if (count($patterns) == 0) {
                    throw new \BgaUserException(_('You don\'t have patterns to donate'));
                }
                $this->notify->player($player_id, "tim", "", ["cards"=>$patterns, "players"=>$player_names]);
                break;
            case 195:
                # Swap Shop Sandra
                $board = $this->cards->getCardsOfTypeInLocation("back", null, (string)$player_id, null);
                $blocks = $this->cards->getCardsOfTypeInLocation("back", null, "pattern_area", null);
                if (count($board) < 1 || count($blocks) < 1) {
                    throw new \BgaUserException(_('You don\'t have cards to swap'));
                }
                $this->notify->player($player_id, "sandra", "", ["board" => $board, "blocks" => $blocks]);
                break;
            case 196:
                # Granny Smith
                $board = $this->cards->getCardsOfTypeInLocation("back", null, (string)$player_id, null);
                if (count($board) < 1) {
                    throw new \BgaUserException(_('You don\'t have blocks in your quilt'));
                }
                $this->notify->player($player_id, "granny", "", $board);
                break;
            case 197:
                # Uncle Sam
                $patt = [];

                foreach($this->cards->getCardsOfTypeInLocation("pattern",null, (string)$player_id, 0) as $i => $v) {
                    $patt[$v["id"]] = $v["id"];
                }
                $args = array_merge($this->argPlan(), $patt);
                if (count($args) < 1) {
                    throw new \BgaUserException(_('No patterns available'));
                }
                $this->notify->player($player_id, "sam", "", ["patts" => $args, "items" => $this->getAdjacentCards($player_id)]);
                break;
            case 198:
                # Planning Peter
                // handled in the normal placeblocks and return functions
                break;
            case 199:
                # Gifted Gladys
                $this->notify->player($player_id, "gladys", "", []);
                break;
            case 200:
                # Tricky Travis
                $this->notify->player($player_id, "travis", "", $this->getCollectionFromDB("SELECT player_id, player_name, assistant FROM player WHERE player_id != $player_id"));
                break;
            case 201:
                # Mayhem Maddie
                $this->notify->player($player_id, "maddie", "", $this->getCollectionFromDB("SELECT player_id, player_name, assistant FROM player WHERE player_id != $player_id"));
                break;

            // these two aren't in the instruction booklet
            case 206:
                # Observant Omar
                break;
            case 207:
                # Clever Clarissa
                break;
        }

        $this->notify->player((int)$this->getActivePlayerId(), "assistantAction", "", [
            "player_id" => $player_id,
            "player_name" => $this->getActivePlayerName(), // remove this line if you uncomment notification decorator
        ]);
    }

    public function actBack() : void
    {
        $this->gamestate->nextState("back");
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
        // at the end of the action, move to the next state
        $this->setGameStateValue("turn_counter", 5);
        $this->gamestate->nextState("nextPlayer");
        
    }

    public function actPlan(): void {
        if (!$this->checkPlanAvailablility()) {
            throw new \BgaUserException('No pattern cards left in deck');
        }

        $this->notify->player((int)$this->getActivePlayerId(), "plan_args", "", $this->argPlan());
    }
    public function actChoose() {
        $this->notify->player((int)$this->getActivePlayerId(), "choose_args", "", $this->argChoose());
    }
    public function actReturn() {

        if (count($this->cards->getCardsOfTypeInLocation("back", null, $this->getCurrentPlayerId(), null)) < 1) {
            throw new \BgaUserException('You don\'t have cards to return');
        }
        if (!$this->checkReturnAvailablility()) {
            throw new \BgaUserException('There aren\'t any spots available to return tiles');
        }

        $this->notify->player((int)$this->getActivePlayerId(), "return_args", "", $this->argReturn());
    }

    public function actChoosePattern(int $card_id, bool $sally) {
        $player_id = (int)$this->getActivePlayerId();
        if(!$this->checkIfTopCard($card_id)) {
            // Throw an exception to stop further processing
            throw new \BgaUserException('Invalid card choice');
        }
        if ($this->gamestate->getCurrentState($player_id) == "playerTurn" && $sally) {
            $this->gamestate->nextState("assistantAction");
        }

            $this->cards->moveCard($card_id, $this->getActivePlayerId());

            if ($sally) {
                $this->incGameStateValue("sally_counter", 1);
                $this->notify->all("plan", clienttranslate('${player_name} uses assistant [Sneaky Sally(192)] to draw pattern card [${card_name}(${card_arg})]'),
                    array(
                        "player_id" => $this->getActivePlayerId(),
                        "card_arg" => $this->cards->getCard($card_id)["type_arg"],
                        "card_name" => "pattern card",
                        "player_name" => $this->getPlayerNameById((int)$this->getActivePlayerId())
                    ));

            } else {
                if (!$sally && $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id") == 192) {
                    $this->incGameStateValue("sally_counter", 1);
                }
            $this->notify->all("plan", clienttranslate('${player_name} draws pattern card [${card_name}(${card_arg})]'),
                    array(
                        "player_id" => $this->getActivePlayerId(),
                        "card_arg" => $this->cards->getCard($card_id)["type_arg"],
                        "card_name" => "pattern card",
                        "player_name" => $this->getPlayerNameById((int)$this->getActivePlayerId())
                    ));

            }

            $target = "player-table-" . $this->getActivePlayerId();    
            $this->notify->all("animation", "", ["player_id"=> $this->getActivePlayerId(),"animation"=>[$card_id => ["card_id"=>$card_id, "target"=>$target, "loc"=>0, "flip"=>0]]]);

            $this->refillPatternArea(true);
            if (!$sally) {
                $this->gamestate->nextState("nextPlayer");
            } else if ($this->getGameStateValue("sally_counter") < 3 && $this->getGameStateValue("sally_only_used_pattern")==0) {
                $this->notify->player($player_id, "sally", "", $this->argPlan());
                $this->gamestate->nextState("nextPlayer");
            } else {
                $this->setGameStateValue("sally_counter", 0);
                $this->setGameStateValue("turn_counter", 5);
                $this->gamestate->nextState("nextPlayer");
            }
    }


    public function actPlaceBlocks(#[JsonParam(associative: true)] mixed $args, bool $billy, bool $gladys=false) {
        $player_id = (int)$this->getActivePlayerId();
        $assis = $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id");

        if ($billy && $assis != 193) {
            throw new \BgaUserException(_('You don\'t have this assistant'));
        }

        if ($assis == 192 && $this->getGameStateValue("sally_only_used_pattern") == 0 && $this->getGameStateValue("turn_counter") == 2) {
            throw new \BgaUserException(_('You may only activate assistant or pass'));
        }

        
        if (!$this->validatePlayerCards($args, $billy, $gladys)) {
            throw new \BgaUserException(_('Invalid card placement'));
        }

        if ($assis == 192) {
            $this->setGameStateValue("use_assistant", 0);
            $this->setGameStateValue("sally_only_used_pattern", 1);
        }
        
        $animation = [];
        $animation["animation"] = [];

        foreach ($args as $arg) {
            $this->cards->moveCard($arg["cardId"], $player_id, $arg["locationId"]);

            $card_id = $arg["cardId"];
            $loc = $arg["locationId"];
            $target = "player-table-" . $this->getActivePlayerId();
            
            $animation["animation"][$card_id] = ["card_id"=>$card_id, "target"=>$target, "loc"=>$loc, "flip"=>0];
        }
        $animation["player_id"] = $this->getActivePlayerId();

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

        if ($billy) {
            $this->setGameStateValue("use_assistant", 0);
            $this->notify->All("chooseTiles",
            clienttranslate('${player_name} uses assistant [${card_name}(${assistant_arg})] to add ${card_arg} to quilt'),
            [
                "animation"=> $animation["animation"],
                "player_name" => $this->getActivePlayerName(),
                "card_name" => $this->quilt_cards[193]["name"],
                "card_arg" => json_encode($card_data), // This will be substituted as a string
                "assistant_arg" => 193
            ]);
        } else if ($gladys) {
            $this->notify->All("chooseTiles",
            clienttranslate('${player_name} uses assistant [${card_name}(${assistant_arg})] to add ${card_arg} to quilt'),
            [
                "animation"=> $animation["animation"],
                "player_name" => $this->getActivePlayerName(),
                "card_name" => $this->quilt_cards[193]["name"],
                "card_arg" => json_encode($card_data), // This will be substituted as a string
                "assistant_arg" => 199
            ]);
        }
        else {
            $this->notify->All("chooseTiles",
                clienttranslate('${player_name} adds ${card_arg} to quilt'),
                [
                    "animation"=> $animation["animation"],
                    "player_name" => $this->getActivePlayerName(),
                    "card_arg" => json_encode($card_data) // This will be substituted as a string
            ]);
        }

        $this->DbQuery("
            UPDATE player
            SET mydata = '".json_encode($animation)."'
            WHERE player_id = $player_id
        ");        
        // $this->notify->all("animation", "", $animation);
        $this->gamestate->nextState("transition");

    }

    function actBlockRefill() {
        $this->gamestate->nextState("refill");
    }

    public function actReturnBlocks(#[JsonParam(associative: null)] mixed $cards) {
        $player_id = (int)$this->getActivePlayerId();
        $assis = $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id");

        if ($assis == 192 && $this->getGameStateValue("sally_only_used_pattern") == 0 && $this->getGameStateValue("turn_counter") == 2) {
            throw new \BgaUserException(_('You may only activate assistant or pass'));
        }
        if ($assis == 192) {
            $this->setGameStateValue("use_assistant", 0);
            $this->setGameStateValue("sally_only_used_pattern", 1);
        }
    
        // Clear previous entries
        $this->DbQuery("DELETE FROM return_blocks");
    
        foreach ($cards as $card_id) {
            $card = $this->cards->getCard(card_id: $card_id);
            if ($card && $card["location"] == $player_id) {
                $this->DbQuery("INSERT INTO return_blocks (card_id, location) VALUES ({$card['id']}, '{$card['location']}')");
            } else {
                throw new \BgaUserException('One or more selected cards don\'t exist');
            }
        }
    
        // Reset the internal pointer to the first card
        $this->setGameStateValue("return_block_index", 0);
    
        $this->gamestate->nextState("itterate");
    }
    
    
    public function actConfirmReturn(int $loc) {
    
        if (!($loc > 223 && $loc < 244)) {
            throw new \BgaUserException('Invalid placement');
        }

        if (!$this->checkReturnAdjacency($loc)) {
            throw new \BgaUserException('Must be adjacent to other cards');
        }
    
        $player_id = (int)$this->getActivePlayerId();
        $index = $this->getGameStateValue("return_block_index");
    
        $tile = $this->getObjectFromDB("SELECT * FROM return_blocks ORDER BY id ASC LIMIT 1 OFFSET $index");
    
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

        $this->notify->all("animation", "", ["player_id"=> $this->getActivePlayerId(),"animation"=>[$card_id => ["card_id"=>$card_id, "target"=>$target, "loc"=>$loc, "flip"=>0]]]);
        $this->gamestate->nextState("checkReturn");
        
    }


    public function actShiftQuilt(string $shiftDirection) {
        $left = [208, 212, 216, 220];
        $top = [208, 209, 210, 211];
        $right = [211, 215, 219, 223];
        $bottom = [220, 221, 222, 223];
        $shift = true;
        $allCards = $this->cards->getCardsOfTypeInLocation("back", null, $this->getCurrentPlayerId(), null);
        $animation = [];

        if ($shiftDirection == "left") {
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

        } else if ($shiftDirection == "up") {
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
        } else if ($shiftDirection == "right") {
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
        } else if ($shiftDirection == "down") {
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
        $player_id = $this->getActivePlayerId();
        return [
            "use_assistant" => $this->getGameStateValue("use_assistant"),
            "turn_num" => $this->getGameStateValue("turn_counter"),
            "last_turn" => $this->getUniqueValueFromDB("SELECT COUNT(*) FROM player WHERE endTriggered = 1") > 0
        ];
    }

    public function argNextPlayer() {
        $matches = [];
        foreach($this->loadPlayersBasicInfos() as $player => $data) {
            $matches[$player] = $this->getPatternPoints($player);
        }
        return ["matches" => $matches];
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
        $cards["card"] = $this->getObjectFromDB("SELECT card_id, location FROM return_blocks ORDER BY id ASC LIMIT 1 OFFSET $index");
    
        return $cards;
    }

    public function stPostEnd() {
        $points = $this->calculatePoints();
        $this->notify->all("endScores", "",[ 'endScores' => $points ]);
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
        $players = array_keys($this->loadPlayersBasicInfos());

        $total = 0;

        foreach($players as $id) {
            $cards = $this->cards->getCardsOfTypeInLocation("back", null, $id, null);

            $total += count($cards);
        }

        return $total / (count($players) * 16)*100;
    }

    /**
     * Game state action, example content.
     *
     * The action method of state `nextPlayer` is called everytime the current game state is set to `nextPlayer`.
     */


    function actTurnCard(int $loc) {
        #turn over card

        $result = $this->refillPatternArea();
         // Place the card at chosen location
        $id = $result["card_id"];

        $this->executeFlip($id, $loc);

        $this->gamestate->nextState("loop");
    }

    function actSlideValidate(int $loc) {
        $result = $this->refillPatternArea();
         // Place the card at chosen location
        $id = $result["card_id"];

        $this->cards->moveCard( $id, "pattern_area", $loc);
        $this->notify->all("animation", "", ["player_id"=> $this->getActivePlayerId(),"animation"=>[$id=>["card_id"=>$id, "target"=>"pattern-board", "loc"=>$loc, "flip"=>0]]]);

        $this->gamestate->nextState("loop");
    }

    function stHelper()
{
    $transition = $this->getGameStateValue("which_transition");
    $result = $this->refillPatternArea();

    if ($result === null && $transition==0 && ($this->getGameStateValue("quilt_master") != 0 || $this->getPlayersNumber()!=1)) {
        // Go directly to next player
        if ($this->getGameStateValue("isQuiltTurn") == 1) {
            $this->setGameStateValue("isQuiltTurn", 0);
            $this->gamestate->nextState("turn");
        } else {
            $this->gamestate->nextState("nextPlayer");
        }
        return;
    }
}

    function argsHelper() {
        $transition = $this->getGameStateValue("which_transition");

        if ($this->getGameStateValue("quilt_master")== 0 && $this->getPlayersNumber()==1) {
            return [
                "corner"=> [229,232,239,242]
            ];
        }

        if ($transition == 2) {
            return [
                "helper" => [
                    "name" => $this->getPlayerNameById($this->getGameStateValue("main_player")),
                    "patterns" => $this->cards->getCardsOfTypeInLocation("pattern", null, $this->getActivePlayerId(), null)
                ]
            ];
        }

        // Normal "flip card" path
        $result = $this->refillPatternArea();

        if ($result === null) {
            // No cards to flip
            return [];
        }

        // Returning result makes frontend receive it on reload too
        return [
            "flip_info" => $result
        ];
    }


    function stTransition() {
        $transition = $this->getGameStateValue("which_transition");
         $player_id = $this->getActivePlayerId();
        $raw = $this->getUniqueValueFromDB("SELECT mydata FROM player WHERE player_id=$player_id");
        if ($raw === null) {
            // empty
        } else {
            $data = json_decode($raw, true);
            $data["player_id"] = $this->getActivePlayerId();
            $this->notify->all("animation", "", $data);
            $this->DbQuery("UPDATE player SET mydata = NULL WHERE player_id=$player_id");
        }



        $result = $this->refillPatternArea();

        if ($result === null && $transition==0 && ($this->getGameStateValue("quilt_master") != 0 || $this->getPlayersNumber()!=1)) {
            // Go directly to next player
            $this->gamestate->nextState("helperAction");
            return;
        } else if ($result != null && $transition==0 && ($this->getGameStateValue("quilt_master") != 0|| $this->getPlayersNumber()!=1)) {
            $this->gamestate->nextState("helperAction");
            return;
        }


        if ($transition == 1) {
            $helper = $this->getGameStateValue('helper_player');
            $this->gamestate->changeActivePlayer($helper);
            $this->setGameStateValue("which_transition", 2);
            $this->gamestate->nextState("helperAction");
        } else {
            $main = $this->getGameStateValue('main_player');
            $this->gamestate->changeActivePlayer($main);
            $this->setGameStateValue("which_transition", 0);
            $this->gamestate->nextState("nextPlayer");
        }


    }



    public function stNextPlayer(): void {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();
        $assis = $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id");
        // Give some extra time to the active player when he completed an action
        $this->giveExtraTime($player_id);

            if ($assis == 192 && $this->getGameStateValue("sally_only_used_pattern")==0) {
                $this->DbQuery("UPDATE player SET num_turns = 3 WHERE player_id = $player_id");
            } else if ($assis == 192 && $this->getGameStateValue("sally_only_used_pattern")==1) {
                $this->DbQuery("UPDATE player SET num_turns = 2 WHERE player_id = $player_id");
            }

            $turns = $this->getUniqueValueFromDB("SELECT num_turns FROM player WHERE player_id = $player_id");
            $turn_counter = $this->getGameStateValue("turn_counter");
            if ($turn_counter < $turns-1) {
                $this->setGameStateValue("turn_counter", $turn_counter + 1);
                // loop to second turn
                $this->gamestate->nextState("nextPlayer");
            } else {
                // Check end conditions and trigger last turns for other players
                $this->checkEndConditions($player_id);
                $nextPlayer = $this->getNextPlayer();
                $endTriggered = $this->getUniqueValueFromDB("SELECT endTriggered FROM player WHERE player_id = $nextPlayer");
                // add points to db
                if ($endTriggered == 1) {
                    $points = $this->calculatePoints();
                    foreach($points as $id => $data) {
                        if ($id == "999") {
                            continue;
                        }
                        $total = $data["total"];
                        if ($this->getPlayersNumber() == 1) {
                            if ($points[$id]["total"] < $points["999"]["total"]) {
                                $this->DbQuery("UPDATE player SET player_score = 0");
                            } else {
                                $this->DbQuery("UPDATE player SET player_score = $total WHERE player_id = $id");
                            }
                        } else {
                            $this->DbQuery("UPDATE player SET player_score = $total WHERE player_id = $id");
                        }
                    }            

                    $this->gamestate->nextState("postEnd");
                } else {

                    if ($this->getGameStateValue("quilting_assistants") == 1) {
                        $next = $this->getPlayerAfter((int)$this->getActivePlayerId());
                        $this->setGameStateValue("use_assistant", (int)$this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $next"));
                        $this->DbQuery("UPDATE player SET num_turns = 2 WHERE player_id = $player_id");
                    }

                    // count patterns
                    $locations = ['deck_0', 'deck_1', 'deck_2', 'deck_3'];

                    $allPatterns = [];
                    foreach ($locations as $loc) {
                        $cards = $this->cards->getCardsOfTypeInLocation("pattern", null, $loc, null);
                        $allPatterns = array_merge($allPatterns, $cards);
                    }

                    // Now: total count of pattern cards across all decks
                    $totalPatterns = count($allPatterns);                    
                    if($totalPatterns == 0) {
                        $this->DbQuery("UPDATE player SET endTriggered = 1 WHERE player_id = $player_id");
                    }
                    if ($this->getPlayersNumber() == 1 && $totalPatterns == 0) {
                        $this->gamestate->nextState("postEnd");
                    } else {
                        $this->setGameStateValue("turn_counter", 0);
                        $this->activeNextPlayer();
                        $this->setGameStateValue("sally_only_used_pattern", 0);
                        $this->setGameStateValue("sally_counter", 0);
                        if ($this->getPlayersNumber() == 1) {
                            $this->quiltMasterTurn();
                            $this->setGameStateValue("isQuiltTurn", 1);
                           $this->gamestate->nextState("transition");  
                        } else {
                           $this->gamestate->nextState("nextPlayer");  
                        }
                    }
                    
                    }
            }
            // Go to another gamestate
            // Here, we would detect if the game is over, and in this case use "endGame" transition instead 
            // $this->gamestate->nextState("nextPlayer");
    }

    public function stCheckReturn() {
        $index = $this->getGameStateValue("return_block_index");

        // Check if there are more cards left to process
        $remaining = $this->getUniqueValueFromDB("SELECT COUNT(*) FROM return_blocks");
    
        if ($index + 1 < $remaining) {
            // Increase the pointer
            $this->setGameStateValue("return_block_index", $index + 1);
            $this->gamestate->nextState("returnBlock");  // Continue the loop
        } else {
            $this->gamestate->nextState("nextPlayer");  // Move to the next player
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

        $result["master"] = ($this->getPlayersNumber() == 1) ? $this->getGameStateValue("quilt_master") : null;

        $result["matches"] = $this->argNextPlayer();

        $result["master_num"] = ($this->getPlayersNumber() == 1) ? $this->getUniqueValueFromDB("SELECT COUNT(*) FROM card WHERE card_location = 'quiltMaster'") : null;

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

        // set counter for single player
        if ($this->getPlayersNumber() == 1) {
            # check if game with advanced options
            if ($this->getGameStateValue("patches") == 1 || $this->getGameStateValue("symmetry") == 1 || $this->getGameStateValue("quilting_assistants") == 1) {
                $num_turns = 1;
            } else {
                $num_turns = 0;
            }
            $solo_option = $this->getGameStateValue("solo_options");
            $num_turns += $solo_option;

            $this->setGameStateValue("quilt_master_turns", $num_turns);
        }



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

        $this->refillPatternArea(true);


        if ($this->getPlayersNumber() == 1) {
            $assistants = [192,194,196,198];

            // $assistants = [196];
        } else {
            $assistants = [192,194,196,198,200];
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
            $this->setGameStateValue("use_assistant", 0);
            // send the arg for single player for the quilt master
            if ($this->getPlayersNumber() == 1) {
                $this->gamestate->nextState("helper");
            } else {
                $this->gamestate->nextState("next");
            }

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
                    $this->actPass();
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
            $points = "-";
        }
        return $points;
    }
    function getPatternPoints($player_id) {
        if ($this->getGameStateValue("game_variants") == 1 || $this->getPlayersNumber() == 1) {
            $quilt = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);
            $patterns = $this->cards->getCardsOfTypeInLocation("pattern", null, $player_id, null);
            $total = 0;
            $total_matches = [];

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
                                    array_push($match_data, ["row"=>$row, "col"=>$col]);
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
                $m = [];
                // var_dump($matches);
                $cards = $this->cards->getCardsOfTypeInLocation("back", null, $player_id, null);
                foreach(array_values($matches) as $l) {
                    foreach(array_values($l) as $g) {
                        foreach($cards as $id => $rest) {
                            $r = $this->quilt_cards[$rest["location_arg"]]["row"]-1;
                            $c = $this->quilt_cards[$rest["location_arg"]]["col"]-1;
                            // var_dump($l);
                            if($g["row"]==$r && $g["col"]==$c) {
                                array_push($m, $id);
                                break;
                         }
                        }
                    }
                    $total_matches[$patternCardId][] = $m;
                    $m = [];
                }
            }       
            return ["total"=> $total, "matches"=>$total_matches];
        } else {
            return ["total"=> "-", "matches"=>null];
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
            return "-";
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
            return "-";
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
            return "-";
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
        $player_score = ['premium'   => $this->getPremiumPoints($player_id),
                'patterns'  => $this->getPatternPoints($player_id)["total"],
                'completed' => $this->getCompletedQuiltPoints($player_id),
                'symmetry'  => $this->getSymmetryPoints($player_id),
                'patches'   => $this->getPatchesPoints($player_id)];

        $sum = 0;
        foreach($player_score as $i => $val) {
            if ($val != "-") {
                $sum += $val;
            }
        }

        $player_score['total'] = $sum;
        $scores[$player_id] = $player_score;
    }

    if ($this->getPlayersNumber() == 1) {
        $patterns = $this->cards->getCardsOfTypeInLocation("pattern", null, "quiltMaster", null);
        $total = 0;

        foreach ($patterns as $id => $card) {
            $total += intval($this->quilt_cards[$card["type_arg"]]["points"]);
        }

        $scores["999"] = [
            'premium'   => "-",
            'patterns'  => $total,
            'completed' => "-",
            'symmetry'  => "-",
            'patches'   => "-",
            'total' => $total
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
            if(( count(array_keys($cardsInQuilt)) >= 16) && $this->getUniqueValueFromDB("SELECT COUNT(*) FROM player WHERE endTriggered = 1;") != 1) {
                $this->DbQuery("UPDATE player SET endTriggered = 1 WHERE player_id = $player_id");
            }
        }
    }

    function validatePlayerCards($args, $billy, $gladysAssis) {
        $player_id = $this->getActivePlayerId();
        $allCardsPlayerTable = $this->cards->getCardsOfTypeInLocation("back", null, $this->getActivePlayerId(), null);
        foreach ($args as $arg) {
            $card_id = $arg["cardId"];
            $loc = $arg["locationId"];
            // validation
            if (!$this->cards->getCard($card_id)) {
                // var_dump("#1");
                return false;
            }
            if ($this->cards->getCard($card_id)["location"] != "pattern_area") {
                // var_dump("#2");
                return false;
            } if ((count($args) < 2 || count($args) > 3) && !$billy) {
                // var_dump("#3");
                return false;
            } if ((count($args) < 1 || count($args) > 4) && (!$billy || $this->getGameStateValue("use_assistant") == 0)) {
                // var_dump("#4");
                return false;
            }
             if ($this->cards->countCardInLocation($this->getActivePlayerId(), $loc) > 0) {
                // var_dump("#5");
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
    if (count($visited) != count($positions) && $this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id") != 199) {
        // var_dump("#5");
        return false;
    } else if (count($allCardsPlayerTable) == 0 && count($visited) != count($positions)) {
        return false;
    }

    // Check that at least one of the new cards is adjacent to an existing card on the player table
    $adjacentFound = false;

    if ($this->getUniqueValueFromDB("SELECT assistant FROM player WHERE player_id = $player_id") == 198) {
        $adjacentFound = true;
    }

    $gladys = [];
    for ($i=0; $i < count($positions); $i++) {
        $gladys[$i] = false;
    }
    
    foreach ($allCardsPlayerTable as $existingCard) {
        $existingPos = $this->quilt_cards[$existingCard['location_arg']];
        $erow = $existingPos['row']-1;
        $ecol = $existingPos['col']-1;

        $counter = 0;
        foreach ($positions as $newPos) {
            $nrow = $newPos['row'];
            $ncol = $newPos['col'];

            if (
                ($nrow == $erow && abs($ncol - $ecol) == 1) ||
                ($ncol == $ecol && abs($nrow - $erow) == 1)
            ) {
                $adjacentFound = true;
                $gladys[$counter] = true;
            }
            $counter++;
        }
    }

    if (in_array(false,array_values($gladys)) && count($allCardsPlayerTable) > 0 && $gladysAssis) {
        // var_dump("#60");
        return false;
    }

    if (!$adjacentFound && count($allCardsPlayerTable) > 0) {
        // var_dump("#6");
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
    

function refillPatternArea($setup = false)
{
    //
    // --- CONFIG ---
    //
    $pattern_grid = [
        213 => [208, 209, 212],
        214 => [210, 211, 215],
        217 => [216, 220, 221],
        218 => [219, 222, 223]
    ];

    $deck_neighbors = [
        'deck_0' => ['deck_1','deck_2','deck_3'],
        'deck_1' => ['deck_0','deck_3','deck_2'],
        'deck_2' => ['deck_3','deck_0','deck_1'],
        'deck_3' => ['deck_2','deck_1','deck_0']
    ];

    $deck_positions = [
        213 => 'deck_0',
        214 => 'deck_1',
        217 => 'deck_2',
        218 => 'deck_3'
    ];

    // Define row/col mapping for all positions
    $position_coords = [
        208 => ["row" => 2, "col" => 2],
        209 => ["row" => 2, "col" => 3],
        210 => ["row" => 2, "col" => 4],
        211 => ["row" => 2, "col" => 5],
        212 => ["row" => 3, "col" => 2],
        213 => ["row" => 3, "col" => 3],
        214 => ["row" => 3, "col" => 4],
        215 => ["row" => 3, "col" => 5],
        216 => ["row" => 4, "col" => 2],
        217 => ["row" => 4, "col" => 3],
        218 => ["row" => 4, "col" => 4],
        219 => ["row" => 4, "col" => 5],
        220 => ["row" => 5, "col" => 2],
        221 => ["row" => 5, "col" => 3],
        222 => ["row" => 5, "col" => 4],
        223 => ["row" => 5, "col" => 5],
        224 => ["row" => 1, "col" => 2],
        225 => ["row" => 1, "col" => 3],
        226 => ["row" => 1, "col" => 4],
        227 => ["row" => 1, "col" => 5],
        228 => ["row" => 1, "col" => 6],
        229 => ["row" => 2, "col" => 6],
        230 => ["row" => 3, "col" => 6],
        231 => ["row" => 4, "col" => 6],
        232 => ["row" => 5, "col" => 6],
        233 => ["row" => 6, "col" => 6],
        234 => ["row" => 6, "col" => 5],
        235 => ["row" => 6, "col" => 4],
        236 => ["row" => 6, "col" => 3],
        237 => ["row" => 6, "col" => 2],
        238 => ["row" => 6, "col" => 1],
        239 => ["row" => 5, "col" => 1],
        240 => ["row" => 4, "col" => 1],
        241 => ["row" => 3, "col" => 1],
        242 => ["row" => 2, "col" => 1],
        243 => ["row" => 1, "col" => 1]
    ];

    //
    // --- PREPARE DECK AVAILABILITY ---
    //
    $available = [];
    foreach ($deck_positions as $pos => $deck_id) {
        $available[$deck_id] = $this->cards->getCardsInLocation($deck_id);
    }

    //
    // --------------------------------------------
    // STEP A: Refill PATTERN SPOTS around each deck
    // --------------------------------------------
    //

    $empty_spots = [];

    foreach ($pattern_grid as $deck_pos => $locs) {
        foreach ($locs as $loc) {
            if ($this->cards->countCardInLocation('pattern_area', $loc) == 0) {
                $empty_spots[] = $loc;
            }
        }
    }

    foreach ($empty_spots as $loc) {

        //
        // Find which deck this pattern slot belongs to
        //
        $deck_id = null;
        foreach ($pattern_grid as $deck_pos => $spots) {
            if (in_array($loc, $spots)) {
                $deck_id = $deck_positions[$deck_pos];
                break;
            }
        }
        if ($deck_id === null) continue;

        //
        // Find a usable source deck
        //
        $source = null;
        if (!empty($available[$deck_id])) {
            $source = $deck_id;
        } else {
            foreach ($deck_neighbors[$deck_id] as $n) {
                if (!empty($available[$n])) {
                    $source = $n;
                    break;
                }
            }
        }

        if ($source === null) continue;

        //
        // Pull card "virtually"
        //
        $card_id = array_key_first($available[$source]);
        $card = $available[$source][$card_id];
        unset($available[$source][$card_id]);

        //
        // Determine possible pattern placements
        //
        $possible_spots = [];
        foreach ($pattern_grid as $deck_pos => $spots) {
            if ($deck_positions[$deck_pos] === $deck_id) {
                foreach ($spots as $s) {
                    if ($this->cards->countCardInLocation('pattern_area', $s) == 0) {
                        $possible_spots[] = $s;
                    }
                }
            }
        }

        //
        // Decide: auto-place vs state-machine choice
        //
        if ($setup || count($possible_spots) === 1) {
            $this->executeFlip($card_id, $possible_spots[0]);
            continue;
        }

        return [
            'card_id' => $card_id,
            'deck_id' => $deck_id,
            'locations' => $possible_spots
        ];
    }

    //
    // ------------------------------------------------
    // STEP B: Refill DECK POSITIONS THEMSELVES (Step 4)
    // ------------------------------------------------
    //

    foreach ($deck_positions as $pos => $deck_id) {

        // Skip: deck still has its own cards
        if (!empty($available[$deck_id])) continue;

        //
        // Search neighbors
        //
        $source = null;
        foreach ($deck_neighbors[$deck_id] as $nb) {
            if (!empty($available[$nb])) {
                $source = $nb;
                break;
            }
        }
        if ($source === null) continue;

        //
        // Pull virtual card
        //
        $card_id = array_key_first($available[$source]);
        $card = $available[$source][$card_id];
        unset($available[$source][$card_id]);

        //
        // DECK POSITION has exactly ONE possible slot
        // (its slot in pattern_area)
        //
        $possible = [$pos];

        //
        // Decide: no choice unless setup==false but >1 (never happens)
        //
        if ($setup || count($possible) === 1) {
            $this->executeFlip($card_id, $possible[0]);
            continue;
        }

        //
        // If somehow multiple choices existed:
        //
        return [
            'card_id' => $card_id,
            'deck_id' => $deck_id,
            'locations' => $possible
        ];
    }

    //
    // ------------------------------------------------
    // STEP C: VALIDATE ALL CARDS HAVE NEIGHBORS
    // ------------------------------------------------
    //
    
    // Get all cards in pattern_area
    $all_cards = $this->cards->getCardsOfTypeInLocation("back", null, "pattern_area", null);
    
    // Build a map of which positions are occupied
    $occupied_positions = [];
    foreach ($all_cards as $card) {
        $occupied_positions[$card['location_arg']] = $card['id'];
    }
    
    // Check each card for horizontal/vertical neighbors
    foreach ($all_cards as $card) {
        $loc = $card['location_arg'];
        
        // Skip if position not in our coords map
        if (!isset($position_coords[$loc])) continue;
        
        $row = $position_coords[$loc]['row'];
        $col = $position_coords[$loc]['col'];
        
        // Check for neighbors (up, down, left, right)
        $has_neighbor = false;
        foreach ($position_coords as $pos => $coords) {
            // Check if adjacent horizontally or vertically
            if (($coords['row'] == $row && abs($coords['col'] - $col) == 1) ||
                ($coords['col'] == $col && abs($coords['row'] - $row) == 1)) {
                if (isset($occupied_positions[$pos])) {
                    $has_neighbor = true;
                    break;
                }
            }
        }
        
        // If no neighbor found, find closest valid slide locations
        if (!$has_neighbor) {
            $valid_slides = [];
            
            // Check all positions where this card could slide to have a neighbor
            foreach ($position_coords as $target_pos => $target_coords) {
                // Skip if position is occupied or is current position
                if (isset($occupied_positions[$target_pos]) || $target_pos == $loc) continue;
                
                // Check if target position would have a neighbor
                $target_row = $target_coords['row'];
                $target_col = $target_coords['col'];
                
                $has_neighbor_at_target = false;
                foreach ($position_coords as $neighbor_pos => $neighbor_coords) {
                    if ($neighbor_pos == $target_pos) continue;
                    
                    // Check if adjacent horizontally or vertically
                    if (($neighbor_coords['row'] == $target_row && abs($neighbor_coords['col'] - $target_col) == 1) ||
                        ($neighbor_coords['col'] == $target_col && abs($neighbor_coords['row'] - $target_row) == 1)) {
                        // Make sure this neighbor position is occupied (and not the current card)
                        if (isset($occupied_positions[$neighbor_pos]) && $occupied_positions[$neighbor_pos] != $card['id']) {
                            $has_neighbor_at_target = true;
                            break;
                        }
                    }
                }
                
                if ($has_neighbor_at_target) {
                    // Calculate Manhattan distance from current position
                    $distance = abs($target_row - $row) + abs($target_col - $col);
                    $valid_slides[] = [
                        'pos' => $target_pos,
                        'distance' => $distance
                    ];
                }
            }
            
            if (!empty($valid_slides)) {
                // Sort by distance
                usort($valid_slides, function($a, $b) {
                    return $a['distance'] - $b['distance'];
                });
                
                // Get minimum distance
                $min_distance = $valid_slides[0]['distance'];
                
                // Keep only positions with minimum distance
                $closest_slides = [];
                foreach ($valid_slides as $slide) {
                    if ($slide['distance'] == $min_distance) {
                        $closest_slides[] = $slide['pos'];
                    }
                }
                
                // Return the card that needs to be moved and closest locations
                return [
                    'card_id' => $card['id'],
                    'current_location' => $loc,
                    'locations' => $closest_slides
                ];
            }
        }
    }

    //
    // Done
    //
    return null;
}


function executeFlip($card_id, $loc) {
    // to avoid bug for placeblocks when changing states
    $card = $this->cards->getCard($card_id);
    $cardsInLoc = $this->cards->getCardsOfTypeInLocation("back", null, "pattern_area", $loc);
    $locations = ['deck_0', 'deck_1', 'deck_2', 'deck_3'];
    $allPatterns = [];
    foreach ($locations as $deck) {
        $cards = $this->cards->getCardsOfTypeInLocation("pattern", null, $deck, $loc);
        $allPatterns = array_merge($allPatterns, $cards);
    }
    $allPatterns = array_merge($allPatterns, $cardsInLoc);

    if (count($allPatterns) == 0) {
        $current = $card['type_arg'];
        $new_type_arg = $this->quilt_cards[$current]['other_side'];
        $new_type = $this->quilt_cards[$new_type_arg]['type'];

        $this->cards->moveCard($card_id, 'pattern_area', $loc);
        $this->DbQuery("UPDATE card SET card_type_arg=$new_type_arg, card_type='$new_type' WHERE card_id=$card_id");

        $this->notify->all('animation', '', [
            "player_id"=> $this->getActivePlayerId(),
            'animation' => [
                $card_id => [
                    'card_id' => $card_id,
                    'target' => 'pattern-board',
                    'loc' => $loc,
                    'flip' => $new_type_arg
                ]
            ]
        ]);
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
