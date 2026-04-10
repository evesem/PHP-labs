<?php
declare(strict_types=1);

interface TransactionStorageInterface {
    public function addTransaction(Transaction $transaction): void;
    
    // В предыдущих заданиях ID был строкой "TX-1001", 
    // но согласно вашему ТЗ в интерфейсе используем int.
    public function removeTransactionById(int $id): void;
    
    public function getAllTransactions(): array;
    
    public function findById(int $id): ?Transaction;
}

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

class TransactionManager {
    // Теперь здесь указан ИНТЕРФЕЙС
    public function __construct(
        private TransactionStorageInterface $repository
    ) {}
}
