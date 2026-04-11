# Лабораторная работа №6. Обработка и валидация форм

## Цель работы
Освоить основные принципы работы с HTML-формами в PHP, включая отправку данных на сервер и их обработку, включая валидацию данных.

Как тему я выбрала каталог “странных фактов”

## Шаг 1. Определение модели данных

|Поле|Тип данных|Описание|Соответствие условию|
|---|---|---|---|
|title|string|Короткий заголовок (например, «Акулы и деревья»)|string|
|full_description|text|Подробное описание факта с объяснением причин и деталей|text (long)|
|category|enum|Категория: Nature, Science, History, Space, Human|enum (checkbox/select|
|weirdness_score|number|Оценка странности от 1 до 10|Минимум 6 полей|
|is_verified|boolean|Подтвержден ли факт научными данными|Минимум 6 полей|
|discovered_at|date|Дата, когда этот факт был зафиксирован или опубликован|date|
|source_url|string|Ссылка на первоисточник или статью в Википедии|Минимум 6 полей|

## Шаг 2. Создание HTML-формы

```html
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить странный факт</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; line-height: 1.6; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"], input[type="date"], input[type="url"], select, textarea {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        textarea { height: 100px; resize: vertical; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        button { background-color: #5c67f2; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #4a54e1; }
    </style>
</head>
<body>

    <h2>🧬 Добавить новый странный факт</h2>
    
    <form action="/submit-fact" method="POST">
        
        <div class="form-group">
            <label for="title">Название факта (title):</label>
            <input type="text" id="title" name="title" required minlength="5" maxlength="100" placeholder="Например: Бессмертная медуза">
        </div>

        <div class="form-group">
            <label for="category">Категория (category):</label>
            <select id="category" name="category" required>
                <option value="">-- Выберите категорию --</option>
                <option value="Nature">Природа (Nature)</option>
                <option value="Science">Наука (Science)</option>
                <option value="History">История (History)</option>
                <option value="Space">Космос (Space)</option>
                <option value="Human">Человек (Human)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="discovered_at">Дата публикации/открытия (discovered_at):</label>
            <input type="date" id="discovered_at" name="discovered_at" required>
        </div>

        <div class="form-group">
            <label for="weirdness_score">Уровень странности (1-10):</label>
            <input type="number" id="weirdness_score" name="weirdness_score" min="1" max="10" value="5" required>
        </div>

        <div class="form-group">
            <label for="full_description">Полное описание (full_description):</label>
            <textarea id="full_description" name="full_description" required minlength="20" placeholder="Расскажите подробнее, почему это странно..."></textarea>
        </div>

        <div class="form-group">
            <label for="source_url">Ссылка на источник (source_url):</label>
            <input type="url" id="source_url" name="source_url" required placeholder="https://example.com">
        </div>

        <div class="form-group checkbox-group">
            <input type="checkbox" id="is_verified" name="is_verified" value="true">
            <label for="is_verified">Факт научно подтвержден (is_verified)</label>
        </div>

        <button type="submit">Отправить в каталог</button>
    </form>

</body>
</html>
```

## Шаг 3. Обработка данных на сервере

```php
<?php
// 1. Установка заголовка для корректного отображения кириллицы
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Получение данных из $_POST
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? '';
    $discovered_at = $_POST['discovered_at'] ?? '';
    $weirdness_score = intval($_POST['weirdness_score'] ?? 0);
    $full_description = trim($_POST['full_description'] ?? '');
    $source_url = trim($_POST['source_url'] ?? '');
    $is_verified = isset($_POST['is_verified']) ? true : false;

    // 3. Базовая валидация на стороне сервера
    $errors = [];

    if (empty($title) || strlen($title) < 5) {
        $errors[] = "Заголовок слишком короткий или пуст.";
    }
    if (empty($category)) {
        $errors[] = "Выберите категорию.";
    }
    if (empty($discovered_at)) {
        $errors[] = "Укажите дату.";
    }
    if ($weirdness_score < 1 || $weirdness_score > 10) {
        $errors[] = "Рейтинг странности должен быть от 1 до 10.";
    }
    if (strlen($full_description) < 20) {
        $errors[] = "Описание должно содержать минимум 20 символов.";
    }
    if (!filter_var($source_url, FILTER_VALIDATE_URL)) {
        $errors[] = "Указан некорректный URL источника.";
    }

    // 4. Обработка результата
    if (empty($errors)) {
        // Подготовка данных для сохранения
        $newFact = [
            "id" => time(), // Использование метки времени как простого ID
            "title" => htmlspecialchars($title),
            "category" => $category,
            "discovered_at" => $discovered_at,
            "weirdness_score" => $weirdness_score,
            "full_description" => htmlspecialchars($full_description),
            "source_url" => $source_url,
            "is_verified" => $is_verified,
            "created_at" => date('Y-m-d H:i:s')
        ];

        // Читаем существующие данные из файла
        $file = 'data.json';
        $current_data = [];
        if (file_exists($file)) {
            $json_data = file_get_contents($file);
            $current_data = json_decode($json_data, true) ?? [];
        }

        // Добавляем новую запись и сохраняем
        $current_data[] = $newFact;
        if (file_put_contents($file, json_encode($current_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
            echo "<h2>Успех!</h2>";
            echo "<p>Странный факт «<strong>$title</strong>» успешно добавлен в каталог.</p>";
            echo "<a href='index.html'>Добавить еще один</a>";
        } else {
            echo "<h2>Ошибка</h2><p>Не удалось записать данные в файл.</p>";
        }
    } else {
        // Вывод ошибок валидации
        echo "<h2>Ошибки заполнения формы:</h2><ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul><p><a href='javascript:history.back()'>Вернуться назад</a></p>";
    }
} else {
    echo "Доступ запрещен.";
}
?>
```

## Шаг 4. Вывод данных

```php
<?php
header('Content-Type: text/html; charset=utf-8');

$file = 'data.json';
$facts = [];

// 1. Загрузка данных
if (file_exists($file)) {
    $json_data = file_get_contents($file);
    $facts = json_decode($json_data, true) ?? [];
}

// 2. Логика сортировки
$sort_by = $_GET['sort'] ?? 'created_at'; // По умолчанию сортируем по дате создания

usort($facts, function($a, $b) use ($sort_by) {
    if ($sort_by === 'weirdness_score') {
        return $b['weirdness_score'] <=> $a['weirdness_score']; // Числовая сортировка (от большего к меньшему)
    }
    return strcmp($a[$sort_by], $b[$sort_by]); // Алфавитная или строковая сортировка
});
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог странных фактов</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background-color: #f4f7f6; }
        h2 { color: #333; text-align: center; }
        .controls { margin-bottom: 20px; text-align: center; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .controls a { margin: 0 10px; text-decoration: none; color: #5c67f2; font-weight: bold; border-bottom: 2px dashed #5c67f2; }
        .controls a:hover { color: #333; border-bottom-style: solid; }
        
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #5c67f2; color: white; }
        tr:hover { background-color: #f9f9f9; }
        
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .cat-nature { background: #e2f9e1; color: #1e4620; }
        .cat-space { background: #e1f5fe; color: #01579b; }
        .verified { color: #2ecc71; font-weight: bold; }
        .not-verified { color: #e74c3c; }
    </style>
</head>
<body>

    <h2>📜 Архив странных фактов</h2>

    <div class="controls">
        Сортировать по: 
        <a href="?sort=title">Заголовку</a>
        <a href="?sort=category">Категории</a>
        <a href="?sort=weirdness_score">Странности</a>
        <a href="?sort=discovered_at">Дате открытия</a>
        | <a href="index.html" style="color: #e67e22;">+ Добавить факт</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Заголовок</th>
                <th>Категория</th>
                <th>Описание</th>
                <th>Странность</th>
                <th>Дата</th>
                <th>Проверен</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($facts)): ?>
                <tr><td colspan="6" style="text-align:center;">Данных пока нет. Будьте первым, кто добавит факт!</td></tr>
            <?php else: ?>
                <?php foreach ($facts as $fact): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($fact['title']) ?></strong></td>
                        <td><span class="badge cat-<?= strtolower($fact['category']) ?>"><?= $fact['category'] ?></span></td>
                        <td><?= nl2br(htmlspecialchars($fact['full_description'])) ?></td>
                        <td style="text-align:center; font-weight:bold;"><?= $fact['weirdness_score'] ?>/10</td>
                        <td><?= date('d.m.Y', strtotime($fact['discovered_at'])) ?></td>
                        <td style="text-align:center;">
                            <?= $fact['is_verified'] ? '<span class="verified">✔</span>' : '<span class="not-verified">✘</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
```

## Контрольные вопросы

1. Какие существуют методы отправки данных из формы на сервер? Какие методы поддерживает HTML-форма?

Существует множество методов (GET, POST, PUT, DELETE, PATCH, HEAD и др.). Стандартные HTML-формы поддерживают только GET и POST.

2. Какие глобальные переменные используются для доступа к данным формы в PHP?

Для доступа к данным используются суперглобальные ассоциативные массивы:

- $_GET — для данных, отправленных методом GET.

- $_POST — для данных, отправленных методом POST.

- $_FILES — для загруженных файлов.

- $_REQUEST — содержит данные из $_GET, $_POST и $_COOKIE одновременно.

3. Как обеспечить безопасность при обработке данных из формы (например, защититься от XSS)?

Для защиты данных необходимо соблюдать два правила:

- Фильтрация (на входе): Проверять данные на соответствие типу и длине. Использовать filter_var() для очистки email, чисел и URL.

- Экранирование (на выходе): Главный способ защиты от XSS — использование функции htmlspecialchars() при выводе данных в браузер. Она превращает опасные символы вроде <script> в обычный текст, который не может быть исполнен браузером.
