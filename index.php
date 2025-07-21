<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Koneksi ke database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'uas2025';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil parameter pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query pencarian
$query = "SELECT * FROM mahasiswa";
if (!empty($search)) {
    $query .= " WHERE npm LIKE '%$search%' OR 
                nama LIKE '%$search%' OR 
                program_studi LIKE '%$search%' OR 
                email LIKE '%$search%' OR 
                alamat LIKE '%$search%'";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Mahasiswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        :root {
            --primary-color: #004754;
            --secondary-color: #003d47;
            --accent-color: #bebd00;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --danger-color: #e74c3c;
            --text-color: #34495e;
            --gray-color: #95a5a6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 0 0 10px 10px;
            position: relative;
        }

        header h1 {
            text-align: center;
            margin-bottom: 1rem;
            font-weight: 600;
            font-size: 2.2rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }

        nav ul {
            display: flex;
            justify-content: center;
            list-style: none;
            flex-wrap: wrap;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: inline-block;
        }

        nav ul li a:hover {
            background-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        nav ul li.active a {
            background-color: var(--accent-color);
            color: var(--dark-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: var(--danger-color);
            color: white;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Search Box Styles */
        .search-box {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-box input {
            flex: 1;
            min-width: 200px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 2px 8px rgba(190, 189, 0, 0.2);
        }

        .search-box button {
            background-color: var(--accent-color);
            color: var(--dark-color);
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            white-space: nowrap;
        }

        .search-box button:hover {
            background-color: #d4d300;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            padding: 2rem;
            margin-bottom: 3rem;
            border: 1px solid rgba(0,0,0,0.05);
            color: var(--dark-color);
        }

        .card h2 {
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--accent-color);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            border-radius: 8px;
            overflow: hidden;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tr:hover {
            background-color: #f1f8e9;
        }

        /* Image Styles */
        .student-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Button Styles */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            white-space: nowrap;
        }

        .btn i {
            margin-right: 5px;
            font-size: 0.9rem;
        }

        .btn.edit {
            background-color: var(--accent-color);
            color: var(--dark-color);
        }

        .btn.edit:hover {
            background-color: #d4d300;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn.delete {
            background-color: var(--danger-color);
            color: white;
        }

        .btn.delete:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn.reset {
            background-color: var(--danger-color);
            color: white;
        }

        .btn.reset:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Footer Styles */
        footer {
            text-align: center;
            padding: 1.5rem 0;
            margin-top: 3rem;
            color: var(--light-color);
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.9rem;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            header {
                padding-top: 4rem;
            }
            
            .logout-btn {
                top: 10px;
                right: 10px;
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            
            nav ul {
                flex-direction: column;
                align-items: center;
            }
            
            nav ul li {
                margin: 5px 0;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 6px;
            }
            
            .btn {
                width: 100%;
                padding: 8px;
            }
            
            .search-box {
                flex-direction: column;
            }
            
            .search-box input,
            .search-box button {
                width: 100%;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistem Informasi Mahasiswa</h1>
            <a href="login.php?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <nav>
                <ul>
                    <li class="active"><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="create.php"><i class="fas fa-user-plus"></i> Tambah Mahasiswa</a></li>
                </ul>
            </nav>
        </header>

        <main>
             <div class="card">
                <h2><i class="fas fa-users"></i> Daftar Mahasiswa</h2>
                
                <!-- Search Form -->
                <form method="GET" action="" class="search-box">
                    <input type="text" name="search" placeholder="Cari mahasiswa..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit"><i class="fas fa-search"></i> Cari</button>
                    <?php if (!empty($search)): ?>
                        <a href="index.php" class="btn reset">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
                
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>NPM</th>
                            <th>Nama</th>
                            <th>Program Studi</th>
                            <th>E-mail</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?php if (!empty($row['foto'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Foto <?= htmlspecialchars($row['nama']) ?>" class="student-photo">
                                <?php else: ?>
                                    <img src="assets/default-profile.png" alt="Foto Default" class="student-photo">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['npm']); ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['program_studi']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['alamat']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?= $row['id']; ?>" class="btn edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete.php?id=<?= $row['id']; ?>" class="btn delete" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <footer>
            <p>&copy; <?= date('Y'); ?> Sistem Informasi Mahasiswa. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>