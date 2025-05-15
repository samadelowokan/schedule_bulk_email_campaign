<?php
include(__DIR__ . "/emailconfig.php");
include(__DIR__ . "/randomizer.php");

$data = $_POST;

/**
 * Replace placeholders in text: {NAME}, {WEBSITE}, {USER}, {QUESTION}
 */
function renderText($text, $name, $website, $user = '', $question = '') {
    return str_replace(
        ['{NAME}','{WEBSITE}','{USER}','{QUESTION}'], 
        [ $name ?: '', $website ?: '', $user, $question ], 
        $text
    );
}

/**
 * Parse one CSV row (semicolon-separated) into its parts.
 * Returns: ['name','email','website','questionFile']
 */
function parseContactLine($line) {
    // $parts = array_map('trim', explode(';', $line));
    // Manjot Kaur
    $delimiter = strpos($line, ';') !== false ? ';' : ',';
    $parts = array_map('trim', explode($delimiter, $line));
    // Manjot Kaur

    return [
        'name'         => $parts[0] ?? null,
        'email'        => $parts[1] ?? null,
        'website'      => $parts[2] ?? null,
        'questionFile' => $parts[3] ?? null
    ];
}

/**
 * From /contacts/questions/{questionFile}, pick a random "User ; Question" line.
 */
function getRandomQuestion($questionFile) {
    if (empty($questionFile)) {
        return ['user'=>'','question'=>''];
    }
    $path = __DIR__ . "/../contacts/questions/" . basename($questionFile);
    if (!is_file($path)) {
        return ['user'=>'','question'=>''];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($lines)) {
        return ['user'=>'','question'=>''];
    }
    $line = $lines[array_rand($lines)];
    $pq = array_map('trim', explode(';', $line, 2));
    return [
        'user'     => $pq[0] ?? '',
        'question' => $pq[1] ?? ''
    ];
}

try {
    // Raw CSV from textarea (semicolon-delimited rows)
    $rawCsv = trim($data['mobile'] ?? '');
    if ($rawCsv === '') {
        throw new Exception("No contact data provided.");
    }
    $lines = preg_split('/\r?\n/', $rawCsv);
    $account = $data['account'] ?? null;
    $subjectTpl = $data['subject'] ?? '';
    $messageTpl = $data['message'] ?? '';

    foreach ($lines as $line) {
        if (trim($line) === '') continue;
        $c = parseContactLine($line);
        // Fetch random question
        $q = getRandomQuestion($c['questionFile']);

        // Render subject & message
        $subject = renderText($subjectTpl, $c['name'], $c['website'], $q['user'], $q['question']);
        $msg     = renderText($messageTpl, $c['name'], $c['website'], $q['user'], $q['question']);

        // Process randomizer
        $rnd       = new Randomizer();
        $subject   = $rnd->process($subject);
        $msg       = $rnd->process($msg);

        // Send email
        $ok = sendAllEmail($account, $c['email'], $c['name'], $subject, $msg);

        echo json_encode([ 'success'=>(bool)$ok, 'email'=>$c['email'] ]) . PHP_EOL;
        sleep(1);
    }
} catch (\Throwable $e) {
    echo json_encode([ 'success'=>false, 'error'=>$e->getMessage() ]);
}

sleep(5);
return;
