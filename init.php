<?php

require 'vendor/autoload.php';

use Faker\Factory as Faker;
use Dotenv\Dotenv;
final class Init
{
    private mysqli $conn;

    public function __construct()
    {
        $this->loadEnv();
        $this->connect();
        $this->create();
        $this->fill();
    }

    private function loadEnv(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
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

    private function create()
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

    public function __destruct()
    {
        $this->conn->close();
    }
}

$init = new Init();
$init->get('');
