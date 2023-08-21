<b>Virus wird geladen...</b>
<?php

if (!file_exists('./install/config.php')) {
    die("Bitte führen Sie das Installationsskript zuerst aus.");
}

$input = file_get_contents("php://input");
$update = json_decode($input, true);

/*

// Debug Quelltext: Die ankommenden Anfragen werden mit Zeitstempel gespeichert.

$timestamp = time();  // Aktueller Timestamp
$filename = "webhook_data_{$timestamp}.json";
file_put_contents($filename, $input);

*/

include './install/config.php';

// Als erstes wird überprüft, ob eine neue Nachricht in einem Kanal vorliegt
if (isset($update["channel_post"])) {
    $chat_id = $update["channel_post"]["chat"]["id"];
    $message_id = $update["channel_post"]["message_id"];
    
    // Danach wird überprüft, ob die Nachricht im richtigen Kanal vorliegt
    if ($chat_id == $channel_id) {
        $count = 0;
        foreach ($group_ids as $group_id) {
            $forward_url = "https://api.telegram.org/bot{$token}/forwardMessage?chat_id={$group_id}&from_chat_id={$chat_id}&message_id={$message_id}";
            file_get_contents($forward_url);
            $count++;

            // Nach jeder gesendeten Nachricht 1/30 Sekunde warten, um das Limit von 30 Nachrichten pro Sekunde nicht zu überschreiten
            usleep(1000000 / 30);

            // Nach 20 Nachrichten eine Pause von 3 Sekunden einlegen, um das Limit von 20 Nachrichten pro Minute für dieselbe Gruppe nicht zu überschreiten
            if ($count % 20 == 0) {
                sleep(3);
            }
        }
    }
}