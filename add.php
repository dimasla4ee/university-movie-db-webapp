<?php
require_once 'db.php';

$actors_list = [];
$actors_result = $mysqli->query("SELECT id, full_name FROM persons ORDER BY full_name");
if ($actors_result) {
    while ($row = $actors_result->fetch_assoc()) {
        $actors_list[] = $row;
    }
    $actors_result->free();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить фильм</title>
    <script src="script.js"></script>
</head>
<body>
    <h1>Добавление фильма</h1>

    <p><a href="index.php">На главную</a></p>
    <p><a href="list.php">Список фильмов</a></p>

    <hr>

    <form method="post" action="save.php" id="movieForm">
        <fieldset>
            <legend>Информация о фильме</legend>

            <p>
                <label>Название фильма:</label><br>
                <input type="text" name="title" required>
            </p>

            <p>
                <label>Год выпуска:</label><br>
                <input type="number" name="release_year" min="1888" max="2026" required>
            </p>

            <p>
                <label>Синопсис:</label><br>
                <textarea name="synopsis" rows="5"></textarea>
            </p>

            <p>
                <label>Бюджет (в долларах):</label><br>
                <input type="text" name="budget" placeholder="25000000">
            </p>
        </fieldset>

        <fieldset>
            <legend>Актёры фильма</legend>

            <div id="actorsContainer">
                <div data-type="actor" data-index="0">
                    <input type="text" class="actor-input" name="actors[]" placeholder="Введите имя актёра" autocomplete="off">
                    <button type="button" class="remove-btn" onclick="removeRow(this)" style="display: none;">✖</button>
                </div>
            </div>

            <button type="button" class="add-btn" onclick="addRow('actor')">+ Добавить ещё актёра</button>

            <div>
                <label>Или выберите из существующих:</label><br>
                <select id="existingActorSelect">
                    <option value="">-- Выберите актёра --</option>
                    <?php foreach ($actors_list as $actor): ?>
                        <option value="<?php echo htmlspecialchars($actor['full_name']); ?>">
                            <?php echo htmlspecialchars($actor['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" onclick="addExistingActor()">Добавить выбранного</button>
            </div>
        </fieldset>

        <fieldset>
            <legend>Интересные факты</legend>

            <div id="factsContainer">
                <div data-type="fact" data-index="0">
                    <input type="text" class="fact-input" name="fun_facts[]" placeholder="Введите интересный факт">
                    <button type="button" class="remove-btn" onclick="removeRow(this)" style="display: none;">✖</button>
                </div>
            </div>

            <button type="button" class="add-btn" onclick="addRow('fact')">+ Добавить ещё факт</button>
        </fieldset>

        <p>
            <input type="submit" value="Сохранить фильм">
            <input type="reset" value="Очистить">
        </p>
    </form>

    <script>
        const actorSuggestions = <?php
            $suggestions = [];
            foreach ($actors_list as $actor) {
                $suggestions[] = $actor['full_name'];
            }
            echo json_encode($suggestions);
        ?>;
    </script>
</body>
</html>