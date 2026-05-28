<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

echo "<pre>";
echo "=== НАЧАЛО ОТЛАДКИ ===\n\n";

$title = trim($_POST['title'] ?? '');
$release_year = trim($_POST['release_year'] ?? '');
$synopsis = trim($_POST['synopsis'] ?? '');
$budget = trim($_POST['budget'] ?? '');
$actors = $_POST['actors'] ?? [];
$fun_facts_array = $_POST['fun_facts'] ?? [];

echo "1. Получены данные:\n";
echo "   title: $title\n";
echo "   release_year: $release_year\n";
echo "   actors: " . print_r($actors, true) . "\n";
echo "   fun_facts_array: " . print_r($fun_facts_array, true) . "\n\n";

// Проверка обязательных полей
if ($title === '' || $release_year === '') {
    die('Не заполнены обязательные поля (название и год выпуска).');
}

if (!is_numeric($release_year) || $release_year < 1888 || $release_year > 2026) {
    die('Год выпуска должен быть числом от 1888 до 2026.');
}

if ($budget !== '' && $budget !== null && !is_numeric($budget)) {
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

echo "2. fun_facts JSON: $fun_facts\n\n";

// Начинаем транзакцию
echo "3. Начинаем транзакцию...\n";
$mysqli->begin_transaction();

try {
    // 1. Сохраняем фильм
    echo "4. Сохраняем фильм...\n";
    $stmt = $mysqli->prepare('INSERT INTO movies (title, release_year, synopsis, budget, fun_facts) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        throw new Exception('Ошибка подготовки запроса фильма: ' . $mysqli->error);
    }
    $stmt->bind_param('sisds', $title, $release_year, $synopsis, $budget, $fun_facts);
    if (!$stmt->execute()) {
        throw new Exception('Ошибка выполнения запроса фильма: ' . $stmt->error);
    }
    $movie_id = $mysqli->insert_id;
    $stmt->close();
    echo "   Фильм сохранён, ID: $movie_id\n\n";
    
    // 2. Обрабатываем актёров
    echo "5. Обрабатываем актёров (всего: " . count($actors) . "):\n";
    foreach ($actors as $index => $actor_name) {
        $actor_name = trim($actor_name);
        echo "   Актёр $index: '$actor_name'\n";
        
        if ($actor_name === '') {
            echo "      -> Пропущен (пустое имя)\n";
            continue;
        }
        
        // Проверяем, существует ли уже такой актёр
        $check_stmt = $mysqli->prepare('SELECT id FROM persons WHERE full_name = ?');
        if (!$check_stmt) {
            throw new Exception('Ошибка подготовки проверки актёра: ' . $mysqli->error);
        }
        $check_stmt->bind_param('s', $actor_name);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $check_stmt->bind_result($person_id);
            $check_stmt->fetch();
            echo "      -> Актёр найден, ID: $person_id\n";
        } else {
            $check_stmt->close();
            // Создаём нового актёра
            $insert_stmt = $mysqli->prepare('INSERT INTO persons (full_name) VALUES (?)');
            if (!$insert_stmt) {
                throw new Exception('Ошибка подготовки вставки актёра: ' . $mysqli->error);
            }
            $insert_stmt->bind_param('s', $actor_name);
            if (!$insert_stmt->execute()) {
                throw new Exception('Ошибка выполнения вставки актёра: ' . $insert_stmt->error);
            }
            $person_id = $mysqli->insert_id;
            $insert_stmt->close();
            echo "      -> Создан новый актёр, ID: $person_id\n";
        }
        $check_stmt->close();
        
        // Создаём связь фильма с актёром
        echo "      -> Создаём связь фильм($movie_id) - актёр($person_id)...\n";
        $link_stmt = $mysqli->prepare('INSERT INTO movie_persons (movie_id, person_id) VALUES (?, ?)');
        if (!$link_stmt) {
            throw new Exception('Ошибка подготовки связи: ' . $mysqli->error);
        }
        $link_stmt->bind_param('ii', $movie_id, $person_id);
        if (!$link_stmt->execute()) {
            throw new Exception('Ошибка выполнения связи: ' . $link_stmt->error);
        }
        $link_stmt->close();
        echo "      -> Связь создана\n";
    }
    
    // Фиксируем транзакцию
    echo "\n6. Фиксируем транзакцию...\n";
    $mysqli->commit();
    
    echo "\n=== ВСЁ УСПЕШНО ===\n";
    echo "</pre>";
    
    echo '<p style="color: green;">✅ Фильм успешно сохранён!</p>';
    echo '<p><a href="list.php">📋 Перейти к списку фильмов</a></p>';
    echo '<p><a href="add.php">➕ Добавить ещё один фильм</a></p>';
    
} catch (Exception $e) {
    $mysqli->rollback();
    echo "\n=== ОШИБКА ===\n";
    echo "Сообщение: " . $e->getMessage() . "\n";
    echo "</pre>";
    echo '<p style="color: red;">❌ Ошибка: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="add.php">← Вернуться к форме</a></p>';
}

$mysqli->close();
?>