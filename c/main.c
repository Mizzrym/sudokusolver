#include <stdio.h>

void cli_print(int sudoku[9][9]);
int solve(int sudoku[9][9], int row, int col);
int fits(int sudoku[9][9], int row, int col, int value);
int next(int *row, int *col);


int
main(void) {
    int sudoku[9][9] = {
                    {0, 3, 4, 0, 0, 2, 1, 7, 8},
                    {0, 0, 0, 0, 1, 0, 0, 0, 0},
                    {0, 0, 1, 5, 0, 0, 4, 0, 6},
                    {0, 0, 0, 7, 0, 0, 0, 8, 4},
                    {0, 0, 0, 8, 0, 0, 0, 2, 9},
                    {8, 0, 0, 0, 6, 0, 5, 0, 0},
                    {0, 8, 5, 3, 0, 0, 0, 0, 7},
                    {0, 0, 0, 0, 0, 5, 0, 0, 0},
                    {6, 0, 3, 0, 7, 0, 0, 5, 0}
                };
    solve(sudoku, 0, 0);
    cli_print(sudoku);
}

int
solve(int sudoku[9][9], int row, int col) {
    int i, x, y;
    if(sudoku[row][col]) {
        if(!next(&row, &col))
            return 1;
        return solve(sudoku, row, col);
    }
    for(i = 1; i <= 9; ++i) 
        if(fits(sudoku, row, col, i)) {
            x = col;
            y = row;
            sudoku[row][col] = i;
            if(!next(&y, &x))
                return 1;
            if(solve(sudoku, y, x))
                return 1;
            else
                sudoku[row][col] = 0;
        }
    return 0;
}

int
fits(int sudoku[9][9], int row, int col, int value) {
    int i, j;
    for(i = 0; i < 9; i++) {
        if(sudoku[i][col] == value)
            return 0;
        if(sudoku[row][i] == value)
            return 0;
    }
    for(i = (row / 3) * 3; i < ((row / 3) * 3) + 3; i++)
        for(j = (col / 3) * 3; j < ((col / 3) * 3) + 3; j++)
            if(sudoku[i][j] == value)
                return 0;
    return 1;
}

int
next(int *row, int *col) {
    if(*row == 8 && *col == 8)
        return 0;
    if(*col == 8) {
        *col = 0;
        ++*row;
    } else {
        ++*col;
    }
    return 1;
}

void
cli_print(int sudoku[9][9]) {
    int row, column;
    for(row = 0; row < 9; row++) {
        printf("+---+---+---+---+---+---+---+---+---+\n");
        for(column = 0; column < 9; column++) {
            printf("| %d ", sudoku[row][column]);
        }
        printf("|\n");
    }
    printf("+---+---+---+---+---+---+---+---+---+\n");
}
