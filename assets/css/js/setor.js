function selectWaste(el, kategori) {
  document.querySelectorAll(".waste-card")
    .forEach(card => card.classList.remove("active"));

  el.classList.add("active");
  document.getElementById("kategori").value = kategori;

  const info = document.getElementById("selectedCategory");
  if (info) {
    info.textContent = `Kategori terpilih: ${kategori}`;
    info.style.color = "#074b0a";
    info.style.fontWeight = "500";
  }
}

// =========================================
// 🔹 Preview Foto AMAN dari error
// =========================================
const fotoInput = document.getElementById("foto");

if (fotoInput) {
  fotoInput.addEventListener("change", function (event) {
    const file = event.target.files[0];
    const placeholder = document.querySelector(".upload-placeholder");

    if (!placeholder) return;

    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        placeholder.innerHTML = `
          <div class="preview-area">
            <img src="${e.target.result}" alt="Preview Foto">
            <p id="uploadText">Foto berhasil diunggah ✅</p>
          </div>
        `;
      };
      reader.readAsDataURL(file);
    } else {
      placeholder.innerHTML = `
        <div class="circle">
          <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="#555" viewBox="0 0 24 24">
            <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm8-3h-2.586l-1.707-1.707A1 1 0 0 0 15 4h-6a1 1 0 0 0-.707.293L6.586 6H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2Zm0 12H4V8h3.586l1.707-1.707L10 6h4l0.707.293L16.414 8H20v10Z"/>
          </svg>
        </div>
        <p>Klik untuk upload foto sampah</p>
        <small>Format: JPG, JPEG, PNG (Max. 5MB)</small>
      `;
    }
  });
}
