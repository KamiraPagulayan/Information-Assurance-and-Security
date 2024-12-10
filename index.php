<?php
// Database connection
$host = 'localhost';  // Your database host (use 'localhost' for local XAMPP)
$db = 'activity1_db'; // Replace with your actual database name
$user = 'root';       // Your MySQL username
$pass = '';           // Your MySQL password (empty for default XAMPP setup)
$charset = 'utf8mb4';

// Create PDO instance for database connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Try connecting to the database
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create the table if it doesn't exist
try {
    $createTableQuery = "
    CREATE TABLE IF NOT EXISTS personaldata (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lastname VARCHAR(255) NOT NULL,
        rstname VARCHAR(255) NOT NULL,
        middlename VARCHAR(255) NOT NULL
    )";
    $pdo->exec($createTableQuery);
} catch (\PDOException $e) {
    die("Table creation failed: " . $e->getMessage());
}

// Insert data into the database
if (isset($_POST['lastname'])) {
    $ln = $_POST['lastname'];
    $fn = $_POST['rstname'];
    $mn = $_POST['middlename'];

    $sql = "INSERT INTO personaldata (lastname, rstname, middlename) VALUES (:ln, :fn, :mn)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['ln' => $ln, 'fn' => $fn, 'mn' => $mn]);

    echo "<br>Data inserted successfully!<hr>";
}

// Delete data from the database
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $sql = "DELETE FROM personaldata WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    echo "<br>Data deleted successfully!<hr>";
    header("Location: index.php"); // Redirect to avoid re-submitting on refresh
    exit;
}

// Edit data - Populate fields with current data for editing
$ln = $fn = $mn = "";
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT id, lastname, rstname, middlename FROM personaldata WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    $ln = $row['lastname'];
    $fn = $row['rstname'];
    $mn = $row['middlename'];
}

// Update data in the database
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $ln = $_POST['lastname'];
    $fn = $_POST['rstname'];
    $mn = $_POST['middlename'];

    $sql = "UPDATE personaldata SET lastname = :ln, rstname = :fn, middlename = :mn WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['ln' => $ln, 'fn' => $fn, 'mn' => $mn, 'id' => $id]);

    echo "<br>Data updated successfully!<hr>";
    header("Location: index.php"); // Redirect to avoid re-submitting on refresh
    exit;
}

// Retrieve all records from the database
$sql = "SELECT id, lastname, rstname, middlename FROM personaldata";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Application</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f0fa; /* Pastel lavender background */
            margin: 0;
            padding: 20px;
            color: #333;
        }

        header {
            text-align: center;
            background-color: #ffdde1; /* Light pastel pink */
            color: #4a4a4a;
            padding: 15px 0;
            font-size: 24px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        /* Left Section: Form */
        .form-container {
            background-color: #e7f5fe; /* Light pastel blue */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 45%;
        }

        .form-container h3 {
            text-align: center;
            color: #4a90e2;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group label {
            flex: 1;
            margin-right: 10px;
            color: #555;
            font-weight: bold;
        }

        .form-group input[type="text"] {
            flex: 2;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #ffd5cd; /* Pastel peach */
            color: #333;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        .form-container button:hover {
            background-color: #ffb3a7;
        }

        /* Right Section: Table */
        .table-container {
            background-color: #fffbe6; /* Pastel yellow */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 50%;
        }

        .table-container h3 {
            text-align: center;
            color: #ffc107;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        table th {
            background-color: #ffcce7; /* Light pastel pink */
            color: #4a4a4a;
        }

        table tr:nth-child(even) {
            background-color: #fdf2f8; /* Very light pastel pink */
        }

        table tr:hover {
            background-color: #ffe9ec; /* Soft pastel pink highlight */
        }

        table td a {
            text-decoration: none;
            color: #4a90e2; /* Pastel blue */
            font-weight: bold;
        }

        table td a:hover {
            color: #003366; /* Darker blue */
        }
    </style>
</head>
<body>

<header>
    Personal Information Manager
</header>

<div class="container">
    <!-- Left Section: Form -->
    <div class="form-container">
        <h3>Add / Edit Information</h3>
        <form method="POST">
            <?php if (isset($_GET['edit'])): ?>
                <input type="hidden" name="id" value="<?= $id; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="lastname">Lastname:</label>
                <input type="text" id="lastname" name="lastname" value="<?= $ln ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="rstname">Firstname:</label>
                <input type="text" id="rstname" name="rstname" value="<?= $fn ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="middlename">Middlename:</label>
                <input type="text" id="middlename" name="middlename" value="<?= $mn ?? ''; ?>" required>
            </div>
            <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : ''; ?>">
                <?= isset($_GET['edit']) ? 'Update' : 'Submit'; ?>
            </button>
        </form>
    </div>

    <!-- Right Section: Table -->
    <div class="table-container">
        <h3>Existing Records</h3>
        <table>
            <thead>
                <tr>
                    <th>LN</th>
                    <th>FN</th>
                    <th>MN</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= $row['lastname']; ?></td>
                    <td><?= $row['rstname']; ?></td>
                    <td><?= $row['middlename']; ?></td>
                    <td>
                        <a href="?edit=<?= $row['id']; ?>">Edit</a> |
                        <a href="?del=<?= $row['id']; ?>">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

