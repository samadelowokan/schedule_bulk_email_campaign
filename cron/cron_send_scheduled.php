<?php 
/** SAMUEL ADELOWOKAN
 *  Mail scheduler script to be run via cron job.
 * 
 * * * * * * php /cron/cron_send_scheduled.php

 */
include("../service/emailconfig.php");

$db = new PDO('sqlite:../db/email_scheduler.sqlite');

$now = date('Y-m-d H:i:s');
$today = date('l'); // Full day name (e.g., Monday)
$timeNow = date('H:i');

$stmt = $db->query("SELECT * FROM campaigns");
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totaltoday = 0;
$skipped = 0;

foreach ($campaigns as $campaign) {
    $campaignDays = array_map('trim', explode(',', $campaign['days']));
    $scheduledTime = $campaign['time'];

    // Check if today is in campaign days and current time is >= scheduled time
    if (in_array($today, $campaignDays) && $timeNow >= $scheduledTime) {
        $totaltoday++;
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
            $skipped++;
            continue;
        }
        
        try {
            // Read CSV and Template
            $mobile = trim($campaign['mobile'] ?? '');
            if ($mobile === '') {
                throw new Exception("No contact data provided.");
            }
            $lines = preg_split('/\r?\n/', $mobile);
            $account = $campaign['account'] ?? null;
            $subjectTpl = $campaign['subject'] ?? '';
            $messageTpl = $campaign['msg'] ?? '';
            
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
                
                // Send Email
                if ($ok) {
                    echo "Sent to $email\n";

                    // Log the sent email
                    $stmt = $db->prepare("INSERT INTO campaign_logs (campaign_id, email, sent_at) VALUES (:cid, :email, :sent)");
                    $stmt->bindValue(':cid', $campaign['id'], SQLITE3_INTEGER);
                    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                    $stmt->bindValue(':sent', $now->format('Y-m-d H:i:s'), SQLITE3_TEXT);
                    $stmt->execute();

                    echo json_encode([ 'success'=>(bool)$ok, 'email'=>$c['email'] ]) . PHP_EOL;
                    sleep(1);
                } else {
                    echo "Failed to send to $email\n";
                }
            }

            // Update campaign last_sent_date
            $stmt = $db->prepare("UPDATE campaigns SET last_sent_date = :date WHERE id = :id");
            $stmt->bindValue(':date', $now->format('Y-m-d'), SQLITE3_TEXT);
            $stmt->bindValue(':id', $campaign['id'], SQLITE3_INTEGER);
            $stmt->execute();

        } catch (\Throwable $e) {
            echo json_encode([ 'success'=>false, 'error'=>$e->getMessage() ]);
        }

        sleep(5);
        return; 
    }
}

// indicate if no campaign is pending to be sent
if (($totaltoday == $skipped) || ($totaltoday==0)){
    echo "No scheduled campaign for today. \n";
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