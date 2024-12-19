<?php
session_start();

$boardSize = 5;

if (!isset($_SESSION['karel'])) {
    $_SESSION['karel'] = ['x' => 0, 'y' => 0, 'direction' => 0]; 
}
if (!isset($_SESSION['board'])) {
    $_SESSION['board'] = array_fill(0, $boardSize, array_fill(0, $boardSize, ''));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $commands = isset($_POST['commands']) ? explode("\n", trim($_POST['commands'])) : [];

    switch ($action) {
        case 'execute':
            foreach ($commands as $command) {
                processCommand(trim($command));
            }
            break;

        case 'repeat':
            if (isset($_SESSION['lastCommands'])) {
                foreach ($_SESSION['lastCommands'] as $command) {
                    processCommand(trim($command));
                }
            }
            break;

        case 'reset':
            $_SESSION['karel'] = ['x' => 0, 'y' => 0, 'direction' => 0];
            $_SESSION['board'] = array_fill(0, $boardSize, array_fill(0, $boardSize, ''));
            break;
    }

    $_SESSION['lastCommands'] = $commands;
}

// Zpracování jednoho příkazu
function processCommand($command) {
    global $boardSize;
    $parts = explode(" ", $command);
    $action = strtoupper($parts[0]);
    $param = isset($parts[1]) ? strtolower($parts[1]) : null;

    switch ($action) {
        case 'KROK':
            moveKarel(intval($param) ?: 1);
            break;

        case 'VLEVOBOK':
            $_SESSION['karel']['direction'] = ($_SESSION['karel']['direction'] + 3) % 4; // Otočka vlevo
            break;

        case 'POLOZ':
            $x = $_SESSION['karel']['x'];
            $y = $_SESSION['karel']['y'];
            $_SESSION['board'][$y][$x] = $param ?: 'žlutá';
            break;

        default:
            break;
    }
}

function moveKarel($steps) {
    global $boardSize;
    $karel = &$_SESSION['karel'];
    for ($i = 0; $i < $steps; $i++) {
        switch ($karel['direction']) {
            case 0: $karel['x'] = min($boardSize - 1, $karel['x'] + 1); break;
            case 1: $karel['y'] = max(0, $karel['y'] - 1); break; 
            case 2: $karel['x'] = max(0, $karel['x'] - 1); break; 
            case 3: $karel['y'] = min($boardSize - 1, $karel['y'] + 1); break;
        }
    }
}

function renderBoard() {
    global $boardSize;
    $board = $_SESSION['board'];
    $karel = $_SESSION['karel'];

    for ($y = 0; $y < $boardSize; $y++) {
        for ($x = 0; $x < $boardSize; $x++) {
            $class = '';
            if ($x === $karel['x'] && $y === $karel['y']) {
                $class = 'karel';
            }
            echo "<div class='cell $class'>" . htmlspecialchars($board[$y][$x]) . "</div>";
        }
    }
}

renderBoard();
?>
