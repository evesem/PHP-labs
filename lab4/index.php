<?php
declare(strict_types=1);

$transactions = [
    [
        "id" => 1,
        "date" => "2023-12-15",
        "amount" => 1500.00,
        "description" => "Зарплата за декабрь",
        "merchant" => "ООО Работодатель",
    ],
    [
        "id" => 2,
        "date" => "2023-12-20",
        "amount" => 350.75,
        "description" => "Продукты на неделю",
        "merchant" => "Супермаркет Пятерочка",
    ],
    [
        "id" => 3,
        "date" => "2024-01-05",
        "amount" => 1200.00,
        "description" => "Аренда квартиры",
        "merchant" => "ИП Иванов",
    ],
    [
        "id" => 4,
        "date" => "2024-01-10",
        "amount" => 500.00,
        "description" => "Оплата интернета",
        "merchant" => "Ростелеком",
    ],
    [
        "id" => 5,
        "date" => "2024-02-14",
        "amount" => 2500.50,
        "description" => "Покупка техники",
        "merchant" => "DNS",
    ],
];

/**
 * Вычисляет общую сумму всех транзакций
 */
function calculateTotalAmount(array $transactions): float
{
    $total = 0.0;
    foreach ($transactions as $transaction) {
        $total += $transaction['amount'];
    }
    return $total;
}

$totalAmount = calculateTotalAmount($transactions);

/**
 * Ищет транзакцию по названию мерчанта (получателя)
 */
function findTransactionByMerchant(array $transactions, string $merchant): ?array
{
    foreach ($transactions as $transaction) {
        if (strtolower($transaction['merchant']) === strtolower($merchant)) {
            return $transaction;
        }
    }
    return null;
}

$foundByMerchant = findTransactionByMerchant($transactions, "Ростелеком");

/**
 * Ищет транзакцию по ID
 */
function findTransactionById (int $id): ?array
{
    global $transactions;
    foreach ($transactions as $transaction) {
        if ($transaction['id'] === $id) {
            return $transaction;
        }
    }
    return null;
}

$foundById = findTransactionById(3);

/**
 * Вычисляет количество дней, прошедших с даты транзакции
 */
function daysSinceTransaction(string $date): int
{
    $transactionDate = new DateTime($date);
    $currentDate = new DateTime();
    $interval = $currentDate->diff($transactionDate);
    return (int)$interval->format('%a');
}

/**
 * Добавляет новую транзакцию в массив
 */
function addTransaction(int $id, string $date, float $amount, string $description, string $merchant): void
{
    global $transactions;
    $transactions[] = [
        "id" => $id,
        "date" => $date,
        "amount" => $amount,
        "description" => $description,
        "merchant" => $merchant,
    ];
}

addTransaction(6, "2024-03-01", 800.00, "Покупка одежды", "Магазин одежды");
addTransaction(7, "2024-03-05", 300.00, "Оплата мобильной связи", "МТС");

$transactionsByDate = $transactions;  // для сортировки по дате
$transactionsByAmount = $transactions; // для сортировки по сумме

/**
 * Сортирует транзакции по дате (от новых к старым)
 */
usort($transactionsByDate, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});


/**
 * Сортирует транзакции по сумме (от большей к меньшей)
 */
usort($transactionsByAmount, function ($a, $b) {
    return $b['amount'] <=> $a['amount'];
});

?>



<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Транзакции</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Список транзакций</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Описание</th>
                <th>Мерчант</th>
                <th>Дней с момента транзакции</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$transaction['id']) ?></td>
                    <td><?= htmlspecialchars($transaction['date']) ?></td>
                    <td><?= htmlspecialchars(number_format($transaction['amount'], 2)) ?></td>
                    <td><?= htmlspecialchars($transaction['description']) ?></td>
                    <td><?= htmlspecialchars($transaction['merchant']) ?></td>
                    <td><?= daysSinceTransaction($transaction['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="result-box">
        <h2>Результаты вызова функций:</h2>
        
        <p><strong>1. calculateTotalAmount():</strong> 
            <?php echo "Общая сумма всех транзакций: " . number_format($totalAmount, 2, ',', ' ') . " руб."; ?>
        </p>
        
        <p><strong>2. findTransactionByMerchant('Ростелеком'):</strong><br>
            <?php 
            if ($foundByMerchant) {
                echo "Найдена транзакция: ID {$foundByMerchant['id']}, ";
                echo "сумма: {$foundByMerchant['amount']} руб., ";
                echo "описание: {$foundByMerchant['description']}";
            } else {
                echo "Транзакция не найдена";
            }
            ?>
        </p>
        
        <p><strong>3. findTransactionById(3):</strong><br>
            <?php 
            if ($foundById) {
                echo "Найдена транзакция: {$foundById['description']}, ";
                echo "получатель: {$foundById['merchant']}, ";
                echo "сумма: {$foundById['amount']} руб.";
            } else {
                echo "Транзакция с ID 3 не найдена";
            }
            ?>
        </p>
        <h2>Сортировка транзакций</h2>
        <p><strong>Сортировка по дате (от новых к старым):</strong></p>
        <ul>
            <?php foreach ($transactionsByDate as $transaction): ?>
                <li><?= htmlspecialchars($transaction['date']) ?> - <?= htmlspecialchars($transaction['description']) ?></li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Сортировка по сумме (от большей к меньшей):</strong></p>
        <ul>
            <?php foreach ($transactionsByAmount as $transaction): ?>
                <li><?= htmlspecialchars(number_format($transaction['amount'], 2)) ?> руб. - <?= htmlspecialchars($transaction['description']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>