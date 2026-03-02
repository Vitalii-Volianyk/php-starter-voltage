<?php

// 1. Отримуємо весь HTML сторінки
$url = "https://www-dtek--krem-com-ua.translate.goog/ua/shutdowns?_x_tr_sl=en&_x_tr_tl=uk&_x_tr_hl=uk&_x_tr_pto=wapp";
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
// echo "<pre>";
// echo $html;
// echo "</pre>";

// 2. Витягуємо JSON-об'єкт з тегу <script> за допомогою Regular Expression
// Шукаємо текст між 'DisconSchedule.fact = ' та наступною крапкою з комою
preg_match('/DisconSchedule\.fact\s*=\s*({.*?})</s', $html, $matches);

if (isset($matches[1])) {
	$jsonData = $matches[1];

	// echo "<pre>";
	// echo $jsonData;
	// echo "</pre>";


	// 3. Декодуємо в асоціативний масив
	$schedule = json_decode($jsonData, true);

	$structured = [];

	foreach ($schedule['data'] as $timestamp => $groups) {
		$dateKey = date('Y-m-d', $timestamp); // Створює ключ '2026-02-27'
		$hour = (int)date('G', $timestamp);    // Година від 0 до 23


		foreach ($groups as $group => $times) {
			$hours = array();
			foreach ($times as $index => $status) {
				$subIndex = strval($index - 1); // Віднімаємо 1, щоб отримати правильну годину
				if ($status == "no") {
					$subIndex .= ":00";
				} else if ($status == "yes") {
					$subIndex .= ":00";
				} else {
					$subIndex .= ":30";
				}
				$hours[$index - 1] = [
					$status,
					$subIndex
				];
			}


			$structured[$dateKey][$group] = $hours;
		}
	}

	// Тепер доступ до даних максимально простий:
	$today = date('Y-m-d');
	$tomorrow = date('Y-m-d', strtotime('+1 day'));

	echo "<pre>";
	var_dump($structured[$today]["GPV6.2"]);
	echo "</pre>";
	$currentHour = (int)date('G') - 1;
	$next = "після 24-ї";
	foreach ($structured[$today]["GPV6.2"] as $hour => $data) {
		if ($hour >= $currentHour && $data[0] != "yes") {
			$next = "сьогодні орієнтовно з " . $data[1];
			foreach ($structured[$today]["GPV6.2"] as $hour2 => $data) {
				if ($hour2 > $hour && $data[0] != "no") {
					$next .= " до " . $data[1];
					break;
				}
			}
			break;
		}
	}
	echo "Наступне відключення  " . $next;
	//echo $currentHour;
	//echo "Сьогодні о 15:00 стан: " . $structured[$today][15];
	//echo "Завтра о 09:00 стан: " . $structured[$tomorrow][9];
} else {
	echo "Графік не знайдено в коді сторінки.";
}
