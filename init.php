<?php

require 'vendor/autoload.php';

use Faker\Factory as Faker;
use Dotenv\Dotenv;

/**
 * Класс Init для работы с базой данных MySQL.
 * Содержит методы для создания таблицы, заполнения её данными и выборки данных.
 *
 * @final Запрещает наследование от этого класса.
 */
final class Init
{
    /**
     * @var mysqli $conn Подключение к базе данных.
     */
    private mysqli $conn;

    /**
     * Конструктор класса.
     * Выполняет загрузку переменных окружения, подключение к базе данных,
     * создание таблицы и её заполнение тестовыми данными.
     */
    public function __construct()
    {
        $this->loadEnv();
        $this->connect();
        $this->create();
        $this->fill();
    }

    /**
     * Загрузка переменных окружения из файла .env.
     * Использует библиотеку `vlucas/phpdotenv`.
     *
     * @return void
     */
    private function loadEnv(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }

    /**
     * Устанавливает подключение к базе данных MySQL с использованием данных из .env файла.
     * В случае ошибки подключения выводит сообщение об ошибке.
     *
     * @return void
     */
    private function connect(): void
    {
        $host = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $dbname = $_ENV['DB_NAME'];

        try {
            $this->conn = new mysqli($host, $username, $password, $dbname);
        } catch (Exception $ex) {
            echo 'Ошибка подключения к БД: ' . $ex->getMessage();
        }
    }

    /**
     * Создаёт таблицу `test` в базе данных, если она не существует.
     *
     * @return void
     */
    private function create(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS test (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            normal TEXT NOT NULL,
            success TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ";

        if ($this->conn->query($sql) === true) {
            echo "Таблица 'test' успешно создана!\n";
        } else {
            echo "Ошибка при создании таблицы: " . $this->conn->error;
        }
    }

    /**
     * Заполняет таблицу `test` случайными данными с использованием библиотеки `Faker`.
     * Вставляет 10 записей.
     *
     * @return void
     */
    private function fill(): void
    {
        $faker = Faker::create();
        $recordCount = 10;

        $stmt = $this->conn->prepare("INSERT INTO test (name, normal, success) VALUES (?, ?, ?)");

        if (!$stmt) {
            echo "Ошибка подготовки запроса: " . $this->conn->error;
            return;
        }

        for ($i = 0; $i < $recordCount; $i++) {
            $name = $faker->name();
            $normal = $faker->sentence();
            $success = $faker->paragraph();

            $stmt->bind_param("sss", $name, $normal, $success);

            if ($stmt->execute()) {
                echo "Запись $i успешно добавлена!\n";
            } else {
                echo "Ошибка при добавлении записи $i: " . $stmt->error . "\n";
            }
        }

        $stmt->close();
    }

    /**
     * Выполняет поиск в таблице `test` по полям `normal` и `success`.
     * Ищет записи, содержащие переданную строку `queryString`.
     *
     * @param string $queryString Строка для поиска.
     *
     * @return void
     */
    public function get(string $queryString): void
    {
        $stmt = $this->conn->prepare("SELECT * FROM test WHERE normal LIKE ? OR success LIKE ?");

        if (!$stmt) {
            echo "Ошибка подготовки запроса: " . $this->conn->error;
            return;
        }

        $likeQuery = '%' . $queryString . '%';
        $stmt->bind_param("ss", $likeQuery, $likeQuery);

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "ID: " . $row["id"] . " | Name: " . $row["name"] . " | Normal: " . $row["normal"] . " | Success: " . $row["success"] . " | Created At: " . $row["created_at"] . "\n";
            }
        } else {
            echo "Записи, соответствующие запросу '$queryString', не найдены.\n";
        }

        $stmt->close();
    }

    /**
     * Деструктор класса.
     * Закрывает соединение с базой данных при уничтожении объекта.
     */
    public function __destruct()
    {
        $this->conn->close();
    }
}

// Инициализация класса и выполнение поиска
$init = new Init();

$init->get('');
