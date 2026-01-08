// ===============================
// 1. Tombol Filter (Active Switch)
// ===============================
const filterButtons = document.querySelectorAll(".filter-buttons button");

filterButtons.forEach(btn => {
  btn.addEventListener("click", () => {
    filterButtons.forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    applyFilter(btn.textContent.trim());
  });
});

// ===============================
// 2. Filter Tabel Reward
// ===============================
function applyFilter(kategori) {
  const rows = document.querySelectorAll(".table-card tbody tr");

  rows.forEach(row => {
    const tag = row.querySelector("td:nth-child(2) span").textContent.trim();

    if (kategori === "Semua Kategori" || kategori === tag) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}
