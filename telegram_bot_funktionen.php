<?php
// Funktionen für den Telegram-Bot

function speichereAnfrage($input) {
    global $debugMode;
    if ($debugMode) {
        $timestamp = time();  // Aktueller Timestamp
        $filename = "webhook_data_{$timestamp}.json";
        file_put_contents($filename, $input);
    }
}

function weiterleitenNachricht($update, $token, $channel_id, $group_ids) {
    if (isset($update["channel_post"])) {
        $chat_id = $update["channel_post"]["chat"]["id"];
        $message_id = $update["channel_post"]["message_id"];

        if ($chat_id == $channel_id) {
            $count = 0;
            foreach ($group_ids as $group_id) {
                $forward_url = "https://api.telegram.org/bot{$token}/forwardMessage?chat_id={$group_id}&from_chat_id={$chat_id}&message_id={$message_id}";
                file_get_contents($forward_url);
                $count++;

                usleep(1000000 / 30);

                if ($count % 20 == 0) {
                    sleep(60);
                }
            }
        }
    }
}

function istBenutzerAdmin($user_id, $chat_id, $token) {
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

function handleGruppenkommando($update, $token) {
    if (isset($update["message"])) {
        $chat_type = $update["message"]["chat"]["type"];
        $chat_id = $update["message"]["chat"]["id"];
        $message_text = $update["message"]["text"];
        $user_id = $update["message"]["from"]["id"];

        if (($chat_type == "group" || $chat_type == "supergroup") && $message_text == "/gruppe") {
            if (istBenutzerAdmin($user_id, $chat_id, $token)) {
                $reply_text = "Die Gruppen-ID ist: `" . $chat_id . "`";
                antworteNachricht($chat_id, $reply_text, $token, 'Markdown');
            } else {
                $reply_text = "Entschuldigung, nur Gruppenadmins können dieses Kommando verwenden.";
                antworteNachricht($chat_id, $reply_text, $token);
            }
        }
    }
}

function antworteNachricht($chat_id, $text, $token, $parse_mode = '') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt($ch, CURLOPT_POST, 1);
    $data = [
        'chat_id' => $chat_id,
        'text' => $text
    ];
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

