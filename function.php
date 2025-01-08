<?php

$host = "localhost";     
$user = "root";     
$pass = "";     
$db = "easyride"; //Nama Database 

// Melakukan koneksi ke database 
$koneksi = mysqli_connect($host, $user, $pass, $db);
if(!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Registrasi   
if (isset($_POST['register'])) {
    try {
        // Ambil data dari form
        $userName = strtolower(stripslashes($_POST['userName']));
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm'];
        $tglLahir = $_POST['tglLahir'];
        $noTelepon = $_POST['noTelepon'];
        $role = "user";

        // Validasi konfirmasi kata sandi
        if ($confirm !== $password) {
            throw new Exception('Konfirmasi kata sandi tidak cocok');
        }

        // Cek apakah username sudah ada
        $cekUsername = mysqli_query($koneksi, "SELECT user_name FROM user WHERE user_name = '$userName'");
        if (mysqli_fetch_assoc($cekUsername)) {
            throw new Exception('Nama pengguna sudah terdaftar');
        }

        // Enkripsi password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Simpan data ke database
        $query = "INSERT INTO user (user_name, email, password, tgl_lahir, no_telepon, role) 
                  VALUES ('$userName', '$email', '$passwordHash', '$tglLahir', '$noTelepon', '$role')";
        $insert = mysqli_query($koneksi, $query);

        if (!$insert) {
            throw new Exception('Pendaftaran gagal: ' . mysqli_error($koneksi));
        }

        // Berhasil registrasi
        echo "<script>
            alert('Pendaftaran berhasil');
            window.location.href = 'login.php';
        </script>";

    } catch (Exception $e) {
        // Menangkap exception dan menampilkan pesan error
        echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.location.href = 'daftar.php';
        </script>";
    }
}

// Login
if (isset($_POST["login"])) {
    try {
        // Ambil data dari form
        $userName = $_POST["userName"];
        $password = $_POST["password"];

        // Cek apakah username ada di database
        $cekdb = mysqli_query($koneksi, "SELECT * FROM user WHERE user_name = '$userName'");
        if (mysqli_num_rows($cekdb) === 0) {
            throw new Exception('Username tidak ditemukan');
        }

        // Ambil data pengguna
        $data = mysqli_fetch_assoc($cekdb);

        // Verifikasi password
        if (!password_verify($password, $data["password"])) {
            throw new Exception('Password salah');
        }

        // Set session dan arahkan ke halaman sesuai role
        $_SESSION["role"] = $data["role"];
        $_SESSION["userName"] = $data["user_name"];

        if ($data["role"] === "admin") {
            echo "<script>
                alert('Login Berhasil');
                window.location.href = 'dashboard/index.php';
            </script>";
        } else if ($data["role"] === "user") {
            echo "<script>
                alert('Login Berhasil');
                window.location.href = 'index.php';
            </script>";
        }
    } catch (Exception $e) {
        // Menangkap exception dan menampilkan pesan error
        $error = true;
        echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.location.href = 'login.php';
        </script>";
    }
}

// Fungsi mengirim data hubungi kami
if (isset($_POST['kirim'])) {
    try {
        // Ambil data dari form
        $namaPengguna = strtolower(stripslashes($_POST['namaPengguna']));
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $subjek = $_POST['subjek'];
        $pesan = $_POST['pesan'];

        // Query untuk menyimpan data
        $insert = mysqli_query($koneksi, "INSERT INTO masukan (user_name, email, subjek, pesan) 
                                           VALUES ('$namaPengguna', '$email', '$subjek', '$pesan')");
        if (!$insert) {
            throw new Exception('Gagal mengirim data: ' . mysqli_error($koneksi));
        }

        // Berhasil mengirim data
        echo "<script>
            alert('Data berhasil dikirim');
            window.location.href = 'index.php';
        </script>";
    } catch (Exception $e) {
        // Menangkap exception dan menampilkan pesan error
        echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.location.href = 'index.php';
        </script>";
    }
}
?>
