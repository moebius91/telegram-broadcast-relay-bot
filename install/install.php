<?php

$configFile = 'config.php';

if (file_exists($configFile)) {
    die("Die Konfigurationsdatei existiert bereits! Bitte löschen Sie sie, wenn Sie die Einrichtung erneut durchführen möchten.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $groupIds = explode(',', $_POST['group_ids']);
    $channelId = $_POST['channel_id'];
    
    $configContent = "<?php\n\n";
    $configContent .= "\$token = '{$token}';\n";
    $configContent .= "\$group_ids = [" . implode(", ", $groupIds) . "];\n";
    $configContent .= "\$channel_id = {$channelId};\n";  // Kanal ID hinzugefügt

    file_put_contents($configFile, $configContent);

    echo "Einrichtung abgeschlossen! Die Konfigurationsdaten wurden in '{$configFile}' gespeichert.";
    exit;
}

?>

<form action="install.php" method="post">
    <label for="token">Bitte geben Sie Ihren Telegram-Bot-Token ein:</label>
    <input type="text" name="token" required>
    
    <label for="group_ids">Bitte geben Sie die Gruppen-IDs ein, getrennt durch Kommas (z.B. -12345,-67890):</label>
    <input type="text" name="group_ids" required>

    <label for="channel_id">Bitte geben Sie die Kanal-ID ein:</label>
    <input type="text" name="channel_id" required>  <!-- Neues Eingabefeld für Kanal ID -->
    
    <input type="submit" value="Einrichten">
</form>
