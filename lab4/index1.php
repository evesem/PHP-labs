

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Галерея изображений</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        
        /* Хедер */
        header {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem;
        }
        
        /* Навигация */
        nav {
            background: #555;
            color: white;
            padding: 1rem;
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: center;
        }
        
        nav ul li {
            margin: 0 1rem;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
        }
        
        /* Основной контент */
        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .gallery-section {
            background: white;
            padding: 2rem;
            border-radius: 5px;
        }
        
        .gallery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Мои любимые персы ♥</h1>
    </header>
    
    <nav>
        <ul>
            <li><a href="#">Главная</a></li>
            <li><a href="#">Галерея</a></li>
            <li><a href="#">Контакты</a></li>
        </ul>
    </nav>
    
    <main>
        <section class="gallery-section">
            <div class="gallery-header">
                <h2>Они реально крутые</h2>
            </div>
            <!-- Здесь будут отображаться изображения -->
        </section>
    </main>
</body>
</html>

<?php

$dir = 'image/';
$files = scandir($dir);

if ($files === false) {
    die("Ошибка при чтении директории");
    return;
}   

for ($i = 0; $i < count($files); $i++)
    {
        // Пропускаем текущую и родительскую директории
        if ($files[$i] === '.' || $files[$i] === '..') {
            continue;
        }
        // Проверяем, является ли элемент файлом (а не директорией)
        if (is_dir($dir .'/'. $files[$i])) {
            continue;
        } else {
            echo '<img src="' . $dir . $files[$i] . '" alt="Image" style="width: 200px; height: auto; margin: 10px;">';
    }
    }
?>