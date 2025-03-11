<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Falconry implementation : © Sam Richardson <samedr16@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * falconry.action.php
 *
 * Falconry main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.bgaPerformAction("myAction", ...)
 *
 */
  
  
  class action_quiltable extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( $this->isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = $this->getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "quiltable_quiltable";
            $this->trace( "Complete reinitialization of board game" );
      }
  	}
  	
  	// TODO: defines your action entry points there

    public function plan() {
        $this->setAjaxMode();
        $this->game->plan();
        $this->ajaxResponse();
    }

    public function choose() {
        $this->setAjaxMode();
        $this->game->choose();
        $this->ajaxResponse();
    }

    public function return() {
        $this->setAjaxMode();
        $this->game->return();
        $this->ajaxResponse();
    }

    public function back() {
        $this->setAjaxMode();
        $this->game->back();
        $this->ajaxResponse();
    }

    public function choosePattern() {
        $this->setAjaxMode();
        $card_id = $this->getArg("card", AT_posint, true);
        $this->game->choosePattern($card_id);
        $this->ajaxResponse();
    }

    public function placeBlocks() {
        $this->setAjaxMode();
        $args = $this->getArg('actionArgs', AT_json, true);
        $this->validateJSonAlphaNum($args, 'actionArgs');
        $this->game->placeBlocks($args);
        $this->ajaxResponse();
    }

    public function validateJSonAlphaNum($value, $argName = 'unknown')
 {
   if (is_array($value)) {
     foreach ($value as $key => $v) {
       $this->validateJSonAlphaNum($key, $argName);
       $this->validateJSonAlphaNum($v, $argName);
     }
     return true;
   }
   if (is_int($value)) {
     return true;
   }
   $bValid = preg_match("/^[_0-9a-zA-Z- ]*$/", $value) === 1;
   if (!$bValid) {
     throw new BgaSystemException("Bad value for: $argName", true, true, FEX_bad_input_argument);
   }
   return true;
 }


    /*
    
    Example:
  	
    public function myAction()
    {
        $this->setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "bgaPerformAction" method
        $arg1 = $this->getArg( "myArgument1", AT_posint, true );
        $arg2 = $this->getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        $this->ajaxResponse( );
    }
    
    */

  }
  

