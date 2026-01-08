// ==============================
// 🌍 Inisialisasi Peta
// ==============================
const map = L.map("map").setView([-8.65, 115.22], 12); // Default view

// Tambahkan tile layer OpenStreetMap
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution:
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
}).addTo(map);

// ==============================
// 📍 Tambahkan Marker Bank Sampah dengan link Google Maps
// ==============================
const markers = [];

bankSampahData.forEach((bank) => {
  const googleMapsLink = `https://www.google.com/maps/dir/?api=1&destination=${bank.latitude},${bank.longitude}`;
  const marker = L.marker([bank.latitude, bank.longitude]).addTo(map)
    .bindPopup(`
      <b>${bank.nama_bank}</b><br>
      ${bank.alamat}<br>
      Kota/Kabupaten: ${bank.kota_kabupaten}<br>
      Kontak: ${bank.kontak}<br>
      <a href="${googleMapsLink}" target="_blank" style="color:#1553a3; font-weight:bold;">Petunjuk Arah</a>
    `);
  markers.push({ marker, bank });
});

// ==============================
// 🔍 Fungsi Pencarian Kota/Kabupaten atau Alamat
// ==============================
function searchBankSampah() {
  const keyword = document.getElementById("search").value.toLowerCase().trim();
  if (!keyword) return alert("Silakan ketik nama kota/kabupaten atau alamat");

  // Filter marker berdasarkan alamat atau kota/kabupaten
  const foundMarkers = markers.filter(
    (m) =>
      m.bank.alamat.toLowerCase().includes(keyword) ||
      m.bank.kota_kabupaten.toLowerCase().includes(keyword)
  );

  if (foundMarkers.length > 0) {
    // Zoom agar semua marker yang cocok terlihat
    const group = new L.featureGroup(foundMarkers.map((m) => m.marker));
    map.fitBounds(group.getBounds().pad(0.2));

    // Buka popup semua marker yang cocok
    foundMarkers.forEach((m) => m.marker.openPopup());
  } else {
    alert("Bank sampah tidak ditemukan di lokasi tersebut.");
  }
}

// ==============================
// 🎯 Event tombol dan Enter
// ==============================
document
  .getElementById("btn-search")
  .addEventListener("click", searchBankSampah);

document.getElementById("search").addEventListener("keydown", function (e) {
  if (e.key === "Enter") {
    e.preventDefault();
    searchBankSampah();
  }
});

// ==============================
// 🗺️ Zoom ke semua marker awal
// ==============================
const group = new L.featureGroup(markers.map((m) => m.marker));
map.fitBounds(group.getBounds().pad(0.2));
