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

if ($title === '' || $release_year === '') {
    die('Ошибка: не заполнены обязательные поля (название и год выпуска).');
}

if (!is_numeric($release_year) || $release_year < 1888 || $release_year > 2026) {
    die('Ошибка: год выпуска должен быть числом от 1888 до 2026.');
}

if ($budget === '' || $budget === null) {
    $budget = null;
} elseif (!is_numeric($budget)) {
    die('Ошибка: бюджет должен быть числом.');
}

$fun_facts = null;
if (!empty($fun_facts_array)) {
    $fun_facts_array = array_filter(array_map('trim', $fun_facts_array));
    if (!empty($fun_facts_array)) {
        $fun_facts = json_encode(array_values($fun_facts_array), JSON_UNESCAPED_UNICODE);
    }
}

$mysqli->begin_transaction();

try {
    $stmt = $mysqli->prepare('INSERT INTO movies (title, release_year, synopsis, budget, fun_facts) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        throw new Exception('Ошибка подготовки запроса: ' . $mysqli->error);
    }
    $stmt->bind_param('sisds', $title, $release_year, $synopsis, $budget, $fun_facts);
    $stmt->execute();
    $movie_id = $mysqli->insert_id;
    $stmt->close();

    if (!empty($actors) && is_array($actors)) {
        foreach ($actors as $actor_name) {
            $actor_name = trim($actor_name);
            if ($actor_name === '') continue;

            $person_id = null;
            $find_stmt = $mysqli->prepare('SELECT id FROM persons WHERE full_name = ?');
            if ($find_stmt) {
                $find_stmt->bind_param('s', $actor_name);
                $find_stmt->execute();
                $find_stmt->bind_result($person_id);
                $find_stmt->fetch();
                $find_stmt->close();
            }

            if (!$person_id) {
                $insert_stmt = $mysqli->prepare('INSERT INTO persons (full_name) VALUES (?)');
                if ($insert_stmt) {
                    $insert_stmt->bind_param('s', $actor_name);
                    $insert_stmt->execute();
                    $person_id = $mysqli->insert_id;
                    $insert_stmt->close();
                }
            }

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

    $mysqli->commit();
    echo "Фильм \"" . htmlspecialchars($title) . "\" успешно сохранён.<br>\n";
    echo "<a href='list.php'>Перейти к списку фильмов</a><br>\n";
    echo "<a href='add.php'>Добавить ещё</a><br>\n";
    echo "<a href='index.php'>На главную</a>\n";

} catch (Exception $e) {
    $mysqli->rollback();
    echo "Ошибка: " . $e->getMessage() . "<br>\n";
    echo "<a href='add.php'>Вернуться к форме</a>\n";
}

$mysqli->close();
?>