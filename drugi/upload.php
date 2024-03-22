<?php
session_start();

$filename = $_FILES['file']['name'];

$location = "uploads/" . $filename;
$imageFileType = pathinfo($location, PATHINFO_EXTENSION);
$valid_extensions = array("jpg", "jpeg", "png", "pdf");

if (!in_array(strtolower($imageFileType), $valid_extensions)) {
    echo "<p>Invalid format ($imageFileType).</p>";
    die();
}

$content = file_get_contents($_FILES['file']['tmp_name']);

$encryption_key = md5('kljuc za enkripciju');
$cipher = "AES-128-CTR";
$iv_length = openssl_cipher_iv_length($cipher);
$options = 0;

// Non-NULL inicijalizacijski vektor za enkripciju
//Random dužine 16 byte
$encryption_iv = random_bytes($iv_length);

// Kriptiraj podatke sa openssl
$encrypted = openssl_encrypt($content, $cipher, $encryption_key, $options, $encryption_iv);

//Spremi podatke
$encryptedData = base64_encode($encrypted);
$_SESSION['iv'] = $encryption_iv;

$fileNameWithoutExt = substr($filename, 0, strpos($filename, "."));

if (!is_dir("uploads/")) {
    if (!mkdir("uploads/", 0777, true)) {
        die("<p>Can not create directory $dir.</p>");
    }
}

$fileNameOnServer = "uploads/${fileNameWithoutExt}.$imageFileType.txt";

file_put_contents($fileNameOnServer, $encryptedData);

echo "File uploaded successfully";