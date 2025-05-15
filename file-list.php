<?php

$contactsDirectory = __DIR__ . '/contacts';
$templatesDirectory = __DIR__ . '/templates';
$extension = isset($_GET['extension']) ? $_GET['extension'] : '';

if ($extension === 'txt' || $extension === 'csv') {
    if ($extension === 'csv') {
        $directory = $contactsDirectory;
    } else {
        $directory = $templatesDirectory;
    }

    $files = scandir($directory);
    $filteredFiles = [];

    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == $extension) {
            $filteredFiles[] = $file;
        }
    }

    // Return the list of .txt or .csv files as a JSON array
    header('Content-Type: application/json');
    echo json_encode($filteredFiles);
} else {
    // Invalid or no extension provided
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid or missing file extension.';
}

?>
