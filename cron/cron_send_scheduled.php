<?php 
/** SAMUEL ADELOWOKAN
 *  Mail scheduler script to be run via cron job.
 *  All Campaigns pending will be executed whenever this script is run.
 * Pending Campaign = any campaign with time < now(), and that has today's day in the 'days' column of the database
 * Sent mails are stored in the campaign_logs table, so mails are not sent twice
 * 
 * * * * * * php /cron/cron_send_scheduled.php

 */

include("../service/emailconfig.php");
include("../service/randomizer.php");

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
        $stmt->bindValue(':id', $campaign['id'], PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        // Check if fetch succeeded
        if ($row === false) {
            error_log("Database query failed: could not fetch row from campaign_logs.");
            exit("Error fetching campaign data.");
        }

        // Logic for handling already-sent campaign
        if ($row['sent_count'] > 0) {
            echo "Campaign {$campaign['id']} already sent this week.\n";
            $skipped++;
            continue;
        }
        
        try {
            // check CSV and txt files
            $csv = trim($campaign['csv'] ?? '');
            if ($campaign['csv'] === '') {
                throw new Exception("No .csv contact data provided.");
            }
            if ($campaign['template'] === '') {
                throw new Exception("No .txt template data provided.");
            }
            
            // process contacts .csv file
            $csvFilename = '../contacts/'.$campaign['csv'];
            if (file_exists($csvFilename)) {
                $csvContents = file_get_contents($csvFilename);
                $csvContents = trim($csvContents);
                $lines = preg_split('/\r?\n/', $csvContents);
            } else {
                throw new Exception("Error in opening .csv contact file.");
            }

            // process template .txt file
            $txtFilename = '../templates/'.$campaign['template'];
            if (file_exists($txtFilename)) {
                $txtContents = file_get_contents($txtFilename);
                list($subject, $message) = explode(';', $txtContents, 2); // Split at the first semicolon
                $subject = trim($subject);
                $message = trim($message);
            } else {
                throw new Exception("Error in opening .txt template file.");
            }

            $account = $campaign['account'];
            $subject = $campaign['subject'];
            
            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $c = parseContactLine($line);
                // Fetch random question
                $q = getRandomQuestion($c['questionFile']);
        
                // Render message
                // $subject = renderText($subjectTpl, $c['name'], $c['website'], $q['user'], $q['question']);
                $msg     = renderText($message, $c['name'], $c['email'], $c['website'], $q['user'], $q['question']);
        
                // Process randomizer
                $rnd       = new Randomizer();
                $subject   = $rnd->process($subject);
                $msg       = $rnd->process($msg);

                // Send email
                $ok = sendAllEmail($account, $c['email'], $c['name'], $subject, $msg);
                
                // Send Email
                if ($ok) {
                    echo "Sent to ".$c['email']."\n";

                    // Log the sent email
                    $now = date('Y-m-d H:i:s');
                    $stmt = $db->prepare("INSERT INTO campaign_logs (campaign_id, email, sent_at) VALUES (:cid, :email, :sent)");
                    $stmt->bindValue(':cid', $campaign['id'], PDO::PARAM_INT);
                    $stmt->bindValue(':email', $c['email'], PDO::PARAM_STR);
                    $stmt->bindValue(':sent', $now, PDO::PARAM_STR);
                    $stmt->execute();

                    echo json_encode([ 'success'=>(bool)$ok, 'email'=>$c['email'] ]) . PHP_EOL;
                    sleep(1);
                } else {
                    echo "Failed to send to ".$c['email']."\n";
                }
            }

            // Update campaign last_sent_date
            $now = date('Y-m-d H:i:s');
            $stmt = $db->prepare("UPDATE campaigns SET last_sent_date = :date WHERE id = :id");
            $stmt->bindValue(':date', $now, PDO::PARAM_STR);
            $stmt->bindValue(':id', $campaign['id'], PDO::PARAM_INT);
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

file_put_contents("cron_log.txt", date("Y-m-d H:i:s") . " - Cron ran successfully\n", FILE_APPEND);


/**
 * Replace placeholders in text: {NAME}, {WEBSITE}, {USER}, {QUESTION}
 */
function renderText($text, $name, $email, $website, $user = '', $question = '') {
    return str_replace(
        ['{NAME}', '{EMAIL}', '{WEBSITE}','{USER}','{QUESTION}'], 
        [ $name ?: '', $email ?: '', $website ?: '', $user, $question ], 
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