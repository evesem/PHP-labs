<?php

declare(strict_types=1);

class Transaction {
    // Приватные свойства
    private string $id;
    private DateTime $date;
    private float $amount;
    private string $description;
    private string $merchant;

    // Конструктор для инициализации свойств
    public function __construct(
        string $id, 
        DateTime $date, 
        float $amount, 
        string $description, 
        string $merchant
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->amount = $amount;
        $this->description = $description;
        $this->merchant = $merchant;
    }

    // Метод для получения разницы в днях
    public function getDaysSinceTransaction(): int {
        $today = new DateTime();
        $interval = $this->date->diff($today);
        
        // Возвращаем общее количество дней разницы
        return (int)$interval->format('%a');
    }

    // Getter-методы
    public function getId(): string {
        return $this->id;
    }

    public function getDate(): DateTime {
        return $this->date;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getMerchant(): string {
        return $this->merchant;
    }
}

class TransactionRepository {
    /**
     * Приватный массив для хранения объектов типа Transaction.
     * @var Transaction[]
     */
    private array $transactions = [];

    /**
     * Добавляет новую транзакцию в коллекцию.
     */
    public function addTransaction(Transaction $transaction): void {
        $this->transactions[] = $transaction;
    }

    /**
     * Удаляет транзакцию по её уникальному идентификатору.
     */
    public function removeTransactionById(string $id): void {
        foreach ($this->transactions as $key => $transaction) {
            if ($transaction->getId() === $id) {
                unset($this->transactions[$key]);
                // Сбрасываем индексы массива, чтобы не было дырок
                $this->transactions = array_values($this->transactions);
                return;
            }
        }
    }

    /**
     * Возвращает полный список всех транзакций.
     * @return Transaction[]
     */
    public function getAllTransactions(): array {
        return $this->transactions;
    }

    /**
     * Находит транзакцию по ID. 
     * Возвращает объект Transaction или null, если транзакция не найдена.
     */
    public function findById(string $id): ?Transaction {
        foreach ($this->transactions as $transaction) {
            if ($transaction->getId() === $id) {
                return $transaction;
            }
        }
        return null;
    }
}

class TransactionManager {
    /**
     * Используем Property Promotion (PHP 8.0+) для объявления и инициализации репозитория.
     */
    public function __construct(
        private TransactionRepository $repository
    ) {}

    /**
     * Вычисление общей суммы всех транзакций.
     */
    public function calculateTotalAmount(): float {
        $transactions = $this->repository->getAllTransactions();
        $total = 0.0;

        foreach ($transactions as $transaction) {
            $total += $transaction->getAmount();
        }

        return $total;
    }

    /**
     * Вычисление суммы транзакций за определенный период (включительно).
     */
    public function calculateTotalAmountByDateRange(string $startDate, string $endDate): float {
        $start = new DateTime($startDate);
        $end = (new DateTime($endDate))->setTime(23, 59, 59); // До конца дня
        $total = 0.0;

        foreach ($this->repository->getAllTransactions() as $transaction) {
            $txDate = $transaction->getDate();
            if ($txDate >= $start && $txDate <= $end) {
                $total += $transaction->getAmount();
            }
        }

        return $total;
    }

    /**
     * Подсчет количества транзакций по конкретному получателю.
     */
    public function countTransactionsByMerchant(string $merchant): int {
        $count = 0;
        foreach ($this->repository->getAllTransactions() as $transaction) {
            if (strcasecmp($transaction->getMerchant(), $merchant) === 0) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Сортировка транзакций по дате (от старых к новым).
     * @return Transaction[]
     */
    public function sortTransactionsByDate(): array {
        $transactions = $this->repository->getAllTransactions();

        usort($transactions, function (Transaction $a, Transaction $b) {
            return $a->getDate() <=> $b->getDate();
        });

        return $transactions;
    }

    /**
     * Сортировка транзакций по сумме (от большей к меньшей).
     * @return Transaction[]
     */
    public function sortTransactionsByAmountDesc(): array {
        $transactions = $this->repository->getAllTransactions();

        usort($transactions, function (Transaction $a, Transaction $b) {
            // b <=> a для сортировки по убыванию
            return $b->getAmount() <=> $a->getAmount();
        });

        return $transactions;
    }
}


final class TransactionTableRenderer {
    /**
     * Формирует HTML-таблицу на основе массива объектов Transaction.
     * * @param Transaction[] $transactions
     * @return string
     */
    public function render(array $transactions): string {
        if (empty($transactions)) {
            return "<p>Транзакции не найдены.</p>";
        }

        $html = "<table border='1' style='border-collapse: collapse; width: 100%; text-align: left;'>";
        $html .= "<thead>
                    <tr style='background-color: #f2f2f2;'>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Описание</th>
                        <th>Получатель</th>
                        <th>Дней прошло</th>
                    </tr>
                  </thead>";
        $html .= "<tbody>";

        foreach ($transactions as $transaction) {
            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($transaction->getId()) . "</td>";
            $html .= "<td>" . $transaction->getDate()->format('Y-m-d H:i') . "</td>";
            $html .= "<td>" . number_format($transaction->getAmount(), 2, '.', ' ') . "</td>";
            $html .= "<td>" . htmlspecialchars($transaction->getDescription()) . "</td>";
            $html .= "<td>" . htmlspecialchars($transaction->getMerchant()) . "</td>";
            $html .= "<td>" . $transaction->getDaysSinceTransaction() . "</td>";
            $html .= "</tr>";
        }

        $html .= "</tbody>";
        $html .= "</table>";

        return $html;
    }
}

// 1. Инициализируем репозиторий
$repo = new TransactionRepository();

// 2. Создаем и добавляем 10 транзакций
$repo->addTransaction(new Transaction(
    "TX-1001", new DateTime("2023-11-01"), 120.50, "Утренний кофе", "Starbucks"
));

$repo->addTransaction(new Transaction(
    "TX-1002", new DateTime("2023-11-05"), 4500.00, "Аренда квартиры", "City Real Estate"
));

$repo->addTransaction(new Transaction(
    "TX-1003", new DateTime("2023-11-10"), 89.90, "Подписка на музыку", "Spotify"
));

$repo->addTransaction(new Transaction(
    "TX-1004", new DateTime("2023-11-15"), 1200.00, "Покупка продуктов", "Auchan"
));

$repo->addTransaction(new Transaction(
    "TX-1005", new DateTime("2023-11-18"), 350.00, "Ужин в ресторане", "Local Bistro"
));

$repo->addTransaction(new Transaction(
    "TX-1006", new DateTime("2023-11-20"), 15000.00, "Новый ноутбук", "Apple Store"
));

$repo->addTransaction(new Transaction(
    "TX-1007", new DateTime("2023-11-22"), 45.00, "Поездка на такси", "Uber"
));

$repo->addTransaction(new Transaction(
    "TX-1008", new DateTime("2023-11-25"), 600.00, "Спортивный инвентарь", "Decathlon"
));

$repo->addTransaction(new Transaction(
    "TX-1009", new DateTime("2023-11-28"), 250.00, "Билеты в кино", "Cinema City"
));

$repo->addTransaction(new Transaction(
    "TX-1010", new DateTime("2023-12-01"), 2100.30, "Авиабилеты", "Lufthansa"
));
