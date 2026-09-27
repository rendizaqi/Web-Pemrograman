function muatDaftarBuku() {
    muatDataTabel("../data/buku.json", ["judul", "pengarang", "tahun", "stok", "kategori"]);
}

document.addEventListener("DOMContentLoaded", muatDaftarBuku);
document.getElementById("btn-reload")?.addEventListener("click", muatDaftarBuku);