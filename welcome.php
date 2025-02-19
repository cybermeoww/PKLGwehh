<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_perangkat = $_POST['nama-perangkat'];
    $lokasi = $_POST['lokasi'];
    $status = $_POST['status'];
    $keterangan = $_POST['keterangan'];

    $sql = "INSERT INTO perangkat (nama_perangkat, lokasi, status, keterangan) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nama_perangkat, $lokasi, $status, $keterangan);

    if ($stmt->execute()) {
        $success_message = "Data berhasil disimpan!";
    } else {
        $success_message = "Gagal menyimpan data!";
    }
}

$sql = "SELECT * FROM perangkat";
$result = $conn->query($sql);
?>

<html>

<head>
    <title>
        Aplikasi Pemeliharaan Perangkat Jaringan PLN
    </title>
    <script src="https://cdn.tailwindcss.com">
    </script>
    <script>
        document.querySelectorAll('nav a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet" />
    <!-- Slick Carousel CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />

    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        main {
            padding-top: 90px;
        }

        /* Scrollbar untuk browser berbasis WebKit (Chrome, Edge, Safari) */
        ::-webkit-scrollbar {
            width: 8px;
            /* Lebar scrollbar */
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            /* Warna background track */
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #4A90E2;
            /* Warna scrollbar */
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #357ABD;
            /* Warna scrollbar saat hover */
        }

        .carousel {
            width: 100%;
            margin: 0 auto;
            height: 500px;
            /* Atur tinggi tetap di sini */
            overflow: hidden;
            /* Sembunyikan bagian gambar yang melebihi tinggi */
        }

        .carousel img {
            width: 100%;
            height: auto;
            /* Biarkan tinggi gambar menyesuaikan proporsi */
            position: relative;
            /* Agar gambar dapat diposisikan */
            top: 50%;
            /* Posisikan gambar dari tengah */
            transform: translateY(-50%);
            /* Geser gambar ke atas untuk menyesuaikan */
            border-radius: 8px;
        }

        .slick-dots {
            position: absolute;
            bottom: 10px;
            width: 100%;
            text-align: center;
        }

        .slick-dots li button:before {
            color: grey;
            /* Change the color of the dots */
            font-size: 12px;
            /* Adjust the size of the dots */
        }

        .slick-dots li.slick-active button:before {
            color: blue;
            /* Change the color of the active dot */
        }
    </style>
</head>

<body class="bg-gray-100">
    <header class="bg-cyan-300 text-white p-4 fixed w-full top-0 shadow-md z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <img src="Logo_PLN.png" alt="Logo PLN" class="h-10">
                <h1 class="text-2xl font-bold">Pemeliharaan Perangkat Jaringan PLN</h1>
            </div>
            <nav>
                <ul class="flex space-x-4">
                    <li>
                        <a class="hover:underline" href="#beranda">
                            <i class="fas fa-home"></i>
                        </a>
                    </li>
                    <li>
                        <a class="hover:underline" href="#daftar-perangkat">
                            <i class="fas fa-list"></i>
                        </a>
                    </li>
                    <li>
                        <a class="hover:underline" href="#form-pemeliharaan">
                            <i class="fas fa-tools"></i>
                        </a>
                    </li>
                    <li>
                        <a class="hover:underline" href="login.php">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto mt-8">
        <section class="mb-8" id="beranda">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4 text-center" style="color: #357ABD;">
                    Selamat Datang di Aplikasi Pemeliharaan Perangkat Jaringan PLN
                </h2>
                <p class="text-gray-700 text-center" style="color:rgb(0, 49, 99);">
                    Aplikasi ini membantu Anda dalam mengelola dan memelihara perangkat jaringan PLN di STI Sumut.
                </p>
                <!-- Carousel -->
                <div class="carousel mt-4">
                    <div><img src="fa-nataru.png" alt="Gambar 1" class="rounded-lg w-full">
                    </div>
                    <div><img src="kalender.jpg" alt="Gambar 2" class="rounded-lg w-full">
                    </div>
                    <div><img src="banner-hln.jpg" alt="Gambar 3" class="rounded-lg w-full"></div>
                </div>
            </div>
        </section>

        <section class="mb-8" id="daftar-perangkat">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4">
                    Daftar Perangkat
                </h2>
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">No</th>
                            <th class="py-2 px-4 border-b">Nama Perangkat</th>
                            <th class="py-2 px-4 border-b">Lokasi</th>
                            <th class="py-2 px-4 border-b">Status</th>
                            <th class="py-2 px-4 border-b">Keterangan</th>
                            <th class="py-2 px-4 border-b">Aksi</th> <!-- Kolom baru untuk tombol hapus -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            $no = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                <td class='py-2 px-4 border-b'>$no</td>
                <td class='py-2 px-4 border-b'>{$row['nama_perangkat']}</td>
                <td class='py-2 px-4 border-b'>{$row['lokasi']}</td>
                <td class='py-2 px-4 border-b'>{$row['status']}</td>
                <td class='py-2 px-4 border-b'>{$row['keterangan']}</td>
                <td class='py-2 px-4 border-b'>
                    <form method='POST' action='hapus.php' onsubmit='return confirm(\"Apakah Anda yakin ingin menghapus data ini?\");'>
                        <input type='hidden' name='id' value='{$row['id']}'>
                        <button type='submit' class='bg-red-500 text-white px-2 py-1 rounded'>Hapus</button>
                    </form>
                </td>
              </tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr><td colspan='6' class='py-2 px-4 border-b text-center'>Tidak ada data</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
        <!-- Form Pemeliharaan -->
        <section class="mb-8" id="form-pemeliharaan">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4">
                    Form Maintenance
                </h2>
                <?php if (!empty($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <span class="block sm:inline"><?php echo $success_message; ?></span>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-gray-700" for="nama-perangkat">
                            Nama Perangkat
                        </label>
                        <input class="w-full p-2 border border-gray-300 rounded mt-1" id="nama-perangkat"
                            name="nama-perangkat" type="text" required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="lokasi">
                            Lokasi
                        </label>
                        <input class="w-full p-2 border border-gray-300 rounded mt-1" id="lokasi" name="lokasi"
                            type="text" required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="status">
                            Status
                        </label>
                        <select class="w-full p-2 border border-gray-300 rounded mt-1" id="status" name="status"
                            required>
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="keterangan">
                            Keterangan
                        </label>
                        <textarea class="w-full p-2 border border-gray-300 rounded mt-1" id="keterangan"
                            name="keterangan"></textarea>
                    </div>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded" type="submit">
                        Submit
                    </button>
                </form>
            </div>
        </section>
    </main>
    <footer class="bg-cyan-900 text-white p-4 mt-8">
        <div class="container mx-auto text-center">
            <p>
                © 2025 PLN STI Sumut. All rights reserved.
            </p>
        </div>
    </footer>
    <!-- Slick Carousel JS -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.carousel').slick({
                dots: true,
                infinite: true,
                speed: 300,
                slidesToShow: 1,
                adaptiveHeight: false,
                autoplay: true,
                autoplaySpeed: 2000,
            });
        });
    </script>
</body>

</html>