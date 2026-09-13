// ===== Hamburger menu (JS-driven, menggantikan checkbox hack) =====
function initNavToggle() {
    const toggleBtn = document.getElementById("nav-toggle-btn");
    const nav = document.querySelector("header nav");
    if (!toggleBtn || !nav) return;

    toggleBtn.addEventListener("click", function () {
        nav.classList.toggle("nav-open");
    });
}

// ===== Indikator / Counter Jumlah Baris Tabel (Latihan 4) =====
function updateTableCounter() {
    const counter = document.getElementById("counter-info");
    const rows = document.querySelectorAll(".table-responsive table tbody tr");
    if (!counter || !rows.length) return;

    let countTampil = 0;
    rows.forEach(function (row) {
        if (row.style.display !== "none") {
            countTampil++;
        }
    });

    counter.textContent = `Menampilkan ${countTampil} dari ${rows.length} data.`;
}

// ===== Konfirmasi hapus (front-end) =====
function initHapusConfirm() {
    document.querySelectorAll(".btn-hapus").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const row = btn.closest("tr");
            const nama = row ? row.querySelector("td")?.textContent : "data ini";
            const yakin = confirm("Yakin ingin menghapus \"" + nama + "\"?");
            if (yakin && row) {
                row.remove();
                updateTableCounter(); // Update counter setelah hapus baris
            }
        });
    });
}

// ===== Filter/pencarian tabel real-time spesifik kolom (Latihan 3) =====
function initTableFilter() {
    const input = document.getElementById("search-input");
    const table = document.querySelector(".table-responsive table");
    if (!input || !table) return;

    input.addEventListener("keyup", function () {
        const keyword = input.value.toLowerCase();
        const rows = table.querySelectorAll("tbody tr");
        rows.forEach(function (row) {
            // Hanya mengecek kolom ke-1 (Judul/Nama)
            const targetCell = row.cells[0]?.textContent.toLowerCase() || "";
            row.style.display = targetCell.includes(keyword) ? "" : "none";
        });
        updateTableCounter(); // Update counter saat penyaringan
    });
}

// ===== Helper Error Validasi =====
function tampilkanError(input, pesan) {
    hapusError(input);
    const span = document.createElement("span");
    span.className = "error";
    span.textContent = pesan;
    input.insertAdjacentElement("afterend", span);
}

function hapusError(input) {
    const next = input.nextElementSibling;
    if (next && next.classList.contains("error")) {
        next.remove();
    }
}

// ===== Validasi form dengan array loop & Regex ISBN (Latihan 1 & 5) =====
function initValidasiForm() {
    const form = document.getElementById("form-tambah");
    if (!form) return;

    form.addEventListener("submit", function (e) {
        let valid = true;

        const rules = [
            {
                field: form.querySelector("[name='judul'], [name='nama']"),
                valid: input => input.value.trim() !== "",
                pesan: "Field ini wajib diisi."
            },
            {
                field: form.querySelector("[name='pengarang']"),
                valid: input => input.value.trim() !== "",
                pesan: "Pengarang wajib diisi."
            },
            {
                field: form.querySelector("[name='isbn']"),
                valid: input => {
                    if (input.value.trim() === "") return true;
                    // Hanya angka dan tanda hubung (-)
                    return /^[0-9-]+$/.test(input.value.trim());
                },
                pesan: "ISBN hanya boleh berisi angka dan tanda hubung (-)."
            },
            {
                field: form.querySelector("[name='tahun']"),
                valid: input => {
                    const nilai = parseInt(input.value, 10);
                    return !isNaN(nilai) && nilai >= 1900 && nilai <= 2026;
                },
                pesan: "Tahun harus di antara 1900-2026."
            },
            {
                field: form.querySelector("[name='stok']"),
                valid: input => {
                    const nilai = parseInt(input.value, 10);
                    return !isNaN(nilai) && nilai >= 0;
                },
                pesan: "Stok tidak boleh negatif."
            }
        ];

        rules.forEach(rule => {
            if (rule.field) {
                if (!rule.valid(rule.field)) {
                    tampilkanError(rule.field, rule.pesan);
                    valid = false;
                } else {
                    hapusError(rule.field);
                }
            }
        });

        if (!valid) {
            e.preventDefault();
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initNavToggle();
    initHapusConfirm();
    initTableFilter();
    initValidasiForm();
    updateTableCounter();
});