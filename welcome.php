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

// Ambil username dari session
$username = $_SESSION['username'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_perangkat = $_POST['nama-perangkat'];
    $unit = $_POST['unit'];
    $unit2 = $_POST['unit2'];
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

    $sql = "INSERT INTO perangkat (nama_perangkat, unit, unit2, lokasi, status, keterangan, gambar_sebelum, gambar_sesudah, waktu_submit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $nama_perangkat, $unit, $unit2, $lokasi, $status, $keterangan, $gambar_sebelum, $gambar_sesudah);

    if ($stmt->execute()) {
        $success_message = "Data berhasil disimpan!";
    } else {
        $success_message = "Gagal menyimpan data!";
    }
}

// Define the columns that can be sorted
$columns = array('nama_perangkat', 'unit', 'unit2', 'lokasi', 'status', 'keterangan', 'waktu_submit');

// Get the column to sort by (default to the first column)
$column = isset($_GET['column']) && in_array($_GET['column'], $columns) ? $_GET['column'] : $columns[0];

// Get the sort order (default to ascending)
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

// Determine the icon for the current sort order
$up_or_down = ($sort_order == 'ASC') ? 'up' : 'down';
$asc_or_desc = ($sort_order == 'ASC') ? 'desc' : 'asc';

// Handle lokasi filter
$selected_lokasi = isset($_GET['lokasi']) ? $_GET['lokasi'] : ''; // Inisialisasi dengan nilai default

// Get total records
$total_sql = "SELECT COUNT(*) as total FROM perangkat WHERE nama_perangkat LIKE ?";
$total_stmt = $conn->prepare($total_sql);
$search_param = "%" . $search . "%";
$total_stmt->bind_param("s", $search_param);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];

// Define how many results you want per page
$results_per_page = 5;

// Calculate total pages
$total_pages = ceil($total_records / $results_per_page);

// Get current page from URL, if not set default to 1
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Calculate the starting limit for the SQL query
$start_limit = ($current_page - 1) * $results_per_page;

// Create SQL query with search and lokasi filter
$sql = "SELECT * FROM perangkat WHERE nama_perangkat LIKE ?";
if (!empty($selected_lokasi)) {
    $sql .= " AND lokasi = ?";
}
$sql .= " ORDER BY $column $sort_order LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!empty($selected_lokasi)) {
    $stmt->bind_param("ssii", $search_param, $selected_lokasi, $results_per_page, $start_limit);
} else {
    $stmt->bind_param("sii", $search_param, $results_per_page, $start_limit);
}

$stmt->execute();
$result = $stmt->get_result();
?>

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
            width: 300px;
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

        .profile-container {
            display: flex;
            align-items: center;
            margin-left: 10px;
        }

        .profile-icon {
            font-size: 24px;
            margin-right: 5px;
        }

        .profile-name {
            font-size: 16px;
            font-weight: bold;
            margin-right: 10px;
        }

        .responsive-table {
            width: 100%;
            overflow-x: auto;
            display: block;
        }

        .responsive-table table {
            width: 100%;
            min-width: 600px;
            /* Sesuaikan dengan lebar minimal yang Anda inginkan */
        }

        @media (max-width: 768px) {
            .responsive-table {
                overflow-x: auto;
            }

            .responsive-table table {
                min-width: 600px;
                /* Sesuaikan dengan lebar minimal yang Anda inginkan */
            }
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            border: 1px solid #4A90E2;
            color: #4A90E2;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #4A90E2;
            color: white;
        }

        .pagination a:hover {
            background-color: #357ABD;
            color: white;
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
                        <a class="hover:underline" href="welcome.php">
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
                        <div class="profile-container">
                            <span class="profile-name"><?php echo htmlspecialchars($username); ?></span>
                            <i class="fas fa-user-circle profile-icon"></i>
                        </div>
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
                            <option value="UIW Sumatera Utara" <?php echo ($selected_lokasi == 'UIW Sumatera Utara') ? 'selected' : ''; ?>>UIW Sumatera Utara</option>
                            <option value="UP2K Provinsi Sumatera Utara" <?php echo ($selected_lokasi == 'UP2K Provinsi Sumatera Utara') ? 'selected' : ''; ?>>UP2K Provinsi Sumatera Utara</option>
                            <option value="UP2D Sumatera Utara" <?php echo ($selected_lokasi == 'UP2D Sumatera Utara') ? 'selected' : ''; ?>>UP2D Sumatera Utara</option>
                            <option value="COMMAND CENTER" <?php echo ($selected_lokasi == 'COMMAND CENTER') ? 'selected' : ''; ?>>COMMAND CENTER</option>
                            <option value="UP3 Medan" <?php echo ($selected_lokasi == 'UP3 Medan') ? 'selected' : ''; ?>>
                                UP3 Medan</option>
                            <option value="ULP Medan Kota" <?php echo ($selected_lokasi == 'ULP Medan Kota') ? 'selected' : ''; ?>>ULP Medan Kota</option>
                            <option value="ULP Medan Baru" <?php echo ($selected_lokasi == 'ULP Medan Baru') ? 'selected' : ''; ?>>ULP Medan Baru</option>
                            <option value="ULP Medan Selatan" <?php echo ($selected_lokasi == 'ULP Medan Selatan') ? 'selected' : ''; ?>>ULP Medan Selatan</option>
                            <option value="ULP Sunggal" <?php echo ($selected_lokasi == 'ULP Sunggal') ? 'selected' : ''; ?>>ULP Sunggal</option>
                            <option value="ULP Johor" <?php echo ($selected_lokasi == 'ULP Johor') ? 'selected' : ''; ?>>
                                ULP Johor</option>
                            <option value="ULP Deli Tua" <?php echo ($selected_lokasi == 'ULP Deli Tua') ? 'selected' : ''; ?>>ULP Deli Tua</option>
                            <option value="Gudang Logistik UP3 Medan" <?php echo ($selected_lokasi == 'Gudang Logistik UP3 Medan') ? 'selected' : ''; ?>>Gudang Logistik UP3 Medan</option>
                            <option value="UP3 Medan Utara" <?php echo ($selected_lokasi == 'UP3 Medan Utara') ? 'selected' : ''; ?>>UP3 Medan Utara</option>
                            <option value="ULP Belawan" <?php echo ($selected_lokasi == 'ULP Belawan') ? 'selected' : ''; ?>>ULP Belawan</option>
                            <option value="ULP Helvetia" <?php echo ($selected_lokasi == 'ULP Helvetia') ? 'selected' : ''; ?>>ULP Helvetia</option>
                            <option value="ULP Labuhan" <?php echo ($selected_lokasi == 'ULP Labuhan') ? 'selected' : ''; ?>>ULP Labuhan</option>
                            <option value="ULP Medan Timur" <?php echo ($selected_lokasi == 'ULP Medan Timur') ? 'selected' : ''; ?>>ULP Medan Timur</option>
                            <option value="ULP Medan Denai" <?php echo ($selected_lokasi == 'ULP Medan Denai') ? 'selected' : ''; ?>>ULP Medan Denai</option>
                            <option value="UP3 Bukit Barisan" <?php echo ($selected_lokasi == 'UP3 Bukit Barisan') ? 'selected' : ''; ?>>UP3 Bukit Barisan</option>
                            <option value="ULP Sidikalang" <?php echo ($selected_lokasi == 'ULP Sidikalang') ? 'selected' : ''; ?>>ULP Sidikalang</option>
                            <option value="ULP Tiga Binanga" <?php echo ($selected_lokasi == 'ULP Tiga Binanga') ? 'selected' : ''; ?>>ULP Tiga Binanga</option>
                            <option value="ULP Brastagi" <?php echo ($selected_lokasi == 'ULP Brastagi') ? 'selected' : ''; ?>>ULP Brastagi</option>
                            <option value="ULP Kabanjahe" <?php echo ($selected_lokasi == 'ULP Kabanjahe') ? 'selected' : ''; ?>>ULP Kabanjahe</option>
                            <option value="ULP Pancur Batu" <?php echo ($selected_lokasi == 'ULP Pancur Batu') ? 'selected' : ''; ?>>ULP Pancur Batu</option>
                            <option value="ULP Pangururan" <?php echo ($selected_lokasi == 'ULP Pangururan') ? 'selected' : ''; ?>>ULP Pangururan</option>
                            <option value="ULP Kuala" <?php echo ($selected_lokasi == 'ULP Kuala') ? 'selected' : ''; ?>>
                                ULP Kuala</option>
                            <option value="KANTOR JAGA PHAK PHAK BARAT" <?php echo ($selected_lokasi == 'KANTOR JAGA PHAK PHAK BARAT') ? 'selected' : ''; ?>>KANTOR JAGA PHAK PHAK BARAT</option>
                            <option value="UP3 Binjai" <?php echo ($selected_lokasi == 'UP3 Binjai') ? 'selected' : ''; ?>>UP3 Binjai</option>
                            <option value="ULP Binjai Kota" <?php echo ($selected_lokasi == 'ULP Binjai Kota') ? 'selected' : ''; ?>>ULP Binjai Kota</option>
                            <option value="ULP Binjai Timur" <?php echo ($selected_lokasi == 'ULP Binjai Timur') ? 'selected' : ''; ?>>ULP Binjai Timur</option>
                            <option value="ULP Binjai Barat" <?php echo ($selected_lokasi == 'ULP Binjai Barat') ? 'selected' : ''; ?>>ULP Binjai Barat</option>
                            <option value="ULP Gebang" <?php echo ($selected_lokasi == 'ULP Gebang') ? 'selected' : ''; ?>>ULP Gebang</option>
                            <option value="ULP P.Brandan" <?php echo ($selected_lokasi == 'ULP P.Brandan') ? 'selected' : ''; ?>>ULP P.Brandan</option>
                            <option value="ULP P.Susu" <?php echo ($selected_lokasi == 'ULP P.Susu') ? 'selected' : ''; ?>>ULP P.Susu</option>
                            <option value="ULP Stabat" <?php echo ($selected_lokasi == 'ULP Stabat') ? 'selected' : ''; ?>>ULP Stabat</option>
                            <option value="ULP Tanjung Pura" <?php echo ($selected_lokasi == 'ULP Tanjung Pura') ? 'selected' : ''; ?>>ULP Tanjung Pura</option>
                            <option value="Gudang Logistik UP3 Binjai" <?php echo ($selected_lokasi == 'Gudang Logistik UP3 Binjai') ? 'selected' : ''; ?>>Gudang Logistik UP3 Binjai</option>
                            <option value="UP3 Nias" <?php echo ($selected_lokasi == 'UP3 Nias') ? 'selected' : ''; ?>>UP3
                                Nias</option>
                            <option value="ULP Gunung Sitoli" <?php echo ($selected_lokasi == 'ULP Gunung Sitoli') ? 'selected' : ''; ?>>ULP Gunung Sitoli</option>
                            <option value="ULP Teluk Dalam" <?php echo ($selected_lokasi == 'ULP Teluk Dalam') ? 'selected' : ''; ?>>ULP Teluk Dalam</option>
                            <option value="ULP Nias Barat" <?php echo ($selected_lokasi == 'ULP Nias Barat') ? 'selected' : ''; ?>>ULP Nias Barat</option>
                            <option value="Gudang Logistik UP3 Nias" <?php echo ($selected_lokasi == 'Gudang Logistik UP3 Nias') ? 'selected' : ''; ?>>Gudang Logistik UP3 Nias</option>
                        </select>
                        <button type="submit" class="search-button">Cari</button>
                    </form>
                </div>
                <div class="responsive-table">
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
                                    <a href="?column=unit&order=<?php echo $asc_or_desc; ?>">Unit Level I<i
                                            class="fas fa-sort-<?php echo ($column == 'unit') ? $up_or_down : ''; ?>"></i></a>
                                </th>
                                <th class="py-2 px-4 border-b">
                                    <a href="?column=unit2&order=<?php echo $asc_or_desc; ?>">Unit Level II <i
                                            class="fas fa-sort-<?php echo ($column == 'unit2') ? $up_or_down : ''; ?>"></i></a>
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
                                    <td class='py-2 px-4 border-b'>{$row['unit']}</td>
                                    <td class='py-2 px-4 border-b'>{$row['unit2']}</td>
                                    <td class='py-2 px-4 border-b'>{$row['lokasi']}</td>
                                    <td class='py-2 px-4 border-b'>{$row['status']}</td>
                                    <td class='py-2 px-4 border-b'>{$row['keterangan']}</td>
                                    <td class='py-2 px-4 border-b'><img src='{$row['gambar_sebelum']}' alt='Tidak ada gambar' class='w-20 h-20'></td>
                                    <td class='py-2 px-4 border-b'><img src='{$row['gambar_sesudah']}' alt='Tidak ada gambar' class='w-20 h-20'></td>
                                    <td class='py-2 px-4 border-b'>{$row['waktu_submit']}</td>
                                    <td class='py-2 px-4 border-b'>
                                        
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
                <!-- Pagination Links -->
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a
                            href="?search=<?php echo urlencode($search); ?>&lokasi=<?php echo urlencode($selected_lokasi); ?>&column=<?php echo $column; ?>&order=<?php echo $sort_order; ?>&page=<?php echo $current_page - 1; ?>">«
                            Sebelumnya</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?search=<?php echo urlencode($search); ?>&lokasi=<?php echo urlencode($selected_lokasi); ?>&column=<?php echo $column; ?>&order=<?php echo $sort_order; ?>&page=<?php echo $i; ?>"
                            <?php if ($i == $current_page)
                                echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a
                            href="?search=<?php echo urlencode($search); ?>&lokasi=<?php echo urlencode($selected_lokasi); ?>&column=<?php echo $column; ?>&order=<?php echo $sort_order; ?>&page=<?php echo $current_page + 1; ?>">Selanjutnya
                            »</a>
                    <?php endif; ?>
                </div>
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
                        <label class="block text-gray-700" for="unit">Unit Level I</label>
                        <select class="w-full p-2 border border-gray-300 rounded mt-1" id="unit" name="unit" required>
                            <option value="UID Sumatera Utara">UID Sumatera Utara</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="unit2">Unit Level II</label>
                        <select class="w-full p-2 border border-gray-300 rounded mt-1" id="unit2" name="unit2" required>
                            <option value="UP3 Medan">UP3 Medan</option>
                            <option value="UP3 Medan Utara">UP3 Medan Utara</option>
                            <option value="UP3 Bukit Barisan">UP3 Bukit Barisan</option>
                            <option value="UP3 Binjai">UP3 Binjai</option>
                            <option value="UP3 Lubuk Pakam">UP3 Lubuk Pakam</option>
                            <option value="UP3 Pematang Siantar">UP3 Pematang Siantar</option>
                            <option value="UP3 Rantau Prapat">UP3 Rantau Prapat</option>
                            <option value="UP3 Sibolga">UP3 Sibolga</option>
                            <option value="UP3 Padang Sidempuan">UP3 Padang Sidempuan</option>
                            <option value="UP3 Nias">UP3 Nias</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700" for="lokasi">Lokasi</label>
                        <select class="w-full p-2 border border-gray-300 rounded mt-1" id="lokasi" name="lokasi"
                            required>
                            <option value="UIW Sumatera Utara">UIW Sumatera Utara</option>
                            <option value="UP2K Provinsi Sumatera Utara">UP2K Provinsi Sumatera Utara</option>
                            <option value="UP2D Sumatera Utara">UP2D Sumatera Utara</option>
                            <option value="COMMAND CENTER">COMMAND CENTER</option>
                            <option value="UP3 Medan">UP3 Medan</option>
                            <option value="ULP Medan Kota">ULP Medan Kota</option>
                            <option value="ULP Medan Baru">ULP Medan Baru</option>
                            <option value="ULP Medan Selatan">ULP Medan Selatan</option>
                            <option value="ULP Sunggal">ULP Sunggal</option>
                            <option value="ULP Johor">ULP Johor</option>
                            <option value="ULP Deli Tua">ULP Deli Tua</option>
                            <option value="Gudang Logistik UP3 Medan">Gudang Logistik UP3 Medan</option>
                            <option value="UP3 Medan Utara">UP3 Medan Utara</option>
                            <option value="ULP Belawan">ULP Belawan</option>
                            <option value="ULP Helvetia">ULP Helvetia</option>
                            <option value="ULP Labuhan">ULP Labuhan</option>
                            <option value="ULP Medan Timur">ULP Medan Timur</option>
                            <option value="ULP Medan Denai">ULP Medan Denai</option>
                            <option value="UP3 Bukit Barisan">UP3 Bukit Barisan</option>
                            <option value="ULP Sidikalang">ULP Sidikalang</option>
                            <option value="ULP Tiga Binanga">ULP Tiga Binanga</option>
                            <option value="ULP Brastagi">ULP Brastagi</option>
                            <option value="ULP Kabanjahe">ULP Kabanjahe</option>
                            <option value="ULP Pancur Batu">ULP Pancur Batu</option>
                            <option value="ULP Pangururan">ULP Pangururan</option>
                            <option value="ULP Kuala">ULP Kuala</option>
                            <option value="KANTOR JAGA PHAK PHAK BARAT">KANTOR JAGA PHAK PHAK BARAT</option>
                            <option value="UP3 Binjai">UP3 Binjai</option>
                            <option value="ULP Binjai Kota">ULP Binjai Kota</option>
                            <option value="ULP Binjai Timur">ULP Binjai Timur</option>
                            <option value="ULP Binjai Barat">ULP Binjai Barat</option>
                            <option value="ULP Gebang">ULP Gebang</option>
                            <option value="ULP P.Brandan">ULP P.Brandan</option>
                            <option value="ULP P.Susu">ULP P.Susu</option>
                            <option value="ULP Stabat">ULP Stabat</option>
                            <option value="ULP Tanjung Pura">ULP Tanjung Pura</option>
                            <option value="Gudang Logistik UP3 Binjai">Gudang Logistik UP3 Binjai</option>
                            <option value="UP3 Lubuk Pakam">UP3 Lubuk Pakam</option>
                            <option value="ULP Dolok Masihul">ULP Dolok Masihul</option>
                            <option value="GH ULP Dolok Masihul (kantor Lama)">GH ULP Dolok Masihul (kantor Lama)
                            </option>
                            <option value="ULP Lubuk Pakam Kota">ULP Lubuk Pakam Kota</option>
                            <option value="ULP Perbaungan">ULP Perbaungan</option>
                            <option value="ULP Tanjung Morawa">ULP Tanjung Morawa</option>
                            <option value="ULP Sei Rampah">ULP Sei Rampah</option>
                            <option value="ULP Galang">ULP Galang</option>
                            <option value="Gudang Logistik UP3 Lubuk Pakam">Gudang Logistik UP3 Lubuk Pakam</option>
                            <option value="UP3 Pematang Siantar">UP3 Pematang Siantar</option>
                            <option value="ULP Siantar Kota">ULP Siantar Kota</option>
                            <option value="ULP Tebing Tinggi">ULP Tebing Tinggi</option>
                            <option value="ULP Kisaran">ULP Kisaran</option>
                            <option value="ULP Perdagangan">ULP Perdagangan</option>
                            <option value="ULP Parapat">ULP Parapat</option>
                            <option value="ULP Tanjung Tiram">ULP Tanjung Tiram</option>
                            <option value="ULP Pematang Raya">ULP Pematang Raya</option>
                            <option value="ULP Indra Pura">ULP Indra Pura</option>
                            <option value="ULP Tanah Jawa">ULP Tanah Jawa</option>
                            <option value="ULP Limapuluh">ULP Limapuluh</option>
                            <option value="Gudang Logistik UP3 Pematang Siantar">Gudang Logistik UP3 Pematang Siantar
                            </option>
                            <option value="WISMA RETTA DANAU TOBA">WISMA RETTA DANAU TOBA</option>
                            <option value="UP3 Rantau Prapat">UP3 Rantau Prapat</option>
                            <option value="ULP Aek Kanopan">ULP Aek Kanopan</option>
                            <option value="ULP Aek Kota Batu">ULP Aek Kota Batu</option>
                            <option value="ULP Aek Nabara">ULP Aek Nabara</option>
                            <option value="ULP Kota Pinang">ULP Kota Pinang</option>
                            <option value="ULP Labuhan Bilik">ULP Labuhan Bilik</option>
                            <option value="ULP Rantau Kota">ULP Rantau Kota</option>
                            <option value="ULP Simpang Kawat">ULP Simpang Kawat</option>
                            <option value="ULP Tanjung Balai">ULP Tanjung Balai</option>
                            <option value="Gudang Logistik UP3 Rantau Prapat">Gudang Logistik UP3 Rantau Prapat</option>
                            <option value="UP3 Sibolga">UP3 Sibolga</option>
                            <option value="ULP Sibolga Kota">ULP Sibolga Kota</option>
                            <option value="ULP Porsea">ULP Porsea</option>
                            <option value="ULP Balige">ULP Balige</option>
                            <option value="ULP Siborong-borong">ULP Siborong-borong</option>
                            <option value="ULP Dolok Sanggul">ULP Dolok Sanggul</option>
                            <option value="ULP Tarutung">ULP Tarutung</option>
                            <option value="ULP Barus">ULP Barus</option>
                            <option value="Gudang Logistik UP3 Sibolga">Gudang Logistik UP3 Sibolga</option>
                            <option value="GH BUKIT ULP Barus">GH BUKIT ULP Barus</option>
                            <option value="UP3 Padangsidimpuan">UP3 Padangsidimpuan</option>
                            <option value="ULP Gunung Tua">ULP Gunung Tua</option>
                            <option value="ULP Kota Nopan">ULP Kota Nopan</option>
                            <option value="ULP Natal">ULP Natal</option>
                            <option value="ULP Penyambungan">ULP Penyambungan</option>
                            <option value="ULP Sibuhuan">ULP Sibuhuan</option>
                            <option value="ULP Sidimpuan Kota">ULP Sidimpuan Kota</option>
                            <option value="ULP Sipirok">ULP Sipirok</option>
                            <option value="Gudang Logistik UP3 Padang Sidimpuan">Gudang Logistik UP3 Padang Sidimpuan
                            </option>
                            <option value="UP3 Nias">UP3 Nias</option>
                            <option value="ULP Gunung Sitoli">ULP Gunung Sitoli</option>
                            <option value="ULP Teluk Dalam">ULP Teluk Dalam</option>
                            <option value="ULP Nias Barat">ULP Nias Barat</option>
                            <option value="Gudang Logistik UP3 Nias">Gudang Logistik UP3 Nias</option>
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