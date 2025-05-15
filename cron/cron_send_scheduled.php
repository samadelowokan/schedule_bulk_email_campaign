<?php 
/** SAMUEL ADELOWOKAN
 *  Mail scheduler script to be run via cron job.
 * 
 * * * * * * php /cron/cron_send_scheduled.php

 */
echo (__DIR__);
$db = new PDO('sqlite:../db/email_scheduler.sqlite');

$now = date('Y-m-d H:i:s');
$today = date('l'); // Full day name (e.g., Monday)
$timeNow = date('H:i');

$campaigns = $db->query("SELECT * FROM campaigns");
while ($campaign = $campaigns->fetchArray(SQLITE3_ASSOC)) {
    $campaignDays = array_map('trim', explode(',', $campaign['days']));
    $scheduledTime = $campaign['time'];

    // Check if today is in campaign days and current time is >= scheduled time
    if (in_array($today, $campaignDays) && $timeNow >= $scheduledTime) {

        // Prevent sending more than once a week (check logs)
        $stmt = $db->prepare("
            SELECT COUNT(*) as sent_count 
            FROM campaign_logs 
            WHERE campaign_id = :id AND strftime('%W', sent_at) = strftime('%W', 'now')
        ");
        $stmt->bindValue(':id', $campaign['id'], SQLITE3_INTEGER);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($row['sent_count'] > 0) {
            echo "Campaign {$campaign['id']} already sent this week.\n";
            continue;
        }

        // Read CSV and Template
        // $csvFile = __DIR__ . '/../contacts/' . $campaign['csv'];
        $templateFile = __DIR__ . '/../templates/' . $campaign['msg'];

        if (!file_exists($csvFile) || !file_exists($templateFile)) {
            echo "Missing CSV or template for campaign ID {$campaign['id']}\n";
            continue;
        }

        // $rows = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rows = preg_split('/\r\n|\r|\n/', $campaign['mobile']);
        $templateParts = explode(';', file_get_contents($templateFile));
        $subjectTpl = trim($templateParts[0]);
        $bodyTpl = trim($templateParts[1]);

        foreach ($rows as $row) {
            $fields = str_getcsv($row);
            $email = $fields[0];
            $name = $fields[1] ?? '';

            $subject = str_replace(['{name}'], [$name], $subjectTpl);
            $body = str_replace(['{name}'], [$name], $bodyTpl);

            // Send Email
            if (mail($email, $subject, $body)) {
                echo "Sent to $email\n";

                // Log the sent email
                $stmt = $db->prepare("INSERT INTO campaign_logs (campaign_id, email, sent_at) VALUES (:cid, :email, :sent)");
                $stmt->bindValue(':cid', $campaign['id'], SQLITE3_INTEGER);
                $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                $stmt->bindValue(':sent', $now->format('Y-m-d H:i:s'), SQLITE3_TEXT);
                $stmt->execute();
            } else {
                echo "Failed to send to $email\n";
            }
        }

        // Update campaign last_sent_date
        $stmt = $db->prepare("UPDATE campaigns SET last_sent_date = :date WHERE id = :id");
        $stmt->bindValue(':date', $now->format('Y-m-d'), SQLITE3_TEXT);
        $stmt->bindValue(':id', $campaign['id'], SQLITE3_INTEGER);
        $stmt->execute();
    }
}

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