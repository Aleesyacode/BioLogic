<?php

// Kelas BioLogic berisi fungsi-fungsi untuk analisis biologi molekuler
class BioLogic {
    // Tabel codon standar untuk menerjemahkan mRNA ke protein
    // Setiap triplet basa (codon) mRNA diterjemahkan ke satu asam amino
    private $codonTable = [
        'UUU' => 'F', 'UUC' => 'F', 'UUA' => 'L', 'UUG' => 'L',
        'CUU' => 'L', 'CUC' => 'L', 'CUA' => 'L', 'CUG' => 'L',
        'AUU' => 'I', 'AUC' => 'I', 'AUA' => 'I', 'AUG' => 'M',
        'GUU' => 'V', 'GUC' => 'V', 'GUA' => 'V', 'GUG' => 'V',
        'UCU' => 'S', 'UCC' => 'S', 'UCA' => 'S', 'UCG' => 'S',
        'CCU' => 'P', 'CCC' => 'P', 'CCA' => 'P', 'CCG' => 'P',
        'ACU' => 'T', 'ACC' => 'T', 'ACA' => 'T', 'ACG' => 'T',
        'GCU' => 'G', 'GCC' => 'G', 'GCA' => 'G', 'GCG' => 'G',
        'UAU' => 'Y', 'UAC' => 'Y', 'UAA' => '*', 'UAG' => '*',
        'CAU' => 'H', 'CAC' => 'H', 'CAA' => 'Q', 'CAG' => 'Q',
        'AAU' => 'N', 'AAC' => 'N', 'AAA' => 'K', 'AAG' => 'K',
        'GAU' => 'D', 'GAC' => 'D', 'GAA' => 'E', 'GAG' => 'E',
        'UGU' => 'C', 'UGC' => 'C', 'UGA' => '*', 'UGG' => 'W',
        'CGU' => 'R', 'CGC' => 'R', 'CGA' => 'R', 'CGG' => 'R',
        'AGU' => 'S', 'AGC' => 'S', 'AGA' => 'R', 'AGG' => 'R',
        'GGU' => 'G', 'GGC' => 'G', 'GGA' => 'G', 'GGG' => 'G'
    ];

    // Fungsi untuk membersihkan dan memvalidasi urutan DNA
    // Menghapus spasi, angka, mengubah ke huruf besar, dan memastikan hanya A,T,C,G
    public function sanitizeAndValidateDNA($dna) {
        $dna = preg_replace('/\s+/', '', $dna); // hapus spasi
        $dna = preg_replace('/\d+/', '', $dna); // hapus angka
        $dna = strtoupper($dna); // huruf besar
        if (!preg_match('/^[ATCG]+$/', $dna)) {
            return false; // tidak valid
        }
        return $dna;
    }

    // Fungsi untuk mentranskripsikan DNA ke mRNA
    // Mengganti semua basa T (thymine) dengan U (uracil)
    public function transcribe($dna) {
        return str_replace('T', 'U', $dna);
    }

    // Fungsi untuk menerjemahkan mRNA ke protein
    // Membaca mRNA dalam triplet (codon) dan mengubahnya ke asam amino menggunakan tabel codon
    // Berhenti jika menemukan kodon stop (*)
    public function translate($mRNA) {
        $protein = '';
        $length = strlen($mRNA);
        for ($i = 0; $i < $length; $i += 3) {
            $codon = substr($mRNA, $i, 3); // ambil 3 basa sekaligus
            if (strlen($codon) < 3) break; // jika tidak cukup 3 basa, berhenti
            $aa = isset($this->codonTable[$codon]) ? $this->codonTable[$codon] : 'X'; // cari asam amino dari tabel, jika tidak ada gunakan X
            if ($aa == '*') break; // kodon stop, berhenti penerjemahan
            $protein .= $aa; // tambahkan asam amino ke protein
        }
        return $protein;
    }

    // Fungsi untuk menemukan Open Reading Frame (ORF) dalam mRNA
    // ORF adalah urutan basa yang dimulai dengan AUG dan diakhiri dengan kodon stop
    // Mencari di 3 frame reading berbeda
    public function findORFs($mRNA) {
        $orfs = []; // array untuk menyimpan ORF yang ditemukan
        $length = strlen($mRNA); // panjang mRNA
        for ($frame = 0; $frame < 3; $frame++) { // cek 3 frame reading (posisi mulai 0,1,2)
            for ($i = $frame; $i < $length - 2; $i += 3) { // loop setiap 3 basa dalam frame
                $codon = substr($mRNA, $i, 3); // ambil codon
                if ($codon == 'AUG') { // jika menemukan start codon AUG
                    $start = $i; // catat posisi mulai
                    $end = -1; // inisialisasi posisi akhir
                    for ($j = $i + 3; $j < $length - 2; $j += 3) { // cari kodon stop dari sini
                        $stopCodon = substr($mRNA, $j, 3); // ambil codon berikutnya
                        if (in_array($stopCodon, ['UAA', 'UAG', 'UGA'])) { // jika kodon stop
                            $end = $j + 2; // posisi akhir (termasuk basa terakhir kodon stop)
                            break; // berhenti cari
                        }
                    }
                    if ($end != -1) { // jika menemukan stop
                        $orfLength = ($end - $start + 1) / 3; // hitung panjang dalam asam amino
                        $orfs[] = [ // simpan ORF
                            'start' => $start + 1, // posisi mulai (berbasis 1)
                            'end' => $end + 1, // posisi akhir (berbasis 1)
                            'length' => $orfLength // panjang ORF
                        ];
                    }
                }
            }
        }
        return $orfs; // kembalikan daftar ORF
    }

    // Fungsi untuk mensimulasikan mutasi DNA dan melihat dampaknya
    // Mengubah satu basa di posisi tertentu dan melihat perubahan protein
    public function simulateMutation($dna, $pos, $newBase) {
        if ($pos < 1 || $pos > strlen($dna)) return null; // posisi tidak valid
        $mutatedDNA = substr_replace($dna, $newBase, $pos - 1, 1); // ganti basa di posisi tersebut
        $mutatedMRNA = $this->transcribe($mutatedDNA); // transkripsikan DNA yang bermutasi
        $mutatedProtein = $this->translate($mutatedMRNA); // terjemahkan ke protein yang bermutasi
        $originalMRNA = $this->transcribe($dna); // transkripsikan DNA asli
        $originalProtein = $this->translate($originalMRNA); // terjemahkan ke protein asli

        // bandingkan protein asli dan yang bermutasi untuk menentukan tipe mutasi
        $mutationType = 'Tidak Diketahui'; // tipe mutasi awal
        $changedPositions = []; // posisi yang berubah dalam protein
        $minLen = min(strlen($originalProtein), strlen($mutatedProtein)); // panjang minimum protein
        for ($i = 0; $i < $minLen; $i++) { // loop setiap posisi
            if ($originalProtein[$i] != $mutatedProtein[$i]) { // jika asam amino berbeda
                $changedPositions[] = $i + 1; // catat posisi berubah (berbasis 1)
                if ($mutatedProtein[$i] == '*') { // jika menjadi kodon stop
                    $mutationType = 'Nonsense'; // mutasi nonsense
                } elseif ($originalProtein[$i] != '*') { // jika asli bukan stop, tapi berubah
                    $mutationType = 'Missense'; // mutasi missense
                }
            }
        }
        if (empty($changedPositions)) { // jika tidak ada perubahan asam amino
            $mutationType = 'Silent'; // mutasi silent (diam)
        }
        if (strlen($mutatedProtein) < strlen($originalProtein) && $mutatedProtein[strlen($mutatedProtein) - 1] == '*') {
            $mutationType = 'Nonsense'; // stop prematur (mutasi menyebabkan protein lebih pendek karena stop dini)
        }

        return [ // kembalikan hasil simulasi
            'mutatedDNA' => $mutatedDNA, // DNA yang bermutasi
            'mutatedProtein' => $mutatedProtein, // protein yang bermutasi
            'originalProtein' => $originalProtein, // protein asli
            'mutationType' => $mutationType, // tipe mutasi
            'changedPositions' => $changedPositions // posisi yang berubah
        ];
    }
}
?>