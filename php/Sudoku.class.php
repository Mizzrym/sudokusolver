<?php

namespace sudokusolver;

use \Exception;

/**
 * Class to store and solve a Sudoku
 * Can only process standard 9x9 Sudokus (none of these
 * fancy 9x12 or even larger ones). 
 * There is nothing special about the way the solution is 
 * calculated - it just tries every possbility that seems
 * to make sense and ends if it found a solution that fits. 
 * Class is too stupid to find out if there are multiple
 * possible solutions.
 */
class Sudoku
{
    /**
     * A standard Sudoku has the size of 9x9 fields
     */
    const SIZE = 9;

    /**
     * Pointer to move around the field in two dimensions
     * Contains x and y coordinates
     */
    protected $pointer;

    /**
     * Two-dimensional array to store the Sudoku
     */
    protected $matrix = array();

    /**
     * Storage for the solutions tried by solve()
     */
    protected $solutions;

    /**
     * Index for solutions array
     */
    protected $index;

    /**
     * Counts how many tries were needed to solve
     * the Sudoku
     */
    protected $counter;

    /**
     * Constructor with two dimensional Sudoku matrix
     */
    public function __construct(array $matrix)
    {
        // sanity check
        if(sizeof($matrix) != self::SIZE) {
            throw new Exception('Invalid matrix');
        }
        foreach($matrix as $row) {
            if(sizeof($row) != self::SIZE) {
                throw new Exception('Invalid matrix');
            }
        }
        // initialize object
        $this->matrix = $matrix;
        $this->resetPointer();
        $this->counter = 0;
        $this->solutions = array();
        $this->index = 0;
    }

    /**
     * Renders the Sudoku for a CLI in a MySQL style table
     */
    public function renderCli()
    {
        // assemble table
        $table = '';
        for($i = 0; $i <= self::SIZE; $i++) {
            $table .= '+---';
        }
        $table = rtrim($table, '-');

        // iterate matrix
        foreach($this->matrix as $row) {
            echo $table . PHP_EOL;
            $line = '';
            foreach($row as $column) {
                $line .=  '| ' . $column . ' ';
            }
            $line .= '|';
            echo $line . PHP_EOL;
        }
        echo $table . PHP_EOL;
    }

    /**
     * Returns counter of how many tries were needed to 
     * solve the Sudoku
     *
     * @return int number of tries
     */
    public function getNumberOfTries()
    {
        return $this->counter;
    }

    /**
     * Tries to solve current Sudoku and fills in missing numbers
     *
     * @throws Exception 
     */
    public function solve()
    {
        // find an empty space
        for(;;) {
            if($this->getValue() === 0) {
                break;
            }
            // There is no empty space
            // solve() shouldn't have been called
            if($this->movePointer() === false) {
                return;
            }
        }

        // save current pointer position
        $position = $this->pointer;

        // find possible solution for this space
        // If there is no possible solution the Exception won't be catched
        // so if we're in a recursive call the current solution will be 
        // correctly intepreted as a crap solution and thrown away
        $possibleNumbers = $this->findSolution();

        // try each possible solution recursive
        foreach($possibleNumbers as $number) {
            try {
                $this->pointer = $position;
                $this->counter++;
                $this->setValue($number);
                $this->solve();
                // if we got this far then we found a solution for the last
                // empty space and we're done
                return;
            } catch(Exception $e) {
                // there was a problem with our solution, try a different one
                continue;
            }
        }
        // we couldn't solve the current Sudoku, so let's clean up 
        $this->pointer = $position;
        $this->setValue(0);
        throw new Exception('No value fits');
    }

    /**
     * Calculates all possible numbers for current pointer position
     * 
     * @return Array of possible numbers or empty array
     * @throws Exception
     */
    protected function findSolution()
    {
        // get numbers
        $numbers = array(
            1 => false,
            2 => false,
            3 => false,
            4 => false,
            5 => false,
            6 => false,
            7 => false,
            8 => false,
            9 => false
        );
        // get current quadrant range
        $rangeVertical = $this->getRange($this->pointer['x']);
        $rangeHorizontal = $this->getRange($this->pointer['y']);

        // iterate through quadrant and find out what numbers are 
        // already used for that quadrant
        // NOTE: In Sudoku each number MUST be once in each quadrant.
        //       A quadrant is a 3x3 part of the Sudoku. Each Sudoku 
        //       has 9 quadrants.
        foreach($rangeVertical as $x) {
            foreach($rangeHorizontal as $y) {
                // ignore fields that aren't filled with anything
                if($this->matrix[$x][$y] > 0) {
                    // sanity check
                    if($numbers[$this->matrix[$x][$y]] === true) {
                        throw new Exception('Found a number twice in quadrant');
                    }
                    $numbers[$this->matrix[$x][$y]] = true;
                }
            }
        }

        // next check the column and rows the pointer is at for the 
        // remaining numbers (if any)
        // NOTE: As in the quadrants each number MUST be once in each
        //       row and in each column of the Sudoku
        foreach($numbers as $number => $found) {
            if(!$found) {
                for($i = 0; $i < 9; $i++) {
                    // check row
                    if($this->matrix[$i][$this->pointer['y']] === $number) {
                        $numbers[$number] = true;
                    }
                    // check column
                    if($this->matrix[$this->pointer['x']][$i] === $number) {
                        $numbers[$number] = true;
                    }
                }
            }
        }

        // assemble return value $rv
        $rv = array();
        foreach($numbers as $number => $found) {
            if(!$found) {
                $rv[] = $number;
            }
        }

        // Throw Exception if all numbers are taken
        if(sizeof($rv) === 0) {
            throw new Exception('Found no possible solution');
        }
        return $rv;
    }

    /**
     * Returns array indizes for quadrants
     *
     * @return array with three matrix indizes
     */
    protected function getRange($value)
    {
        $value++;
        $ident = ceil($value / 3);
        switch($ident) {
            case 1:
                return array(0, 1, 2);
            case 2:
                return array(3, 4, 5);
            case 3:
                return array(6, 7, 8);
            default:
                throw new Exception('Something wicked happend');
        }
    }

    /**
     * Brings pointer to initial position
     */
    protected function resetPointer() 
    {
        $this->pointer = array('x' => 0, 'y' => 0);
    }

    /**
     * Moves pointer one field forward. Will jump to next 
     * line if the end of the current one is reached
     * 
     * @param $position Array of a specific position to move to
     */
    protected function movePointer($position = null)
    {
        // move to specific position if position is given
        if(is_array($position)) {
            $this->pointer = $position;
            return true;
        }
        // move one field forward
        if($this->pointer['y'] < self::SIZE - 1) {
            $this->pointer['y']++;
            return true;
        }
        if($this->pointer['x'] < self::SIZE - 1) {
            $this->pointer['y'] = 0;
            $this->pointer['x']++;
            return true;
        }
        return false;
    }

    /**
     * Returns value from current pointer position
     *
     * return int value from 0 - 9 where 0 is an empty field
     */
    protected function getValue()
    {
        return (int) $this->matrix[$this->pointer['x']][$this->pointer['y']];
    }

    /**
     * Overwrites value from current pointer position
     */
    protected function setValue($value)
    {
        $this->matrix[$this->pointer['x']][$this->pointer['y']] = $value;
    }
}
