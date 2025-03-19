<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * FalconryCardGame implementation : © Sam Richardson <samedr16@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * FalconryCardGame game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


/*

Example:

$this->card_types = array(
    1 => array( "card_name" => ...,
                ...
              )
);

*/

// CARD SIZE IN PX (MUST BE THE SAME AS IN THE CSS FILE)
$size = 100;

// GRID GAP IN PX (MUST BE THE SAME AS IN THE CSS FILE)
$gap = 3;

$this->quilt_cards = array(
    // card 1
    0 => array("type" => "pattern","type_arg" => 0,"points" => "4","other_side" => 1,"pattern" => ["pumpkin", "A", "A"],"class" => "AAA"),
    1 => array("type" => "back","type_arg" => 1,"name" => "sunflower","points" => "1","position" => "left","color" => "green","other_side" => 0,"class" => "AAAB"),
    // card 2
    2 => array("type" => "pattern","type_arg" => 2,"points" => "3","other_side" => 3,"pattern" => ["pumpkin", "leaf"],"class" => "AAB"),
    3 => array("type" => "back","type_arg" => 3,"name" => "sunflower","points" => "0","position" => "left","color" => "yellow","other_side" => 2,"class" => "AABB"),
    // card 3
    4 => array(
        "type" => "pattern",
        "type_arg" => 4,
        "points" => "3",
        "other_side" => 5,
        "pattern" => ["pumpkin", "pumpkin"],
        "class" => "AAC"
    ),
    5 => array(
        "type" => "back",
        "type_arg" => 5,
        "name" => "sunflower",
        "points" => "0",
        "position" => "left",
        "color" => "orange",
        "other_side" => 4,
        "class" => "AACB"
    ),
    // card 4
    6 => array(
        "type" => "pattern",
        "type_arg" => 6,
        "points" => "8",
        "other_side" => 7,
        "pattern" => [["pumpkin", "pumpkin"], ["pie","pie"]],
        "class" => "AAD"
    ),
    7 => array(
        "type" => "back",
        "type_arg" => 7,
        "name" => "sunflower",
        "points" => "0",
        "position" => "left",
        "color" => "brown",
        "other_side" => 6,
        "class" => "AADB"
    ),
    // card 5
    8 => array(
        "type" => "pattern",
        "type_arg" => 8,
        "points" => "5",
        "other_side" => 9,
        "pattern" => ["A", "B", "A", "C"],
        "class" => "AAE"
    ),
    9 => array(
        "type" => "back",
        "type_arg" => 9,
        "name" => "sunflower",
        "points" => "1",
        "position" => "middle",
        "color" => "green",
        "other_side" => 8,
        "class" => "AAEB"
    ),
    // card 6
    10 => array(
        "type" => "pattern",
        "type_arg" => 10,
        "points" => "6",
        "other_side" => 11,
        "pattern" => [["A","ANY", "A"], ["ANY", "ANY", "ANY"], ["A","ANY", "A"]],
        "class" => "AAF"
    ),
    11 => array(
        "type" => "back",
        "type_arg" => 11,
        "name" => "sunflower",
        "points" => "1",
        "position" => "middle",
        "color" => "yellow",
        "other_side" => 10,
        "class" => "AAFB"
    ),
    // card 7
    12 => array(
        "type" => "pattern",
        "type_arg" => 12,
        "points" => "4",
        "other_side" => 13,
        "pattern" => ["pumpkin", "A", "B"],
        "class" => "AAG"
    ),
    13 => array(
        "type" => "back",
        "type_arg" => 13,
        "name" => "sunflower",
        "points" => "1",
        "position" => "middle",
        "color" => "orange",
        "other_side" => 12,
        "class" => "AAGB"
    ),
    // card 8
    14 => array(
        "type" => "pattern",
        "type_arg" => 14,
        "points" => "3",
        "other_side" => 15,
        "pattern" => ["pumpkin", "acorn"],
        "class" => "AAH"
    ),
    15 => array(
        "type" => "back",
        "type_arg" => 15,
        "name" => "sunflower",
        "points" => "0",
        "position" => "middle",
        "color" => "brown",
        "other_side" => 14,
        "class" => "AAHB"
    ),
    // card 9
    16 => array(
        "type" => "pattern",
        "type_arg" => 16,
        "points" => "3",
        "other_side" => 17,
        "pattern" => ["pumpkin", "apple"],
        "class" => "AAI"
    ),
    17 => array(
        "type" => "back",
        "type_arg" => 17,
        "name" => "sunflower",
        "points" => "0",
        "position" => "right",
        "color" => "green",
        "other_side" => 16,
        "class" => "AAIB"
    ),
    // card 10
    18 => array(
        "type" => "pattern",
        "type_arg" => 18,
        "points" => "6",
        "other_side" => 19,
        "pattern" => [["pumpkin", "A"], ["B","C"]],
        "class" => "AAJ"
    ),
    19 => array(
        "type" => "back",
        "type_arg" => 19,
        "name" => "sunflower",
        "points" => "1",
        "position" => "right",
        "color" => "yellow",
        "other_side" => 18,
        "class" => "AAJB"
    ),
    // card 11
    20 => array(
        "type" => "pattern",
        "type_arg" => 20,
        "points" => "6",
        "other_side" => 21,
        "pattern" => ["pumkin", "A", "B", "C"],
        "class" => "AAK"
    ),
    21 => array(
        "type" => "back",
        "type_arg" => 21,
        "name" => "sunflower",
        "points" => "1",
        "position" => "right",
        "color" => "orange",
        "other_side" => 20,
        "class" => "AAKB"
    ),
    // card 12
    22 => array(
        "type" => "pattern",
        "type_arg" => 22,
        "points" => "7",
        "other_side" => 23,
        "pattern" => [["A","B", "ANY"], ["B", "ANY", "ANY"], ["ANY","ANY", "A"]],
        "class" => "AAL"
    ),
    23 => array(
        "type" => "back",
        "type_arg" => 23,
        "name" => "sunflower",
        "points" => "1",
        "position" => "right",
        "color" => "brown",
        "other_side" => 22,
        "class" => "AALB"
    ),
    // card 13
    24 => array(
        "type" => "pattern",
        "type_arg" => 24,
        "points" => "4",
        "other_side" => 25,
        "pattern" => ["sunflower", "A", "A"],
        "class" => "AAM"
    ),
    25 => array(
        "type" => "back",
        "type_arg" => 25,
        "name" => "leaf",
        "points" => "1",
        "position" => "left",
        "color" => "green",
        "other_side" => 24,
        "class" => "AAMB"
    ),
    // card 14
    26 => array(
        "type" => "pattern",
        "type_arg" => 26,
        "points" => "3",
        "other_side" => 27,
        "pattern" => ["sunflower", "corn"],
        "class" => "AAN"
    ),
    27 => array(
        "type" => "back",
        "type_arg" => 27,
        "name" => "leaf",
        "points" => "0",
        "position" => "left",
        "color" => "yellow",
        "other_side" => 26,
        "class" => "AANB"
    ),
    // card 15
    28 => array(
        "type" => "pattern",
        "type_arg" => 28,
        "points" => "3",
        "other_side" => 29,
        "pattern" => ["sunflower", "sunflower"],
        "class" => "AAO"
    ),
    29 => array(
        "type" => "back",
        "type_arg" => 29,
        "name" => "leaf",
        "points" => "0",
        "position" => "left",
        "color" => "orange",
        "other_side" => 28,
        "class" => "AAOB"
    ),
    // card 16
    30 => array(
        "type" => "pattern",
        "type_arg" => 30,
        "points" => "8",
        "other_side" => 31,
        "pattern" => [["sunflower", "sunflower"], ["acorn","acorn"]],
        "class" => "AAP"
    ),
    31 => array(
        "type" => "back",
        "type_arg" => 31,
        "name" => "leaf",
        "points" => "0",
        "position" => "left",
        "color" => "brown",
        "other_side" => 30,
        "class" => "AAPB"
    ),
    // card 17
    32 => array(
        "type" => "pattern",
        "type_arg" => 32,
        "points" => "5",
        "other_side" => 33,
        "pattern" => ["A", "B", "B", "C"],
        "class" => "AAQ"
    ),
    33 => array(
        "type" => "back",
        "type_arg" => 33,
        "name" => "leaf",
        "points" => "1",
        "position" => "middle",
        "color" => "green",
        "other_side" => 32,
        "class" => "AAQB"
    ),
    // card 18
    34 => array(
        "type" => "pattern",
        "type_arg" => 34,
        "points" => "6",
        "other_side" => 35,
        "pattern" => [["A","ANY", "A"], ["ANY", "ANY", "ANY"], ["A","ANY", "B"]],
        "class" => "AAR"
    ),
    35 => array(
        "type" => "back",
        "type_arg" => 35,
        "name" => "leaf",
        "points" => "1",
        "position" => "middle",
        "color" => "yellow",
        "other_side" => 34,
        "class" => "AARB"
    ),
    // card 19
    36 => array(
        "type" => "pattern",
        "type_arg" => 36,
        "points" => "4",
        "other_side" => 37,
        "pattern" => ["sunflower", "A", "B"],
        "class" => "AAS"
    ),
    37 => array(
        "type" => "back",
        "type_arg" => 37,
        "name" => "leaf",
        "points" => "1",
        "position" => "middle",
        "color" => "orange",
        "other_side" => 36,
        "class" => "AASB"
    ),
    // card 20
    38 => array(
        "type" => "pattern",
        "type_arg" => 38,
        "points" => "3",
        "other_side" => 39,
        "pattern" => ["sunflower", "cottage"],
        "class" => "AAT"
    ),
    39 => array(
        "type" => "back",
        "type_arg" => 39,
        "name" => "leaf",
        "points" => "0",
        "position" => "middle",
        "color" => "brown",
        "other_side" => 38,
        "class" => "AATB"
    ),
    // card 21
    40 => array(
        "type" => "pattern",
        "type_arg" => 40,
        "points" => "3",
        "other_side" => 41,
        "pattern" => ["sunflower", "pumpkin"],
        "class" => "AAU"
    ),
    41 => array(
        "type" => "back",
        "type_arg" => 41,
        "name" => "leaf",
        "points" => "0",
        "position" => "right",
        "color" => "green",
        "other_side" => 40,
        "class" => "AAUB"
    ),
    // card 22
    42 => array(
        "type" => "pattern",
        "type_arg" => 42,
        "points" => "6",
        "other_side" => 43,
        "pattern" => [["sunflower", "A"], ["B","C"]],
        "class" => "AAV"
    ),
    43 => array(
        "type" => "back",
        "type_arg" => 43,
        "name" => "leaf",
        "points" => "1",
        "position" => "right",
        "color" => "yellow",
        "other_side" => 42,
        "class" => "AAVB"
    ),
    // card 23
    44 => array(
        "type" => "pattern",
        "type_arg" => 44,
        "points" => "6",
        "other_side" => 45,
        "pattern" => ["sunflower", "A", "B", "C"],
        "class" => "AAW"
    ),
    45 => array(
        "type" => "back",
        "type_arg" => 45,
        "name" => "leaf",
        "points" => "1",
        "position" => "right",
        "color" => "orange",
        "other_side" => 44,
        "class" => "AAWB"
    ),
    // card 24
    46 => array(
        "type" => "pattern",
        "type_arg" => 46,
        "points" => "8",
        "other_side" => 47,
        "pattern" => [["ANY","A", "ANY"], ["B", "ANY", "B"], ["ANY","C", "ANY"]],
        "class" => "AAX"
    ),
    47 => array(
        "type" => "back",
        "type_arg" => 47,
        "name" => "leaf",
        "points" => "1",
        "position" => "right",
        "color" => "brown",
        "other_side" => 46,
        "class" => "AAXB"
    ),
    // card 25
    48 => array(
        "type" => "pattern",
        "type_arg" => 48,
        "points" => "4",
        "other_side" => 49,
        "pattern" => ["leaf", "A", "A"],
        "class" => "AAY"
    ),
    49 => array(
        "type" => "back",
        "type_arg" => 49,
        "name" => "leaf",
        "points" => "1",
        "position" => "left",
        "color" => "green",
        "other_side" => 48,
        "class" => "AAYB"
    ),
    // card 26
    50 => array(
        "type" => "pattern",
        "type_arg" => 50,
        "points" => "3",
        "other_side" => 51,
        "pattern" => ["leaf", "pie"],
        "class" => "AAZ"
    ),
    51 => array(
        "type" => "back",
        "type_arg" => 51,
        "name" => "corn",
        "points" => "0",
        "position" => "left",
        "color" => "yellow",
        "other_side" => 50,
        "class" => "AAZB"
    ),
    // card 27
    52 => array(
        "type" => "pattern",
        "type_arg" => 52,
        "points" => "3",
        "other_side" => 53,
        "pattern" => ["leaf", "leaf"],
        "class" => "ABA"
    ),
    53 => array(
        "type" => "back",
        "type_arg" => 53,
        "name" => "corn",
        "points" => "0",
        "position" => "left",
        "color" => "orange",
        "other_side" => 52,
        "class" => "ABAB"
    ),
    // card 28
    54 => array(
        "type" => "pattern",
        "type_arg" => 54,
        "points" => "8",
        "other_side" => 55,
        "pattern" => [["leaf", "leaf"], ["cottage","cottage"]],
        "class" => "ABB"
    ),
    55 => array(
        "type" => "back",
        "type_arg" => 55,
        "name" => "corn",
        "points" => "0",
        "position" => "left",
        "color" => "brown",
        "other_side" => 54,
        "class" => "ABBB"
    ),
    // card 29
    56 => array(
        "type" => "pattern",
        "type_arg" => 56,
        "points" => "5",
        "other_side" => 57,
        "pattern" => ["A", "B", "C", "A"],
        "class" => "ABC"
    ),
    57 => array(
        "type" => "back",
        "type_arg" => 57,
        "name" => "corn",
        "points" => "1",
        "position" => "middle",
        "color" => "green",
        "other_side" => 56,
        "class" => "ABCB"
    ),
    // card 30
    58 => array(
        "type" => "pattern",
        "type_arg" => 58,
        "points" => "6",
        "other_side" => 59,
        "pattern" => [["A","ANY", "A"], ["ANY", "ANY", "ANY"], ["B","ANY", "B"]],
        "class" => "ABD"
    ),
    59 => array(
        "type" => "back",
        "type_arg" => 59,
        "name" => "corn",
        "points" => "1",
        "position" => "middle",
        "color" => "yellow",
        "other_side" => 58,
        "class" => "ABDB"
    ),
    // card 31
    60 => array(
        "type" => "pattern",
        "type_arg" => 60,
        "points" => "4",
        "other_side" => 61,
        "pattern" => ["leaf", "A", "B"],
        "class" => "ABE"
    ),
    61 => array(
        "type" => "back",
        "type_arg" => 61,
        "name" => "corn",
        "points" => "1",
        "position" => "middle",
        "color" => "orange",
        "other_side" => 60,
        "class" => "ABEB"
    ),
    // card 32
    62 => array(
        "type" => "pattern",
        "type_arg" => 62,
        "points" => "3",
        "other_side" => 63,
        "pattern" => ["leaf", "apple"],
        "class" => "ABF"
    ),
    63 => array(
        "type" => "back",
        "type_arg" => 63,
        "name" => "corn",
        "points" => "0",
        "position" => "middle",
        "color" => "brown",
        "other_side" => 62,
        "class" => "ABFB"
    ),
    // card 33
    64 => array(
        "type" => "pattern",
        "type_arg" => 64,
        "points" => "3",
        "other_side" => 65,
        "pattern" => ["leaf", "sunflower"],
        "class" => "ABG"
    ),
    65 => array(
        "type" => "back",
        "type_arg" => 65,
        "name" => "corn",
        "points" => "0",
        "position" => "right",
        "color" => "green",
        "other_side" => 64,
        "class" => "ABGB"
    ),
    // card 34
    66 => array(
        "type" => "pattern",
        "type_arg" => 66,
        "points" => "6",
        "other_side" => 67,
        "pattern" => [["leaf", "A"], ["A","A"]],
        "class" => "ABH"
    ),
    67 => array(
        "type" => "back",
        "type_arg" => 67,
        "name" => "corn",
        "points" => "1",
        "position" => "right",
        "color" => "yellow",
        "other_side" => 66,
        "class" => "ABHB"
    ),
    // card 35
    68 => array(
        "type" => "pattern",
        "type_arg" => 68,
        "points" => "6",
        "other_side" => 69,
        "pattern" => ["leaf", "A", "A", "A"],
        "class" => "ABI"
    ),
    69 => array(
        "type" => "back",
        "type_arg" => 69,
        "name" => "corn",
        "points" => "1",
        "position" => "right",
        "color" => "orange",
        "other_side" => 68,
        "class" => "ABIB"
    ),
    // card 36
    70 => array(
        "type" => "pattern",
        "type_arg" => 70,
        "points" => "7",
        "other_side" => 71,
        "pattern" => [["A","A", "ANY"], ["A", "ANY", "ANY"], ["ANY","ANY", "A"]],
        "class" => "ABJ"
    ),
    71 => array(
        "type" => "back",
        "type_arg" => 71,
        "name" => "corn",
        "points" => "1",
        "position" => "right",
        "color" => "brown",
        "other_side" => 70,
        "class" => "ABJB"
    ),
    // card 37
    72 => array(
        "type" => "pattern",
        "type_arg" => 72,
        "points" => "4",
        "other_side" => 73,
        "pattern" => ["corn", "A", "A"],
        "class" => "ABK"
    ),
    73 => array(
        "type" => "back",
        "type_arg" => 73,
        "name" => "corn",
        "points" => "1",
        "position" => "left",
        "color" => "green",
        "other_side" => 72,
        "class" => "ABKB"
    ),
    // card 38
    74 => array(
        "type" => "pattern",
        "type_arg" => 74,
        "points" => "3",
        "other_side" => 75,
        "pattern" => ["corn", "acorn"],
        "class" => "ABL"
    ),
    75 => array(
        "type" => "back",
        "type_arg" => 75,
        "name" => "pie",
        "points" => "0",
        "position" => "left",
        "color" => "yellow",
        "other_side" => 74,
        "class" => "ABLB"
    ),
    // card 39
    76 => array(
        "type" => "pattern",
        "type_arg" => 76,
        "points" => "3",
        "other_side" => 77,
        "pattern" => ["corn", "corn"],
        "class" => "ABM"
    ),
    77 => array(
        "type" => "back",
        "type_arg" => 77,
        "name" => "pie",
        "points" => "0",
        "position" => "left",
        "color" => "orange",
        "other_side" => 76,
        "class" => "ABMB"
    ),
    // card 40
    78 => array(
        "type" => "pattern",
        "type_arg" => 78,
        "points" => "8",
        "other_side" => 79,
        "pattern" => [["corn", "corn"], ["apple","apple"]],
        "class" => "ABN"
    ),
    79 => array(
        "type" => "back",
        "type_arg" => 79,
        "name" => "pie",
        "points" => "0",
        "position" => "left",
        "color" => "brown",
        "other_side" => 78,
        "class" => "ABNB"
    ),
    // card 41
    80 => array(
        "type" => "pattern",
        "type_arg" => 80,
        "points" => "5",
        "other_side" => 81,
        "pattern" => ["A", "A", "A", "B"],
        "class" => "ABO"
    ),
    81 => array(
        "type" => "back",
        "type_arg" => 81,
        "name" => "pie",
        "points" => "1",
        "position" => "middle",
        "color" => "green",
        "other_side" => 80,
        "class" => "ABOB"
    ),
    // card 42
    82 => array(
        "type" => "pattern",
        "type_arg" => 82,
        "points" => "6",
        "other_side" => 83,
        "pattern" => [["A","ANY", "B"], ["ANY", "ANY", "ANY"], ["B","ANY", "A"]],
        "class" => "ABP"
    ),
    83 => array(
        "type" => "back",
        "type_arg" => 83,
        "name" => "pie",
        "points" => "1",
        "position" => "middle",
        "color" => "yellow",
        "other_side" => 82,
        "class" => "ABPB"
    ),
    // card 43
    84 => array(
        "type" => "pattern",
        "type_arg" => 84,
        "points" => "4",
        "other_side" => 85,
        "pattern" => ["corn", "A", "B"],
        "class" => "ABQ"
    ),
    85 => array(
        "type" => "back",
        "type_arg" => 85,
        "name" => "pie",
        "points" => "1",
        "position" => "middle",
        "color" => "orange",
        "other_side" => 84,
        "class" => "ABQB"
    ),
    // card 44
    86 => array(
        "type" => "pattern",
        "type_arg" => 86,
        "points" => "3",
        "other_side" => 87,
        "pattern" => ["corn", "pumpkin"],
        "class" => "ABR"
    ),
    87 => array(
        "type" => "back",
        "type_arg" => 87,
        "name" => "pie",
        "points" => "0",
        "position" => "middle",
        "color" => "brown",
        "other_side" => 86,
        "class" => "ABRB"
    ),
    // card 45
    88 => array(
        "type" => "pattern",
        "type_arg" => 88,
        "points" => "3",
        "other_side" => 89,
        "pattern" => ["corn", "leaf"],
        "class" => "ABS"
    ),
    89 => array(
        "type" => "back",
        "type_arg" => 89,
        "name" => "pie",
        "points" => "0",
        "position" => "right",
        "color" => "green",
        "other_side" => 88,
        "class" => "ABSB"
    ),
    // card 46
    90 => array(
        "type" => "pattern",
        "type_arg" => 90,
        "points" => "6",
        "other_side" => 91,
        "pattern" => [["corn", "A"], ["A","A"]],
        "class" => "ABT"
    ),
    91 => array(
        "type" => "back",
        "type_arg" => 91,
        "name" => "pie",
        "points" => "1",
        "position" => "right",
        "color" => "yellow",
        "other_side" => 90,
        "class" => "ABTB"
    ),
    // card 47
    92 => array(
        "type" => "pattern",
        "type_arg" => 92,
        "points" => "6",
        "other_side" => 93,
        "pattern" => ["corn", "A", "A", "A"],
        "class" => "ABU"
    ),
    93 => array(
        "type" => "back",
        "type_arg" => 93,
        "name" => "pie",
        "points" => "1",
        "position" => "right",
        "color" => "orange",
        "other_side" => 92,
        "class" => "ABUB"
    ),
    // card 48
    94 => array(
        "type" => "pattern",
        "type_arg" => 94,
        "points" => "8",
        "other_side" => 95,
        "pattern" => [["ANY","A", "ANY"], ["B", "ANY", "A"], ["ANY","C", "ANY"]],
        "class" => "ABV"
    ),
    95 => array(
        "type" => "back",
        "type_arg" => 95,
        "name" => "pie",
        "points" => "1",
        "position" => "right",
        "color" => "brown",
        "other_side" => 94,
        "class" => "ABVB"
    ),
    // card 49
    96 => array(
        "type" => "pattern",
        "type_arg" => 96,
        "points" => "4",
        "other_side" => 97,
        "pattern" => ["pie", "A", "A"],
        "class" => "ABW"
    ),
    97 => array(
        "type" => "back",
        "type_arg" => 97,
        "name" => "pie",
        "points" => "1",
        "position" => "left",
        "color" => "green",
        "other_side" => 96,
        "class" => "ABWB"
    ),
    // card 50
    98 => array(
        "type" => "pattern",
        "type_arg" => 98,
        "points" => "3",
        "other_side" => 99,
        "pattern" => ["pie", "cottage"],
        "class" => "ABX"
    ),
    99 => array(
        "type" => "back",
        "type_arg" => 99,
        "name" => "apple",
        "points" => "0",
        "position" => "left",
        "color" => "yellow",
        "other_side" => 98,
        "class" => "ABXB"
    ),
    // card 51
    100 => array(
        "type" => "pattern",
        "type_arg" => 100,
        "points" => "3",
        "other_side" => 101,
        "pattern" => ["pie", "pie"],
        "class" => "ABY"
    ),
    101 => array(
        "type" => "back",
        "type_arg" => 101,
        "name" => "apple",
        "points" => "0",
        "position" => "left",
        "color" => "orange",
        "other_side" => 100,
        "class" => "ABYB"
    ),
    // card 52
    102 => array(
        "type" => "pattern",
        "type_arg" => 102,
        "points" => "8",
        "other_side" => 103,
        "pattern" => [["pie", "pie"], ["leaf","leaf"]],
        "class" => "ABZ"
    ),
    103 => array(
        "type" => "back",
        "type_arg" => 103,
        "name" => "apple",
        "points" => "0",
        "position" => "left",
        "color" => "brown",
        "other_side" => 102,
        "class" => "ABZB"
    ),
    // card 53
    104 => array(
        "type" => "pattern",
        "type_arg" => 104,
        "points" => "5",
        "other_side" => 105,
        "pattern" => ["A", "A", "B", "A"],
        "class" => "ACA"
    ),
    105 => array(
        "type" => "back",
        "type_arg" => 105,
        "name" => "apple",
        "points" => "1",
        "position" => "middle",
        "color" => "green",
        "other_side" => 104,
        "class" => "ACAB"
    ),
    // card 54
    106 => array(
        "type" => "pattern",
        "type_arg" => 106,
        "points" => "7",
        "other_side" => 107,
        "pattern" => [["A","B", "ANY"], ["ANY", "ANY", "B"], ["ANY","ANY", "A"]],
        "class" => "ACB"
    ),
    107 => array(
        "type" => "back",
        "type_arg" => 107,
        "name" => "apple",
        "points" => "1",
        "position" => "middle",
        "color" => "yellow",
        "other_side" => 106,
        "class" => "ACBB"
    ),
    // card 55
    108 => array(
        "type" => "pattern",
        "type_arg" => 108,
        "points" => "4",
        "other_side" => 109,
        "pattern" => ["pie", "A", "B"],
        "class" => "ACC"
    ),
    109 => array(
        "type" => "back",
        "type_arg" => 109,
        "name" => "apple",
        "points" => "1",
        "position" => "middle",
        "color" => "orange",
        "other_side" => 108,
        "class" => "ACCB"
    ),
    // card 56
    110 => array(
        "type" => "pattern",
        "type_arg" => 110,
        "points" => "3",
        "other_side" => 111,
        "pattern" => ["pie", "sunflower"],
        "class" => "ACD"
    ),
    111 => array(
        "type" => "back",
        "type_arg" => 111,
        "name" => "apple",
        "points" => "0",
        "position" => "middle",
        "color" => "brown",
        "other_side" => 110,
        "class" => "ACDB"
    ),
    // card 57
    112 => array(
        "type" => "pattern",
        "type_arg" => 112,
        "points" => "3",
        "other_side" => 113,
        "pattern" => ["pie", "corn"],
        "class" => "ACE"
    ),
    113 => array(
        "type" => "back",
        "type_arg" => 113,
        "name" => "apple",
        "points" => "0",
        "position" => "right",
        "color" => "green",
        "other_side" => 112,
        "class" => "ACEB"
    ),
    // card 58
    114 => array(
        "type" => "pattern",
        "type_arg" => 114,
        "points" => "6",
        "other_side" => 115,
        "pattern" => [["pie", "A"], ["A","B"]],
        "class" => "ACF"
    ),
    115 => array(
        "type" => "back",
        "type_arg" => 115,
        "name" => "apple",
        "points" => "1",
        "position" => "right",
        "color" => "yellow",
        "other_side" => 114,
        "class" => "ACFB"
    ),
    // card 59
    116 => array(
        "type" => "pattern",
        "type_arg" => 116,
        "points" => "6",
        "other_side" => 117,
        "pattern" => ["pie", "A", "A", "B"],
        "class" => "ACG"
    ),
    117 => array(
        "type" => "back",
        "type_arg" => 117,
        "name" => "apple",
        "points" => "1",
        "position" => "right",
        "color" => "orange",
        "other_side" => 116,
        "class" => "ACGB"
    ),
    // card 60
    118 => array(
        "type" => "pattern",
        "type_arg" => 118,
        "points" => "8",
        "other_side" => 119,
        "pattern" => [["ANY","A", "ANY"], ["A", "ANY", "A"], ["ANY","A", "ANY"]],
        "class" => "ACH"
    ),
    119 => array(
        "type" => "back",
        "type_arg" => 119,
        "name" => "apple",
        "points" => "1",
        "position" => "right",
        "color" => "brown",
        "other_side" => 118,
        "class" => "ACHB"
    ),
    // card 61
    120 => array(
        "type" => "pattern",
        "type_arg" => 120,
        "points" => "4",
        "other_side" => 121,
        "pattern" => ["acorn", "A", "A"],
        "class" => "ACI"
    ),
    121 => array(
        "type" => "back",
        "type_arg" => 121,
        "name" => "apple",
        "points" => "1",
        "position" => "left",
        "color" => "green",
        "other_side" => 120,
        "class" => "ACIB"
    ),
    // card 62
    122 => array(
        "type" => "pattern",
        "type_arg" => 122,
        "points" => "3",
        "other_side" => 123,
        "pattern" => ["acorn", "apple"],
        "class" => "ACJ"
    ),
    123 => array(
        "type" => "back",
        "type_arg" => 123,
        "name" => "pumpkin",
        "points" => "0",
        "position" => "left",
        "color" => "yellow",
        "other_side" => 122,
        "class" => "ACJB"
    ),
    // card 63
    124 => array(
        "type" => "pattern",
        "type_arg" => 124,
        "points" => "3",
        "other_side" => 125,
        "pattern" => ["acorn", "acorn"],
        "class" => "ACK"
    ),
    125 => array(
        "type" => "back",
        "type_arg" => 125,
        "name" => "pumpkin",
        "points" => "0",
        "position" => "left",
        "color" => "orange",
        "other_side" => 124,
        "class" => "ACKB"
    ),
    // card 64
    126 => array(
        "type" => "pattern",
        "type_arg" => 126,
        "points" => "8",
        "other_side" => 127,
        "pattern" => [["acorn", "acorn"], ["corn","corn"]],
        "class" => "ACL"
    ),
    127 => array(
        "type" => "back",
        "type_arg" => 127,
        "name" => "pumpkin",
        "points" => "0",
        "position" => "left",
        "color" => "brown",
        "other_side" => 126,
        "class" => "ACLB"
    ),
    // card 65
    128 => array(
        "type" => "pattern",
        "type_arg" => 128,
        "points" => "5",
        "other_side" => 129,
        "pattern" => ["A", "A", "B", "B"],
        "class" => "ACM"
    ),
    129 => array(
        "type" => "back",
        "type_arg" => 129,
        "name" => "pumpkin",
        "points" => "1",
        "position" => "middle",
        "color" => "green",
        "other_side" => 128,
        "class" => "ACMB"
    ),
    // card 66
    130 => array(
        "type" => "pattern",
        "type_arg" => 130,
        "points" => "6",
        "other_side" => 131,
        "pattern" => [["A","ANY", "B"], ["ANY", "ANY", "ANY"], ["B","ANY", "C"]],
        "class" => "ACN"
    ),
    131 => array(
        "type" => "back",
        "type_arg" => 131,
        "name" => "pumpkin",
        "points" => "1",
        "position" => "middle",
        "color" => "yellow",
        "other_side" => 130,
        "class" => "ACNB"
    ),
    // card 67
    132 => array(
        "type" => "pattern",
        "type_arg" => 132,
        "points" => "4",
        "other_side" => 133,
        "pattern" => ["acorn", "A", "B"],
        "class" => "ACO"
    ),
    133 => array(
        "type" => "back",
        "type_arg" => 133,
        "name" => "pumpkin",
        "points" => "1",
        "position" => "middle",
        "color" => "orange",
        "other_side" => 132,
        "class" => "ACOB"
    ),
    // card 68
    134 => array(
        "type" => "pattern",
        "type_arg" => 134,
        "points" => "3",
        "other_side" => 135,
        "pattern" => ["acorn", "leaf"],
        "class" => "ACP"
    ),
    135 => array(
        "type" => "back",
        "type_arg" => 135,
        "name" => "pumpkin",
        "points" => "0",
        "position" => "middle",
        "color" => "brown",
        "other_side" => 134,
        "class" => "ACPB"
    ),
    // card 69
    136 => array(
        "type" => "pattern",
        "type_arg" => 136,
        "points" => "3",
        "other_side" => 137,
        "pattern" => ["acorn", "pie"],
        "class" => "ACQ"
    ),
    137 => array(
        "type" => "back",
        "type_arg" => 137,
        "name" => "pumpkin",
        "points" => "0",
        "position" => "right",
        "color" => "green",
        "other_side" => 136,
        "class" => "ACQB"
    ),
    // card 70
    138 => array(
        "type" => "pattern",
        "type_arg" => 138,
        "points" => "6",
        "other_side" => 139,
        "pattern" => [["acorn", "A"], ["A","B"]],
        "class" => "ACR"
    ),
    139 => array(
        "type" => "back",
        "type_arg" => 139,
        "name" => "pumpkin",
        "points" => "1",
        "position" => "right",
        "color" => "yellow",
        "other_side" => 138,
        "class" => "ACRB"
    ),
    // card 71
    140 => array(
        "type" => "pattern",
        "type_arg" => 140,
        "points" => "6",
        "other_side" => 141,
        "pattern" => ["acorn", "A", "A", "B"],
        "class" => "ACS"
    ),
    141 => array(
        "type" => "back",
        "type_arg" => 141,
        "name" => "pumpkin",
        "points" => "1",
        "position" => "right",
        "color" => "orange",
        "other_side" => 140,
        "class" => "ACSB"
    ),
    // card 72
    142 => array(
        "type" => "pattern",
        "type_arg" => 142,
        "points" => "8",
        "other_side" => 143,
        "pattern" => [["ANY","A", "ANY"], ["A", "ANY", "A"], ["ANY","B", "ANY"]],
        "class" => "ACT"
    ),
    143 => array(
        "type" => "back",
        "type_arg" => 143,
        "name" => "pumpkin",
        "points" => "1",
        "position" => "right",
        "color" => "brown",
        "other_side" => 142,
        "class" => "ACTB"
    ),
    // card 73
    144 => array(
        "type" => "pattern",
        "type_arg" => 144,
        "points" => "4",
        "other_side" => 145,
        "pattern" => ["cottage", "A", "A"],
        "class" => "ACU"
    ),
    145 => array(
        "type" => "back",
        "type_arg" => 145,
        "name" => "cottage",
        "points" => "1",
        "position" => "left",
        "color" => "green",
        "other_side" => 144,
        "class" => "ACUB"
    ),
    // card 74
    146 => array(
        "type" => "pattern",
        "type_arg" => 146,
        "points" => "3",
        "other_side" => 147,
        "pattern" => ["cottage", "pumpkin"],
        "class" => "ACV"
    ),
    147 => array(
        "type" => "back",
        "type_arg" => 147,
        "name" => "cottage",
        "points" => "0",
        "position" => "left",
        "color" => "yellow",
        "other_side" => 146,
        "class" => "ACVB"
    ),
    // card 75
    148 => array(
        "type" => "pattern",
        "type_arg" => 148,
        "points" => "3",
        "other_side" => 149,
        "pattern" => ["cottage", "cottage"],
        "class" => "ACW"
    ),
    149 => array(
        "type" => "back",
        "type_arg" => 149,
        "name" => "cottage",
        "points" => "0",
        "position" => "left",
        "color" => "orange",
        "other_side" => 148,
        "class" => "ACWB"
    ),
    // card 76
    150 => array(
        "type" => "pattern",
        "type_arg" => 150,
        "points" => "8",
        "other_side" => 151,
        "pattern" => [["cottage", "cottage"], ["pumpkin","pumpkin"]],
        "class" => "ACX"
    ),
    151 => array(
        "type" => "back",
        "type_arg" => 151,
        "name" => "cottage",
        "points" => "0",
        "position" => "left",
        "color" => "brown",
        "other_side" => 150,
        "class" => "ACXB"
    ),
    // card 77
    152 => array(
        "type" => "pattern",
        "type_arg" => 152,
        "points" => "5",
        "other_side" => 153,
        "pattern" => ["A", "A", "B", "C"],
        "class" => "ACY"
    ),
    153 => array(
        "type" => "back",
        "type_arg" => 153,
        "name" => "cottage",
        "points" => "1",
        "position" => "middle",
        "color" => "green",
        "other_side" => 152,
        "class" => "ACYB"
    ),
    // card 78
    154 => array(
        "type" => "pattern",
        "type_arg" => 154,
        "points" => "7",
        "other_side" => 155,
        "pattern" => [["A","A", "ANY"], ["ANY", "ANY", "A"], ["ANY","ANY", "A"]],
        "class" => "ACZ"
    ),
    155 => array(
        "type" => "back",
        "type_arg" => 155,
        "name" => "cottage",
        "points" => "1",
        "position" => "middle",
        "color" => "yellow",
        "other_side" => 154,
        "class" => "ACZB"
    ),
    // card 79
    156 => array(
        "type" => "pattern",
        "type_arg" => 156,
        "points" => "4",
        "other_side" => 157,
        "pattern" => ["cottage", "A", "B"],
        "class" => "ADA"
    ),
    157 => array(
        "type" => "back",
        "type_arg" => 157,
        "name" => "cottage",
        "points" => "1",
        "position" => "middle",
        "color" => "orange",
        "other_side" => 156,
        "class" => "ADAB"
    ),
    // card 80
    158 => array(
        "type" => "pattern",
        "type_arg" => 158,
        "points" => "3",
        "other_side" => 159,
        "pattern" => ["cottage", "corn"],
        "class" => "ADB"
    ),
    159 => array(
        "type" => "back",
        "type_arg" => 159,
        "name" => "cottage",
        "points" => "0",
        "position" => "middle",
        "color" => "brown",
        "other_side" => 158,
        "class" => "ADBB"
    ),
    // card 81
    160 => array(
        "type" => "pattern",
        "type_arg" => 160,
        "points" => "3",
        "other_side" => 161,
        "pattern" => ["cottage", "acorn"],
        "class" => "ADC"
    ),
    161 => array(
        "type" => "back",
        "type_arg" => 161,
        "name" => "cottage",
        "points" => "0",
        "position" => "right",
        "color" => "green",
        "other_side" => 160,
        "class" => "ADCB"
    ),
    // card 82
    162 => array(
        "type" => "pattern",
        "type_arg" => 162,
        "points" => "6",
        "other_side" => 163,
        "pattern" => [["cottage", "A"], ["B","A"]],
        "class" => "ADD"
    ),
    163 => array(
        "type" => "back",
        "type_arg" => 163,
        "name" => "cottage",
        "points" => "1",
        "position" => "right",
        "color" => "yellow",
        "other_side" => 162,
        "class" => "ADDB"
    ),
    // card 83
    164 => array(
        "type" => "pattern",
        "type_arg" => 164,
        "points" => "6",
        "other_side" => 165,
        "pattern" => ["cottage", "A", "B", "A"],
        "class" => "ADE"
    ),
    165 => array(
        "type" => "back",
        "type_arg" => 165,
        "name" => "cottage",
        "points" => "1",
        "position" => "right",
        "color" => "orange",
        "other_side" => 164,
        "class" => "ADEB"
    ),
    // card 84
    166 => array(
        "type" => "pattern",
        "type_arg" => 166,
        "points" => "8",
        "other_side" => 167,
        "pattern" => [["ANY","A", "ANY"], ["A", "ANY", "B"], ["ANY","B", "ANY"]],
        "class" => "ADF"
    ),
    167 => array(
        "type" => "back",
        "type_arg" => 167,
        "name" => "cottage",
        "points" => "1",
        "position" => "right",
        "color" => "brown",
        "other_side" => 166,
        "class" => "ADFB"
    ),
    // card 85
    168 => array(
        "type" => "pattern",
        "type_arg" => 168,
        "points" => "4",
        "other_side" => 169,
        "pattern" => ["apple", "A", "A"],
        "class" => "ADG"
    ),
    169 => array(
        "type" => "back",
        "type_arg" => 169,
        "name" => "acorn",
        "points" => "1",
        "position" => "left",
        "color" => "green",
        "other_side" => 168,
        "class" => "ADGB"
    ),
    // card 86
    170 => array(
        "type" => "pattern",
        "type_arg" => 170,
        "points" => "3",
        "other_side" => 171,
        "pattern" => ["apple", "sunflower"],
        "class" => "ADH"
    ),
    171 => array(
        "type" => "back",
        "type_arg" => 171,
        "name" => "acorn",
        "points" => "0",
        "position" => "left",
        "color" => "yellow",
        "other_side" => 170,
        "class" => "ADHB"
    ),
    // card 87
    172 => array(
        "type" => "pattern",
        "type_arg" => 172,
        "points" => "3",
        "other_side" => 173,
        "pattern" => ["apple", "apple"],
        "class" => "ADI"
    ),
    173 => array(
        "type" => "back",
        "type_arg" => 173,
        "name" => "acorn",
        "points" => "0",
        "position" => "left",
        "color" => "orange",
        "other_side" => 172,
        "class" => "ADIB"
    ),
    // card 88
    174 => array(
        "type" => "pattern",
        "type_arg" => 174,
        "points" => "8",
        "other_side" => 175,
        "pattern" => [["apple", "apple"], ["sunflower","sunflower"]],
        "class" => "ADJ"
    ),
    175 => array(
        "type" => "back",
        "type_arg" => 175,
        "name" => "acorn",
        "points" => "0",
        "position" => "left",
        "color" => "brown",
        "other_side" => 174,
        "class" => "ADJB"
    ),
    // card 89
    176 => array(
        "type" => "pattern",
        "type_arg" => 176,
        "points" => "5",
        "other_side" => 177,
        "pattern" => ["A", "B", "A", "B"],
        "class" => "ADK"
    ),
    177 => array(
        "type" => "back",
        "type_arg" => 177,
        "name" => "acorn",
        "points" => "1",
        "position" => "middle",
        "color" => "green",
        "other_side" => 176,
        "class" => "ADKB"
    ),
    // card 90
    178 => array(
        "type" => "pattern",
        "type_arg" => 178,
        "points" => "6",
        "other_side" => 179,
        "pattern" => [["A","ANY", "B"], ["ANY", "ANY", "ANY"], ["A","ANY", "C"]],
        "class" => "ADL"
    ),
    179 => array(
        "type" => "back",
        "type_arg" => 179,
        "name" => "acorn",
        "points" => "1",
        "position" => "middle",
        "color" => "yellow",
        "other_side" => 178,
        "class" => "ADLB"
    ),
    // card 91
    180 => array(
        "type" => "pattern",
        "type_arg" => 180,
        "points" => "4",
        "other_side" => 181,
        "pattern" => ["apple", "A", "B"],
        "class" => "ADM"
    ),
    181 => array(
        "type" => "back",
        "type_arg" => 181,
        "name" => "acorn",
        "points" => "1",
        "position" => "middle",
        "color" => "orange",
        "other_side" => 180,
        "class" => "ADMB"
    ),
    // card 92
    182 => array(
        "type" => "pattern",
        "type_arg" => 182,
        "points" => "3",
        "other_side" => 183,
        "pattern" => ["apple", "pie"],
        "class" => "ADN"
    ),
    183 => array(
        "type" => "back",
        "type_arg" => 183,
        "name" => "acorn",
        "points" => "0",
        "position" => "middle",
        "color" => "brown",
        "other_side" => 182,
        "class" => "ADNB"
    ),
    // card 93
    184 => array(
        "type" => "pattern",
        "type_arg" => 184,
        "points" => "3",
        "other_side" => 185,
        "pattern" => ["apple", "cottage"],
        "class" => "ADO"
    ),
    185 => array(
        "type" => "back",
        "type_arg" => 185,
        "name" => "acorn",
        "points" => "0",
        "position" => "right",
        "color" => "green",
        "other_side" => 184,
        "class" => "ADOB"
    ),
    // card 94
    186 => array(
        "type" => "pattern",
        "type_arg" => 186,
        "points" => "6",
        "other_side" => 187,
        "pattern" => [["apple", "A"], ["B","A"]],
        "class" => "ADP"
    ),
    187 => array(
        "type" => "back",
        "type_arg" => 187,
        "name" => "acorn",
        "points" => "1",
        "position" => "right",
        "color" => "yellow",
        "other_side" => 186,
        "class" => "ADPB"
    ),
    // card 95
    188 => array(
        "type" => "pattern",
        "type_arg" => 188,
        "points" => "6",
        "other_side" => 189,
        "pattern" => ["apple", "A", "B", "A"],
        "class" => "ADQ"
    ),
    189 => array(
        "type" => "back",
        "type_arg" => 189,
        "name" => "acorn",
        "points" => "1",
        "position" => "right",
        "color" => "orange",
        "other_side" => 188,
        "class" => "ADQB"
    ),
    // card 96
    190 => array(
        "type" => "pattern",
        "type_arg" => 190,
        "points" => "8",
        "other_side" => 191,
        "pattern" => [["ANY","A", "ANY"], ["B", "ANY", "B"], ["ANY","A", "ANY"]],
        "class" => "ADR"
    ),
    191 => array(
        "type" => "back",
        "type_arg" => 191,
        "name" => "acorn",
        "points" => "1",
        "position" => "right",
        "color" => "brown",
        "other_side" => 190,
        "class" => "ADRB"
    ),
    // card 97 Sneaky Sally
    192 => array(
        "type" => "character",
        "type_arg" => 192,
        "name" => "Sneaky Sally",
        "other_side" => 193,
        "class" => "ADS"
    ),
    // card 98 Big Billy
    193 => array(
        "type" => "back",
        "type_arg" => 193,
        "name" => "Big Billy",
        "other_side" => 192,
        "class" => "ADSB"
    ),
    // card 99 Terrific Tim
    194 => array(
        "type" => "character",
        "type_arg" => 194,
        "name" => "Terrific Tim",
        "other_side" => 195,
        "class" => "ADT"
    ),
    // card 100 Swap Shop Sandra
    195 => array(
        "type" => "character",
        "type_arg" => 195,
        "name" => "Swap Shop Sandra",
        "other_side" => 194,
        "class" => "ADTB"
    ),
    // card 101 Granny Smith
    196 => array(
        "type" => "character",
        "type_arg" => 196,
        "name" => "Granny Smith",
        "other_side" => 197,
        "class" => "ADU"
    ),
    // card 102 Uncle Sam
    197 => array(
        "type" => "character",
        "type_arg" => 197,
        "name" => "Uncle Sam",
        "other_side" => 196,
        "class" => "ADUB"
    ),
    // card 103 Planning Peter
    198 => array(
        "type" => "character",
        "type_arg" => 198,
        "name" => "Planning Peter",
        "other_side" => 199,
        "class" => "ADV"
    ),
    // card 104 Gifted Gladys
    199 => array(
        "type" => "character",
        "type_arg" => 199,
        "name" => "Gifted Gladys",
        "other_side" => 198,
        "class" => "ADVB"
    ),
    // card 105 Tricky Travis
    200 => array(
        "type" => "character",
        "type_arg" => 200,
        "name" => "Tricky Travis",
        "other_side" => 201,
        "class" => "ADW"
    ),
    // card 106 Mayhem Maddie
    201 => array(
        "type" => "character",
        "type_arg" => 201,
        "name" => "Mayhem Maddie",
        "other_side" => 200,
        "class" => "ADWB"
    ),
    // card 107 Turn Reference card
    202 => array(
        "type" => "reference",
        "type_arg" => 202,
        "name" => "Turn Reference",
        "other_side" => 203,
        "class" => "ADX"
    ),
    // card 108 Scoring Reference
    203 => array(
        "type" => "reference",
        "type_arg" => 203,
        "name" => "Scoring Reference",
        "other_side" => 202,
        "class" => "ADXB"
    ),
    // card 109 Star marker
    204 => array(
        "type" => "turnMarker",
        "type_arg" => 204,
        "name" => "Turn Marker",
        "other_side" => 205,
        "class" => "ADY"
    ),
    // card 110 quilt Master
    205 => array(
        "type" => "turnMarker",
        "type_arg" => 205,
        "name" => "Quilt Master",
        "other_side" => 204,
        "class" => "ADYB"
    ),
    // card 111 Observant Omar
    206 => array(
        "type" => "character",
        "type_arg" => 206,
        "name" => "Observant Omar",
        "other_side" => 207,
        "class" => "ADZ"
    ),
    // card 112 Clever Clarissa
    207 => array("type" => "character","type_arg" => 207,"name" => "Clever Clarissa","other_side" => 206,"class" => "ADZB"),


    // PLAYER BOARD POSITIONS

    // FIRST ROW
    208 => array("x" => 0, "y" => 0, "row" => 2, "col" => 2),
    209 => array("x" => $size + $gap, "y" => 0, "row" => 2, "col" => 3),
    210 => array("x" => $size * 2 + $gap*2, "y" => 0, "row" => 2, "col" => 4),
    211 => array("x" => $size * 3 + $gap*3, "y" => 0, "row" => 2, "col" => 5),

    // SECOND ROW
    212 => array("x" => 0, "y" => $size + $gap, "row" => 3, "col" => 2),
    213 => array("x" => $size+ $gap, "y" => $size + $gap, "row" => 3, "col" => 3),
    214 => array("x" => $size * 2 + $gap*2, "y" => $size + $gap, "row" => 3, "col" => 4),
    215 => array("x" => $size * 3 + $gap*3, "y" => $size + $gap, "row" => 3, "col" => 5),

    // THIRD ROW
    216 => array("x" => 0, "y" => $size * 2 + $gap*2, "row" => 4, "col" => 2),
    217 => array("x" => $size + $gap, "y" => $size * 2 + $gap*2, "row" => 4, "col" => 3),
    218 => array("x" => $size * 2 + $gap*2, "y" => $size * 2 + $gap*2, "row" => 4, "col" => 4),
    219 => array("x" => $size * 3 + $gap*3, "y" => $size * 2 + $gap*2, "row" => 4, "col" => 5),

    // FORTH ROW
    220 => array("x" => 0, "y" => $size * 3 + $gap*3, "row" => 5, "col" => 2),
    221 => array("x" => $size + $gap, "y" => $size * 3 + $gap*3, "row" => 5, "col" => 3),
    222 => array("x" => $size * 2 + $gap*2, "y" => $size * 3 + $gap*3, "row" => 5, "col" => 4),
    223 => array("x" => $size * 3 + $gap*3, "y" => $size * 3 + $gap*3, "row" => 5, "col" => 5),


    // PATTERN AREA 4 DECK POSITIONS 213, 214, 217, 218



    // PATTERN AREA EXTRA POSITIONS (FOR RETURNING TILES)

    // TOP
    224 => array("x" => 0, "y" => -$size - $gap, "row" => 1, "col" => 2),
    225 => array("x" => $size + $gap, "y" => -$size - $gap, "row" => 1, "col" => 3),
    226 => array("x" => $size * 2 + $gap*2, "y" => -$size - $gap, "row" => 1, "col" => 4),
    227 => array("x" => $size * 3 + $gap*3, "y" => -$size - $gap, "row" => 1, "col" => 5),
    228 => array("x" => $size * 4 + $gap*4, "y" => -$size - $gap, "row" => 1, "col" => 6),

    // RIGHT
    229 => array("x" => $size * 4 + $gap*4, "y" => 0, "row" => 2, "col" => 6),
    230 => array("x" => $size * 4 + $gap*4, "y" => $size + $gap, "row" => 3, "col" => 6),
    231 => array("x" => $size * 4 + $gap*4, "y" => $size * 2 + $gap*2, "row" => 4, "col" => 6),
    232 => array("x" => $size * 4 + $gap*4, "y" => $size * 3 + $gap*3, "row" => 5, "col" => 6),
    233 => array("x" => $size * 4 + $gap*4, "y" => $size * 4 + $gap*4, "row" => 6, "col" => 6),

    // BOTTOM
    234 => array("x" => $size * 3 + $gap*3, "y" => $size * 4 + $gap*4, "row" => 6, "col" => 5),
    235 => array("x" => $size * 2 + $gap*2, "y" => $size * 4 + $gap*4, "row" => 6, "col" => 4),
    236 => array("x" => $size + $gap, "y" => $size * 4 + $gap*4, "row" => 6, "col" => 3),
    237 => array("x" => 0, "y" => $size * 4 + $gap*4, "row" => 6, "col" => 2),
    238 => array("x" => -$size - $gap, "y" => $size * 4 + $gap*4, "row" => 6, "col" => 1),

    // LEFT
    239 => array("x" => -$size - $gap, "y" => $size * 3 + $gap*3, "row" => 5, "col" => 1),
    240 => array("x" => -$size - $gap, "y" => $size * 2 + $gap*2, "row" => 4, "col" => 1),
    241 => array("x" => -$size - $gap, "y" => $size + $gap, "row" => 3, "col" => 1),
    242 => array("x" => -$size - $gap, "y" => 0, "row" => 2, "col" => 1),
    243 => array("x" => -$size - $gap, "y" => -$size - $gap, "row" => 1, "col" => 1)


);




