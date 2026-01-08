// =======================
// DASHBOARD ADMIN JS
// =======================

// 🔄 Load data awal saat halaman dibuka
document.addEventListener("DOMContentLoaded", function () {
    loadSetoranMenunggu();
    loadStatistikDashboard();
});


// ===============================
// 1. Ambil Data Setoran Menunggu
// ===============================
function loadSetoranMenunggu() {
    fetch("admin_get_setoran.php")
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("tabel-menunggu");
            tbody.innerHTML = "";

            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="6" class="text-center">Tidak ada setoran menunggu.</td></tr>
                `;
                return;
            }

            data.forEach(item => {
                const row = `
                    <tr>
                        <td>${item.nama_user}</td>
                        <td>${item.kategori}</td>
                        <td>${item.berat} Kg</td>
                        <td>${item.tanggal}</td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="verifikasiSetoran(${item.id_setoran})">Verifikasi</button>
                            <button class="btn btn-danger btn-sm" onclick="tolakSetoran(${item.id_setoran})">Tolak</button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML("beforeend", row);
            });
        })
        .catch(error => console.error("Error:", error));
}



// ======================================
// 2. Verifikasi Setoran (AJAX POST)
// ======================================
function verifikasiSetoran(id) {
    if (!confirm("Yakin ingin memverifikasi setoran ini?")) return;

    fetch("admin_verifikasi.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
    })
        .then(response => response.text())
        .then(result => {
            alert(result);
            loadSetoranMenunggu();
            loadStatistikDashboard();
        });
}



// ======================================
// 3. Tolak Setoran (AJAX POST)
// ======================================
function tolakSetoran(id) {
    if (!confirm("Tolak setoran ini?")) return;

    fetch("admin_tolak.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
    })
        .then(response => response.text())
        .then(result => {
            alert(result);
            loadSetoranMenunggu();
            loadStatistikDashboard();
        });
}



// ======================================
// 4. Load Statistik Dashboard (Total poin, total setoran, total user aktif, dll)
// ======================================
function loadStatistikDashboard() {
    fetch("admin_get_statistik.php")
        .then(response => response.json())
        .then(data => {
            document.getElementById("total-setoran").innerText = data.total_setoran;
            document.getElementById("total-user").innerText = data.total_user;
            document.getElementById("total-berat").innerText = data.total_berat + " Kg";
            document.getElementById("total-poin").innerText = data.total_poin;
        })
        .catch(error => console.error("Error Statistik:", error));
}

/// ===============================
// 5. Grafik Setoran Per Bulan
// ===============================
function loadGrafikSetoran() {
    const ctx = document.getElementById("grafikSetoran");

    if (!ctx) return;
    if (typeof dataGrafik === "undefined") return;

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: [
                "Jan","Feb","Mar","Apr","Mei","Jun",
                "Jul","Agu","Sep","Okt","Nov","Des"
            ],
            datasets: [{
                label: "Jumlah Setoran Diverifikasi",
                data: dataGrafik,
                borderWidth: 1
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } }
        }
    });
}

// ===============================
// 6. Grafik Total Berat Sampah
// ===============================
function loadGrafikBeratBulanan() {
    const ctx = document.getElementById("grafikBerat");

    if (!ctx) return;
    if (typeof dataGrafikKg === "undefined") return;

    new Chart(ctx, {
        type: "line",
        data: {
            labels: [
                "Jan","Feb","Mar","Apr","Mei","Jun",
                "Jul","Agu","Sep","Okt","Nov","Des"
            ],
            datasets: [{
                label: "Total Berat Sampah (Kg)",
                data: dataGrafikKg,
                borderWidth: 2,
                tension: 0.3
            }]
        }
    });
}

// 🔥 Manggil grafik setelah SEMUA JS selesai load
window.onload = function () {
    loadGrafikSetoran();
    loadGrafikBeratBulanan();
};

