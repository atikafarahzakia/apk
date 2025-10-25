<?php
$input = '1234'; // password yang ingin kamu tes
$hash = '$2y$10$iHTuNnWuYLKtBMGEsIo6NeQO66Gkh5ntrsutuXOYNNK'; // ambil dari database

if (password_verify($input, $hash)) {
    echo "Cocok!";
} else {
    echo "Tidak cocok!";
}