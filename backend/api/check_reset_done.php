<?php
session_start();
header('Content-Type: application/json');

if (!empty($_SESSION['reset_completed'])) {
    unset($_SESSION['reset_completed']);
    unset($_SESSION['awaiting_reset']);
    echo json_encode(['done' => true]);
} else {
    echo json_encode(['done' => false]);
}