<?php 
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in'])) {
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

// Ambil data mahasiswa berdasarkan ID
$id = $_GET['id'];
$query = "SELECT * FROM mahasiswa WHERE id = $id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $npm = mysqli_real_escape_string($conn, $_POST['npm']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $program_studi = mysqli_real_escape_string($conn, $_POST['program_studi']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $current_foto = $row['foto'];

    // Handle file upload
    $foto = $current_foto;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Hapus foto lama jika ada
        if (!empty($current_foto) && file_exists($target_dir . $current_foto)) {
            unlink($target_dir . $current_foto);
        }

        $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('foto_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto = $new_filename;
            }
        }
    }

    // Handle remove photo request
    if (isset($_POST['remove_foto']) && $_POST['remove_foto'] == '1') {
        if (!empty($current_foto) && file_exists($target_dir . $current_foto)) {
            unlink($target_dir . $current_foto);
        }
        $foto = '';
    }

    // Update data di tabel mahasiswa
    $query = "UPDATE mahasiswa SET 
              npm = '$npm', 
              nama = '$nama', 
              program_studi = '$program_studi', 
              email = '$email', 
              alamat = '$alamat', 
              foto = '$foto' 
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        // Sinkronisasi email di tabel users
        $updateUser = "UPDATE users SET email = '$email' WHERE idMhs = $id";
        mysqli_query($conn, $updateUser);

        header("Location: index.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa</title>
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

        /* Form Styles */
        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 500;
            color: var(--dark-color);
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(190, 189, 0, 0.2);
            background-color: white;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* Photo Upload Styles */
        .photo-upload {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 1.8rem;
        }

        .photo-preview {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            border: 3px solid var(--light-color);
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }

        .upload-controls {
            flex: 1;
        }

        .file-input {
            display: none;
        }

        .file-label {
            display: inline-block;
            padding: 10px 15px;
            background-color: var(--accent-color);
            color: var(--dark-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-align: center;
            margin-top: 10px;
        }

        .file-label:hover {
            background-color: #d4d300;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .remove-photo {
            display: inline-block;
            padding: 10px 15px;
            background-color: var(--danger-color);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-align: center;
            margin-top: 10px;
            margin-left: 10px;
            border: none;
            font-family: inherit;
        }

        .remove-photo:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 2.5rem;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }

        .btn i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .btn.submit {
            background-color: var(--accent-color);
            color: var(--dark-color);
        }

        .btn.submit:hover {
            background-color: #d4d300;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.15);
        }

        .btn.cancel {
            background-color: var(--gray-color);
            color: white;
        }

        .btn.cancel:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.15);
        }

        /* Error Message */
        .error-message {
            color: var(--danger-color);
            background-color: #fdecea;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--danger-color);
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
            
            .photo-upload {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 12px;
            }
            
            .btn {
                width: 100%;
                padding: 12px;
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
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="create.php"><i class="fas fa-user-plus"></i> Tambah Mahasiswa</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="card">
                <h2><i class="fas fa-user-edit"></i> Edit Data Mahasiswa</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    
                    <!-- Photo Upload Section -->
                    <div class="photo-upload">
                        <?php if (!empty($row['foto'])): ?>
                            <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Foto Mahasiswa" class="photo-preview" id="photoPreview">
                        <?php else: ?>
                            <img src="assets/default-profile.png" alt="Foto Default" class="photo-preview" id="photoPreview">
                        <?php endif; ?>
                        
                        <div class="upload-controls">
                            <input type="file" id="foto" name="foto" class="file-input" accept="image/*">
                            <label for="foto" class="file-label">
                                <i class="fas fa-camera"></i> Pilih Foto
                            </label>
                            <?php if (!empty($row['foto'])): ?>
                                <button type="button" class="remove-photo" id="removePhoto">
                                    <i class="fas fa-trash"></i> Hapus Foto
                                </button>
                                <input type="hidden" name="remove_foto" id="removeFotoInput" value="0">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="npm"><i class="fas fa-id-card"></i> NPM</label>
                        <input type="text" id="npm" name="npm" value="<?= htmlspecialchars($row['npm']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nama"><i class="fas fa-user"></i> Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($row['nama']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="program_studi"><i class="fas fa-graduation-cap"></i> Program Studi</label>
                        <select id="program_studi" name="program_studi" required>
                            <option value="SI" <?= $row['program_studi'] == 'SI' ? 'selected' : ''; ?>>Sistem Informasi</option>
                            <option value="TI" <?= $row['program_studi'] == 'TI' ? 'selected' : ''; ?>>Teknik Informatika</option>
                            <option value="RPL" <?= $row['program_studi'] == 'RPL' ? 'selected' : ''; ?>>Rekayasa Perangkat Lunak</option>
                            <option value="MI" <?= $row['program_studi'] == 'MI' ? 'selected' : ''; ?>>Manajemen Informatika</option>
                            <option value="PI" <?= $row['program_studi'] == 'PI' ? 'selected' : ''; ?>>Pendidikan Informatika</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($row['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat"><i class="fas fa-map-marker-alt"></i> Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($row['alamat']); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn submit"><i class="fas fa-save"></i> Update</button>
                        <a href="index.php" class="btn cancel"><i class="fas fa-times"></i> Batal</a>
                    </div>
                </form>
            </div>
        </main>

        <footer>
            <p>&copy; <?= date('Y'); ?> Sistem Informasi Mahasiswa. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Preview foto sebelum upload
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Hapus foto
        document.getElementById('removePhoto')?.addEventListener('click', function() {
            document.getElementById('photoPreview').src = 'assets/default-profile.png';
            document.getElementById('removeFotoInput').value = '1';
            document.getElementById('foto').value = '';
        });
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>