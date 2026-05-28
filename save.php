<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

$title = trim($_POST['title'] ?? '');
$release_year = trim($_POST['release_year'] ?? '');
$synopsis = trim($_POST['synopsis'] ?? '');
$budget = trim($_POST['budget'] ?? '');
$actors = $_POST['actors'] ?? [];
$fun_facts_array = $_POST['fun_facts'] ?? [];

// Проверка обязательных полей
if ($title === '' || $release_year === '') {
    die('Не заполнены обязательные поля (название и год выпуска).');
}

if (!is_numeric($release_year) || $release_year < 1888 || $release_year > 2026) {
    die('Год выпуска должен быть числом от 1888 до 2026.');
}

// Обработка бюджета
if ($budget === '' || $budget === null) {
    $budget = null;
} elseif (!is_numeric($budget)) {
    die('Бюджет должен быть числом.');
}

// Обработка интересных фактов
$fun_facts = null;
if (!empty($fun_facts_array)) {
    $fun_facts_array = array_filter(array_map('trim', $fun_facts_array));
    if (!empty($fun_facts_array)) {
        $fun_facts = json_encode(array_values($fun_facts_array), JSON_UNESCAPED_UNICODE);
    }
}

// Начинаем транзакцию
$mysqli->begin_transaction();

try {
    // 1. Сохраняем фильм
    $stmt = $mysqli->prepare('INSERT INTO movies (title, release_year, synopsis, budget, fun_facts) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        throw new Exception('Ошибка подготовки запроса: ' . $mysqli->error);
    }
    $stmt->bind_param('sisds', $title, $release_year, $synopsis, $budget, $fun_facts);
    $stmt->execute();
    $movie_id = $mysqli->insert_id;
    $stmt->close();
    
    // 2. Обрабатываем актёров
    if (!empty($actors) && is_array($actors)) {
        foreach ($actors as $actor_name) {
            $actor_name = trim($actor_name);
            if ($actor_name === '') continue;
            
            // Поиск существующего актёра
            $person_id = null;
            $find_stmt = $mysqli->prepare('SELECT id FROM persons WHERE full_name = ?');
            if ($find_stmt) {
                $find_stmt->bind_param('s', $actor_name);
                $find_stmt->execute();
                $find_stmt->bind_result($person_id);
                $find_stmt->fetch();
                $find_stmt->close();
            }
            
            // Если не найден, создаём нового
            if (!$person_id) {
                $insert_stmt = $mysqli->prepare('INSERT INTO persons (full_name) VALUES (?)');
                if ($insert_stmt) {
                    $insert_stmt->bind_param('s', $actor_name);
                    $insert_stmt->execute();
                    $person_id = $mysqli->insert_id;
                    $insert_stmt->close();
                }
            }
            
            // Создаём связь
            if ($person_id) {
                $link_stmt = $mysqli->prepare('INSERT INTO movie_persons (movie_id, person_id) VALUES (?, ?)');
                if ($link_stmt) {
                    $link_stmt->bind_param('ii', $movie_id, $person_id);
                    $link_stmt->execute();
                    $link_stmt->close();
                }
            }
        }
    }
    
    // Фиксируем транзакцию
    $mysqli->commit();
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Фильм сохранён</title>
    </head>
    <body>
        <h1>Результат</h1>
        <p style="color: green;">✅ Фильм "<strong>' . htmlspecialchars($title) . '</strong>" успешно сохранён!</p>
        <p><a href="list.php">📋 Перейти к списку фильмов</a></p>
        <p><a href="add.php">➕ Добавить ещё один фильм</a></p>
        <p><a href="index.php">🏠 На главную</a></p>
    </body>
    </html>';
    
} catch (Exception $e) {
    $mysqli->rollback();
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Ошибка</title>
    </head>
    <body>
        <h1>Ошибка</h1>
        <p style="color: red;">❌ Ошибка: ' . htmlspecialchars($e->getMessage()) . '</p>
        <p><a href="add.php">← Вернуться к форме</a></p>
    </body>
    </html>';
}

$mysqli->close();
?>