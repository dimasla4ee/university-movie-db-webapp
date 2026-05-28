<?php
require_once 'db.php';

// Получаем список всех актёров для автодополнения
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Добавление фильма</h1>

    <p>
        <a href="index.php">На главную</a>
    </p>
    <p>
        <a href="list.php">Список фильмов</a>
    </p>

    <hr>

    <form method="post" action="save.php" id="movieForm">
        <!-- Основная информация о фильме -->
        <fieldset>
            <legend>Информация о фильме</legend>

            <p>
                <label><strong>Название фильма:</strong></label><br>
                <input type="text" name="title" size="50" required>
            </p>

            <p>
                <label><strong>Год выпуска:</strong></label><br>
                <input type="number" name="release_year" min="1888" max="2026" required>
            </p>

            <p>
                <label><strong>Синопсис (краткое содержание):</strong></label><br>
                <textarea name="synopsis" cols="50" rows="5"></textarea>
            </p>

            <p>
                <label><strong>Бюджет (в долларах):</strong></label><br>
                <input type="text" name="budget" placeholder="например: 25000000">
            </p>
        </fieldset>

        <!-- Блок для добавления актёров -->
        <fieldset>
            <legend>Актёры фильма</legend>

            <div id="actorsContainer">
                <div class="row" data-type="actor" data-index="0">
                    <input type="text" class="actor-input" name="actors[]" placeholder="Введите имя актёра"
                        autocomplete="off" style="width: 300px;">
                    <button type="button" class="remove-btn" onclick="removeRow(this)" style="display: none;">✖</button>
                </div>
            </div>

            <button type="button" class="add-btn" onclick="addRow('actor')">+ Добавить ещё актёра</button>

            <div class="existing-select">
                <label><strong>Или выберите из существующих:</strong></label><br>
                <select id="existingActorSelect" style="width: 300px;">
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

        <!-- Блок для добавления интересных фактов -->
        <fieldset>
            <legend>Интересные факты</legend>

            <div id="factsContainer">
                <div class="row" data-type="fact" data-index="0">
                    <input type="text" class="fact-input" name="fun_facts[]" placeholder="Введите интересный факт"
                        style="width: 400px;">
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

    <script src="script.js"></script>
    <script>
        // Передаём данные из PHP в JavaScript
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