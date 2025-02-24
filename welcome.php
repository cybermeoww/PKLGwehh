<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$success_message = '';

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_perangkat = $_POST['nama-perangkat'];
    $lokasi = $_POST['lokasi'];
    $status = $_POST['status'];
    $keterangan = $_POST['keterangan'];

    // Handle gambar sebelum
    $gambar_sebelum = '';
    if ($_FILES['gambar-sebelum']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["gambar-sebelum"]["name"]);
        move_uploaded_file($_FILES["gambar-sebelum"]["tmp_name"], $target_file);
        $gambar_sebelum = $target_file;
    }

    // Handle gambar sesudah
    $gambar_sesudah = '';
    if ($_FILES['gambar-sesudah']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["gambar-sesudah"]["name"]);
        move_uploaded_file($_FILES["gambar-sesudah"]["tmp_name"], $target_file);
        $gambar_sesudah = $target_file;
    }

    $sql = "INSERT INTO perangkat (nama_perangkat, lokasi, status, keterangan, gambar_sebelum, gambar_sesudah, waktu_submit) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nama_perangkat, $lokasi, $status, $keterangan, $gambar_sebelum, $gambar_sesudah);

    if ($stmt->execute()) {
        $success_message = "Data berhasil disimpan!";
    } else {
        $success_message = "Gagal menyimpan data!";
    }
}

// Define the columns that can be sorted
$columns = array('nama_perangkat', 'lokasi', 'status', 'keterangan', 'waktu_submit');

// Get the column to sort by (default to the first column)
$column = isset($_GET['column']) && in_array($_GET['column'], $columns) ? $_GET['column'] : $columns[0];

// Get the sort order (default to ascending)
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

// Determine the icon for the current sort order
$up_or_down = ($sort_order == 'ASC') ? 'up' : 'down';
$asc_or_desc = ($sort_order == 'ASC') ? 'desc' : 'asc';

// Handle lokasi filter
$selected_lokasi = isset($_GET['lokasi']) ? $_GET['lokasi'] : ''; // Inisialisasi dengan nilai default

// Create SQL query with search and lokasi filter
$sql = "SELECT * FROM perangkat WHERE nama_perangkat LIKE ?";
if (!empty($selected_lokasi)) {
    $sql .= " AND lokasi = ?";
}
$sql .= " ORDER BY $column $sort_order";

$stmt = $conn->prepare($sql);
$search_param = "%" . $search . "%";

if (!empty($selected_lokasi)) {
    $stmt->bind_param("ss", $search_param, $selected_lokasi);
} else {
    $stmt->bind_param("s", $search_param);
}

$stmt->execute();
$result = $stmt->get_result();
?>


<html>

<head>
    <title>
        Aplikasi Pemeliharaan Perangkat Jaringan PLN
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
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

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #4A90E2;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #357ABD;
        }

        .carousel {
            width: 100%;
            margin: 0 auto;
            height: 500px;
            overflow: hidden;
        }

        .carousel img {
            width: 100%;
            height: auto;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
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
            font-size: 12px;
        }

        .slick-dots li.slick-active button:before {
            color: blue;
        }

        th a {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        th a i {
            margin-left: 5px;
            color: rgba(0, 0, 0, 0.5);
        }

        th a:hover i {
            color: #000;
        }

        /* Custom styles for search input */
        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search-input {
            width: 400px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 24px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            outline: none;
            transition: border 0.3s;
        }

        .search-input:focus {
            border-color: #4A90E2;
        }

        .search-button {
            margin-left: 10px;
            padding: 10px 20px;
            background-color: #4A90E2;
            color: white;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: #357ABD;
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
                <div class="carousel mt-4">
                    <div><img src="fa-nataru.png" alt="Gambar 1" class="rounded-lg w-full"></div>
                    <div><img src="kalender.jpg" alt="Gambar 2" class="rounded-lg w-full"></div>
                    <div><img src="banner-hln.jpg" alt="Gambar 3" class="rounded-lg w-full"></div>
                </div>
            </div>
        </section>

        <section class="mb-8" id="daftar-perangkat">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4">Daftar Perangkat</h2>

                <!-- Search Form -->
                <div class="search-container">
                    <form method="GET" action="">
                        <input type="text" name="search" placeholder="Cari Nama Perangkat" class="search-input" />
                        <select name="lokasi" class="search-input ml-2">
                            <option value="">Semua Lokasi</option>
                            <option value="UID Sumut" <?php echo ($selected_lokasi == 'UID Sumut') ? 'selected' : ''; ?>>
                                UID Sumut</option>
                            <option value="UP3 Nias" <?php echo ($selected_lokasi == 'UP3 Nias') ? 'selected' : ''; ?>>UP3
                                Nias</option>
                        </select>
                        <button type="submit" class="search-button">Cari</button>
                    </form>
                </div>

                <table id="daftar-perangkat" class="min-w-full bg-white text-center">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">
                                <a href="?column=no&order=<?php echo $asc_or_desc; ?>">No <i
                                        class="fas fa-sort"></i></a>
                            </th>
                            <th class="py-2 px-4 border-b">
                                <a href="?column=nama_perangkat&order=<?php echo $asc_or_desc; ?>">Nama Perangkat <i
                                        class="fas fa-sort-<?php echo ($column == 'nama_perangkat') ? $up_or_down : ''; ?>"></i></a>
                            </th>
                            <th class="py-2 px-4 border-b">
                                <a href="?column=lokasi&order=<?php echo $asc_or_desc; ?>">Lokasi <i
                                        class="fas fa-sort-<?php echo ($column == 'lokasi') ? $up_or_down : ''; ?>"></i></a>
                            </th>
                            <th class="py-2 px-4 border-b">
                                <a href="?column=status&order=<?php echo $asc_or_desc; ?>">Status <i
                                        class="fas fa-sort-<?php echo ($column == 'status') ? $up_or_down : ''; ?>"></i></a>
                            </th>
                            <th class="py-2 px-4 border-b">
                                <a href="?column=keterangan&order=<?php echo $asc_or_desc; ?>">Keterangan <i
                                        class="fas fa-sort-<?php echo ($column == 'keterangan') ? $up_or_down : ''; ?>"></i></a>
                            </th>
                            <th class="py-2 px-4 border-b">Gambar Sebelum</th>
                            <th class="py-2 px-4 border-b">Gambar Sesudah</th>
                            <th class="py-2 px-4 border-b">
                                <a href="?column=waktu_submit&order=<?php echo $asc_or_desc; ?>">Waktu Submit <i
                                        class="fas fa-sort-<?php echo ($column == 'waktu_submit') ? $up_or_down : ''; ?>"></i></a>
                            </th>
                            <th class="py-2 px-4 border-b">Aksi</th>
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
                                    <td class='py-2 px-4 border-b'><img src='{$row['gambar_sebelum']}' alt='Tidak ada gambar' class='w-20 h-20'></td>
                                    <td class='py-2 px-4 border-b'><img src='{$row['gambar_sesudah']}' alt='Tidak ada gambar' class='w-20 h-20'></td>
                                    <td class='py-2 px-4 border-b'>{$row['waktu_submit']}</td>
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
                            echo "<tr><td colspan='8' class='py-2 px-4 border-b text-center'>Tidak ada data ditemukan</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mb-8" id="form-pemeliharaan">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4">Form Maintenance</h2>
                <?php if (!empty($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <span class="block sm:inline"><?php echo $success_message; ?></span>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-gray-700" for="nama-perangkat">Nama Perangkat</label>
                        <input class="w-full p-2 border border-gray-300 rounded mt-1" id="nama-perangkat"
                            name="nama-perangkat" type="text" required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="lokasi">Lokasi</label>
                        <select class="w-full p-2 border border-gray-300 rounded mt-1" id="lokasi" name="lokasi"
                            required>
                            <option value="UID Sumut">UID Sumut</option>
                            <option value="UP3 Nias">UP3 Nias</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="status">Status</label>
                        <select class="w-full p-2 border border-gray-300 rounded mt-1" id="status" name="status"
                            required>
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="keterangan">Keterangan</label>
                        <textarea class="w-full p-2 border border-gray-300 rounded mt-1" id="keterangan"
                            name="keterangan"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="gambar-sebelum">Gambar Sebelum</label>
                        <input class="w-full p-2 border border-gray-300 rounded mt-1" id="gambar-sebelum"
                            name="gambar-sebelum" type="file" accept="image/*" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="gambar-sesudah">Gambar Sesudah</label>
                        <input class="w-full p-2 border border-gray-300 rounded mt-1" id="gambar-sesudah"
                            name="gambar-sesudah" type="file" accept="image/*" />
                    </div>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded" type="submit">Submit</button>
                </form>
            </div>
        </section>
    </main>
    <footer class="bg-cyan-900 text-white p-4 mt-8">
        <div class="container mx-auto text-center">
            <p>© 2025 PLN STI Sumut. All rights reserved.</p>
        </div>
    </footer>
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