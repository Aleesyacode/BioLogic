<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Sintesis Protein Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); }
        .card { transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="font-sans min-h-screen">
    <header class="bg-gradient-to-r from-blue-700 to-teal-600 text-white py-12">
        <div class="container mx-auto text-center">
            <h1 class="text-4xl font-bold mb-4">🧬 Aplikasi Sintesis Protein Digital</h1>
            <p class="text-lg opacity-90">Analisis urutan DNA, simulasi transkripsi, translasi, ORF, dan mutasi</p>
        </div>
    </header>
    <div class="container mx-auto p-6">

        <div class="bg-white p-8 rounded-xl shadow-lg mb-10 card">
            <h2 class="text-2xl font-semibold text-blue-800 mb-6 text-center">🔬 Input Urutan DNA</h2>
            <form method="POST">
                <div class="mb-6">
                    <label for="dna" class="block text-lg font-medium text-gray-700 mb-2">Masukkan Urutan DNA:</label>
                    <textarea id="dna" name="dna" rows="5" class="mt-1 block w-full border-2 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-4 text-lg" placeholder="contoh: ATGGCCATCGTTAA"></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 text-lg font-semibold shadow-md transition-all">Analisis Urutan</button>
                </div>
            </form>
        </div>

        <?php
        // Sertakan file kelas BioLogic untuk fungsi biologi
        require_once 'BioLogic.php';
        // Buat objek BioLogic
        $bio = new BioLogic();

        // Cek jika form dikirim (metode POST)
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Ambil urutan DNA dari form, jika tidak ada gunakan string kosong
            $dna = $_POST['dna'] ?? '';
            // Bersihkan dan validasi DNA menggunakan fungsi di BioLogic
            $validatedDNA = $bio->sanitizeAndValidateDNA($dna);

            // Jika DNA tidak valid, tampilkan pesan error
            if ($validatedDNA === false) {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Urutan DNA tidak valid. Hanya A, T, C, G yang diperbolehkan.</div>';
            } else {
                // Lakukan analisis: transkripsi, translasi, cari ORF
                $mRNA = $bio->transcribe($validatedDNA); // DNA -> mRNA
                $protein = $bio->translate($mRNA); // mRNA -> protein
                $orfs = $bio->findORFs($mRNA); // cari ORF di mRNA

                // Tampilkan hasil dalam grid
                echo '<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">';
                // Bagian hasil translasi
                echo '<div class="bg-white p-6 rounded-xl shadow-lg card">';
                echo '<h2 class="text-xl font-semibold text-blue-800 mb-4 flex items-center"><span class="mr-2">🧬</span>Hasil Translasi</h2>';
                echo '<div class="space-y-3">';
                echo '<p><strong>DNA:</strong> <span class="font-mono bg-blue-50 p-2 rounded text-sm break-all">' . $validatedDNA . '</span></p>';
                echo '<p><strong>mRNA:</strong> <span class="font-mono bg-green-50 p-2 rounded text-sm break-all">' . $mRNA . '</span></p>';
                echo '<p><strong>Protein:</strong> <span class="font-mono bg-purple-50 p-2 rounded text-sm break-all">' . $protein . '</span></p>';
                echo '</div></div>';

                // Bagian analisis ORF
                echo '<div class="bg-white p-6 rounded-xl shadow-lg card">';
                echo '<h2 class="text-xl font-semibold text-blue-800 mb-4 flex items-center"><span class="mr-2">🔍</span>Analisis ORF</h2>';
                if (empty($orfs)) { // jika tidak ada ORF
                    echo '<p class="text-gray-500">Tidak ada ORF ditemukan.</p>';
                } else { // jika ada ORF, tampilkan daftar
                    echo '<ul class="space-y-2">';
                    foreach ($orfs as $orf) { // loop setiap ORF
                        echo '<li class="bg-teal-50 p-3 rounded-lg">Awal: ' . $orf['start'] . ', Akhir: ' . $orf['end'] . ', Panjang: ' . $orf['length'] . ' AA</li>';
                    }
                    echo '</ul>';
                }
                echo '</div>';
                echo '</div>';

                // Bagian simulasi mutasi
                echo '<div class="bg-white p-6 rounded-xl shadow-lg mt-8 card col-span-full">';
                echo '<h2 class="text-xl font-semibold text-blue-800 mb-4 flex items-center"><span class="mr-2">🧪</span>Simulasi Mutasi</h2>';
                // Form untuk input posisi dan basa baru
                echo '<form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">';
                echo '<input type="hidden" name="dna" value="' . $validatedDNA . '">'; // kirim DNA asli juga
                echo '<div>';
                echo '<label for="pos" class="block text-sm font-medium text-gray-700 mb-1">Posisi (berbasis 1):</label>';
                echo '<input type="number" id="pos" name="pos" min="1" max="' . strlen($validatedDNA) . '" class="w-full border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3" required>';
                echo '</div>';
                echo '<div>';
                echo '<label for="newbase" class="block text-sm font-medium text-gray-700 mb-1">Basis Baru (A/T/C/G):</label>';
                echo '<input type="text" id="newbase" name="newbase" maxlength="1" class="w-full border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 uppercase" required>';
                echo '</div>';
                echo '<div class="flex items-end">';
                echo '<button type="submit" class="w-full bg-gradient-to-r from-teal-600 to-teal-700 text-white px-6 py-3 rounded-lg hover:from-teal-700 hover:to-teal-800 font-semibold shadow-md transition-all">Simulasi Mutasi</button>';
                echo '</div>';
                echo '</form>';
                echo '</div>';

                // Jika form mutasi dikirim
                if (isset($_POST['pos']) && isset($_POST['newbase'])) {
                    $pos = (int)$_POST['pos']; // posisi sebagai integer
                    $newBase = strtoupper($_POST['newbase']); // basa baru huruf besar
                    if (in_array($newBase, ['A','T','C','G'])) { // validasi basa
                        $mutation = $bio->simulateMutation($validatedDNA, $pos, $newBase); // simulasi mutasi
                        if ($mutation) { // jika simulasi berhasil
                            echo '<div class="bg-white p-6 rounded-xl shadow-lg mt-6 card">';
                            echo '<h3 class="text-lg font-semibold text-teal-800 mb-4 flex items-center"><span class="mr-2">🔬</span>Hasil Mutasi</h3>';
                            echo '<p class="mb-4"><strong>Tipe:</strong> <span class="inline-block bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-sm font-medium">' . $mutation['mutationType'] . '</span></p>';
                            echo '<p class="mb-3"><strong>Protein Asli:</strong> <span class="font-mono bg-gray-100 p-2 rounded text-sm break-all">';
                            // Tampilkan protein asli, highlight posisi berubah
                            for ($i = 0; $i < strlen($mutation['originalProtein']); $i++) {
                                if (in_array($i + 1, $mutation['changedPositions'])) { // jika posisi berubah
                                    echo '<span class="bg-yellow-300 px-1 rounded">' . $mutation['originalProtein'][$i] . '</span>'; // highlight kuning
                                } else {
                                    echo $mutation['originalProtein'][$i]; // normal
                                }
                            }
                            echo '</span></p>';
                            echo '<p><strong>Protein yang Bermutasi:</strong> <span class="font-mono bg-gray-100 p-2 rounded text-sm break-all">';
                            // Tampilkan protein bermutasi, highlight posisi berubah
                            for ($i = 0; $i < strlen($mutation['mutatedProtein']); $i++) {
                                if (in_array($i + 1, $mutation['changedPositions'])) { // jika posisi berubah
                                    echo '<span class="bg-red-300 px-1 rounded">' . $mutation['mutatedProtein'][$i] . '</span>'; // highlight merah
                                } else {
                                    echo $mutation['mutatedProtein'][$i]; // normal
                                }
                            }
                            echo '</span></p>';
                            echo '</div>';
                        } else { // posisi tidak valid
                            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mt-4">Posisi tidak valid.</div>';
                        }
                    } else { // basa tidak valid
                        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4">Basis baru tidak valid.</div>';
                    }
                }
            }
        }
        ?>
    </div>
    <footer class="bg-gray-800 text-white text-center py-6 mt-12">
        <p>&copy; 2023 Aplikasi Sintesis Protein Digital. Dibuat dengan PHP dan Tailwind CSS.</p>
    </footer>
</body>
</html>