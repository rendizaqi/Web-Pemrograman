function muatDaftarAnggota() {
    muatDataTabel("../data/anggota.json", ["nim", "nama", "prodi"]);
}

document.addEventListener("DOMContentLoaded", muatDaftarAnggota);