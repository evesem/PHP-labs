# Лабораторная работа №5. Объектно-ориентированное программирование в PHP

## Цель работы
Освоить основы объектно-ориентированного программирования в PHP на практике. Научиться создавать собственные классы, использовать инкапсуляцию для защиты данных, разделять ответственность между классами, а также применять интерфейсы для построения гибкой архитектуры приложения.

## Задание 1. Включение строгой типизации

Добавляю первую строку этого файла, которая включает строгую типизацию

```php
<?php

declare(strict_types=1);
```

## Задание 2. Класс Transaction

```php
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
```

## Задание 3. Класс TransactionRepository

```php
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
                // Сбрасываем индексы массива
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
```

## Задание 4. Класс TransactionManager

```php
class TransactionManager {

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
```

## Задание 5. Класс TransactionTableRenderer

```php
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
```

## Задание 6. Начальные данные

```php
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
```

## Задание 7. Интерфейс TransactionStorageInterface

### 1. Создание интерфейса TransactionStorageInterface

```php
declare(strict_types=1);

interface TransactionStorageInterface {
    public function addTransaction(Transaction $transaction): void;
    
    public function removeTransactionById(int $id): void;
    
    public function getAllTransactions(): array;
    
    public function findById(int $id): ?Transaction;
}
```

### 2. Реализация интерфейса в TransactionRepository

```php
class TransactionRepository implements TransactionStorageInterface {
    private array $transactions = [];

    public function addTransaction(Transaction $transaction): void {
        $this->transactions[] = $transaction;
    }

    public function removeTransactionById(int $id): void {
        // Приводим ID к строке для сравнения, если в объекте хранится строка
        $stringId = (string)$id; 
        foreach ($this->transactions as $key => $transaction) {
            if ($transaction->getId() === $stringId) {
                unset($this->transactions[$key]);
                $this->transactions = array_values($this->transactions);
                return;
            }
        }
    }

    public function getAllTransactions(): array {
        return $this->transactions;
    }

    public function findById(int $id): ?Transaction {
        $stringId = (string)$id;
        foreach ($this->transactions as $transaction) {
            if ($transaction->getId() === $stringId) {
                return $transaction;
            }
        }
        return null;
    }
}
```

### 3. Обновление TransactionManager

```php
class TransactionManager {
    // Теперь здесь указан ИНТЕРФЕЙС
    public function __construct(
        private TransactionStorageInterface $repository
    ) {}
}
```

## Контрольные вопросы

1. Строгая типизация в PHP

Строгая типизация (declare(strict_types=1);) заставляет интерпретатор проверять соответствие типов данных. Она исключает передачу неверных данных (например, строки вместо числа)

2. Класс в ООП и его компоненты

Класс — это шаблон, по которому создаются объекты. Он описывает, какими свойствами будет обладать объект и что он сможет делать.

Основные компоненты:

- Свойства (Properties): Переменные внутри класса (данные объекта).

- Методы (Methods): Функции внутри класса (поведение объекта).

- Конструктор (__construct): Специальный метод, который вызывается автоматически при создании нового объекта.

- Константы: Постоянные значения, относящиеся к классу.

3. Полиморфизм в PHP
   
Полиморфизм — это способность объектов с разным кодом иметь одинаковый интерфейс взаимодействия.

Обычно реализуется через наследование или интерфейсы.

4. Интерфейс vs Абстрактный класс

| Что? | Абстрактный класс | Интерфейс |
|------|-------------------|-----------|
| Код | Может содержать готовые функции. | Только названия функций (пустые скобки). |
| Кол-во | У класса может быть только 1 родитель. | Класс может соблюдать 10 интерфейсов сразу. |

5. Преимущества интерфейсов в архитектуре

- Гибкость (Слабая связность): Вы можете заменить одну реализацию другой (например, сменить сохранение в базу данных на сохранение в файл), не меняя остальной код приложения.

- Масштабируемость: Легко добавлять новые функции. Если в лабе нужно добавить новый тип сущности, вы просто создаете класс по готовому интерфейсу, и система его сразу «узнает».

- Безопасность: Интерфейс гарантирует, что у объекта точно есть необходимые методы, что исключает ошибки вызова несуществующих функций.