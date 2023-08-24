<?php

if (!file_exists('./install/config.php')) {
    die("Bitte führen Sie das Installationsskript zuerst aus.");
}

include './install/config.php';

$input = file_get_contents("php://input");
$update = json_decode($input, true);


// Debug Quelltext: Die ankommenden Anfragen werden mit Zeitstempel gespeichert.

function speichereAnfragen() {
    $timestamp = time();  // Aktueller Timestamp
    $filename = "webhook_data_{$timestamp}.json";
    file_put_contents($filename, $input);
}

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

            // Nach 20 Nachrichten eine Pause von 60 Sekunden einlegen, um das Limit von 20 Nachrichten pro Minute für dieselbe Gruppe nicht zu überschreiten
            if ($count % 20 == 0) {
                sleep(60);
            }
        }
    }
}


function isUserAdmin($user_id, $chat_id, $token) {
    // Curl initialisieren
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/getChatAdministrators?chat_id={$chat_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($data["ok"] == true) {
        foreach ($data["result"] as $admin) {
            if ($admin["user"]["id"] == $user_id) {
                return true;
            }
        }
    }
    
    return false;
}

// Falls jemand die Adresse Deines Webhooks herausfindet:
echo "<b>Virus wird geladen...</b>";

// Wenn eine neue Nachricht in einem Gruppenchat ankommt
if (isset($update["message"])) {
    $chat_type = $update["message"]["chat"]["type"];
    $chat_id = $update["message"]["chat"]["id"];
    $message_text = $update["message"]["text"];
    $user_id = $update["message"]["from"]["id"];
    
    // Überprüfen, ob die Nachricht im Gruppenchat ist, ob der Text "/gruppe" enthält und ob der Absender ein Admin ist
    if (($chat_type == "group" || $chat_type == "supergroup") && $message_text == "/gruppe") {
        if (isUserAdmin($user_id, $chat_id, $token)) {
            // Antwort senden mit der Gruppen-ID, formattiert als Mono
            $reply_text = "Die Gruppen-ID ist: `" . $chat_id . "`";

            // Curl initialisieren
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/sendMessage");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                'chat_id' => $chat_id,
                'text' => $reply_text,
                'parse_mode' => 'Markdown'
            )));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $reply_text = "Entschuldigung, nur Gruppenadmins können dieses Kommando verwenden.";

            // Antwort senden
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/sendMessage");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                'chat_id' => $chat_id,
                'text' => $reply_text
            )));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
        }
    }
}
