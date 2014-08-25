<?php

include __DIR__ . '/Sudoku.class.php';

use sudokusolver\Sudoku;

$Sudoku = new Sudoku(
    array(
        array(0, 3, 4, 0, 0, 2, 1, 7, 8),
        array(0, 0, 0, 0, 1, 0, 0, 0, 0),
        array(0, 0, 1, 5, 0, 0, 4, 0, 6),
        array(0, 0, 0, 7, 0, 0, 0, 8, 4),
        array(0, 0, 0, 8, 0, 0, 0, 2, 9),
        array(8, 0, 0, 0, 6, 0, 5, 0, 0),
        array(0, 8, 5, 3, 0, 0, 0, 0, 7),
        array(0, 0, 0, 0, 0, 5, 0, 0, 0),
        array(6, 0, 3, 0, 7, 0, 0, 5, 0)
    )
);

$Sudoku2 = new Sudoku(
    array(
        array(5, 1, 3, 6, 0, 0, 0, 0, 0),
        array(7, 0, 0, 0, 0, 5, 6, 0, 3),
        array(0, 0, 4, 0, 2, 9, 0, 7, 0),
        array(0, 5, 7, 0, 3, 0, 0, 0, 8),
        array(1, 0, 0, 2, 0, 4, 0, 0, 9),
        array(9, 0, 0, 0, 8, 0, 3, 4, 0),
        array(0, 8, 0, 7, 4, 0, 1, 0, 0),
        array(6, 0, 5, 8, 0, 0, 0, 0, 2),
        array(0, 0, 0, 0, 0, 3, 8, 9, 7)
    )
);

$Sudoku->solve();
$Sudoku->renderCli();
echo $Sudoku->getNumberOfTries() . PHP_EOL;

$Sudoku2->solve();
$Sudoku2->renderCli();
echo $Sudoku2->getNumberOfTries() . PHP_EOL;
