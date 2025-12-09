{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- Falconry implementation : © Sam Richardson <samedr16@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    falconry_falconry.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->
<div id="lastTurnContainer">
  <div id="last_turn" class="lastTurnMarker" style="display:none;"></div>
</div>
<div class="storage"><div id="storage-item"></div></div>
<div id="play-board">
<div id="my-score-sheet">
</div>

<div id="pattern-cont" style="display: flex; flex-direction:column;">
<div id="pattern-wrapper">
<div class="quilt-board pattern-board" id="pattern-board">
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

</div>

<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/
</script>  

{OVERALL_GAME_FOOTER}
