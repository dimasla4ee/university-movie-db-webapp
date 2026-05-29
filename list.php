<?php
require_once 'db.php';

$movies_result = $mysqli->query('SELECT * FROM movies ORDER BY title ASC');

if (!$movies_result) {
    die('Ошибка выполнения запроса: ' . $mysqli->error);
}

$actors_by_movie = [];
$actors_query = "
    SELECT 
        mp.movie_id,
        p.full_name
    FROM movie_persons mp
    JOIN persons p ON mp.person_id = p.id
    ORDER BY p.full_name
";

$actors_result = $mysqli->query($actors_query);
if ($actors_result) {
    while ($row = $actors_result->fetch_assoc()) {
        $actors_by_movie[$row['movie_id']][] = $row['full_name'];
    }
    $actors_result->free();
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Список фильмов</title>
</head>

<body>
    <h1>Список фильмов</h1>

    <p><a href="index.php">Главная</a> | <a href="add.php">Добавить фильм</a></p>

    <hr>

    <?php if ($movies_result->num_rows > 0): ?>

        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Название</th>
                <th>Год</th>
                <th>Синопсис</th>
                <th>Бюджет ($)</th>
                <th>Актёры</th>
                <th>Интересные факты</th>
            </tr>

            <?php while ($row = $movies_result->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['release_year']); ?></td>
                    <td>
                        <?php
                        if (!empty($row['synopsis'])) {
                            echo nl2br(htmlspecialchars($row['synopsis']));
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($row['budget'] !== null && $row['budget'] > 0) {
                            echo number_format($row['budget'], 0, ',', ' ');
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $movie_id = $row['id'];
                        if (isset($actors_by_movie[$movie_id]) && count($actors_by_movie[$movie_id]) > 0):
                            echo '<ul>';
                            foreach ($actors_by_movie[$movie_id] as $actor):
                                echo '<li>' . htmlspecialchars($actor) . '</li>';
                            endforeach;
                            echo '</ul>';
                        else:
                            echo '—';
                        endif;
                        ?>
                    </td>
                    <td>
                        <?php
                        if (!empty($row['fun_facts'])) {
                            $facts = json_decode($row['fun_facts'], true);
                            if (is_array($facts) && count($facts) > 0) {
                                echo '<ul>';
                                foreach ($facts as $fact) {
                                    echo '<li>' . htmlspecialchars($fact) . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo htmlspecialchars($row['fun_facts']);
                            }
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <p>Всего фильмов: <?php echo $movies_result->num_rows; ?></p>

    <?php else: ?>
        <p>В базе данных пока нет ни одного фильма.</p>
        <p><a href="add.php">Добавить первый фильм</a></p>
    <?php endif; ?>

    <?php 
    $movies_result->free();
    $mysqli->close();
    ?>
</body>
</html>