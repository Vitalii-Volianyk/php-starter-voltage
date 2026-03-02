<?php

// 1. Отримуємо весь HTML сторінки
$url = "https://www.dtek-krem.com.ua/ua/shutdowns";
$options = [
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/149.0\r\n"
    ]
];
$context = stream_context_create($options);
$html = file_get_contents($url, false, $context);

if ($html === FALSE) {
    die("Не вдалося завантажити сторінку.");
}

var_dump($html);

// 2. Витягуємо JSON-об'єкт з тегу <script> за допомогою Regular Expression
// Шукаємо текст між 'DisconSchedule.fact = ' та наступною крапкою з комою
preg_match('/DisconSchedule\.fact\s*=\s*({.*?});/s', $html, $matches);

if (isset($matches[1])) {
    $jsonData = $matches[1];
    
    // 3. Декодуємо в асоціативний масив
    $schedule = json_decode($jsonData, true);

    // Виведемо дані вашої групи (якщо знаєте ID або шукаєте по структурі)
    echo "<pre>";
    print_r($schedule['data']); 
    echo "</pre>";
} else {
    echo "Графік не знайдено в коді сторінки.";
}
