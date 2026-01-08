// === FILTER KATEGORI ===
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".tab");
  const cards = document.querySelectorAll(".gift-card");

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabs.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");

      let kategori = tab.textContent.trim(); // Semua, E-Money, Voucher, Green Impact

      cards.forEach((card) => {
        let tag = card.querySelector(".tag").textContent.trim();

        if (kategori === "Semua" || tag === kategori) {
          card.style.display = "block";
        } else {
          card.style.display = "none";
        }
      });
    });
  });
});

// ===== POPUP =====
const popup = document.getElementById("popup-tukar");
const overlay = document.getElementById("popup-overlay");
const closeBtn = document.getElementById("popup-close");
const batalBtn = document.getElementById("btn-batal");

// Ambil poin user dari tombol header (landing style)
const userPoin = parseInt(
  document.querySelector(".user-point").textContent.replace(/[^0-9]/g, "")
);

function openPopup(data) {
  document.getElementById("popup-img").src = data.img;
  document.getElementById("popup-nama").innerText = data.nama;
  document.getElementById("popup-desc").innerText = data.desc;

  // Tampilkan harga reward
  document.getElementById("popup-harga").innerText = data.poin;
  document.getElementById("popup-harga2").innerText = data.poin;

  const poinSisa = userPoin - data.poin;
  document.getElementById("popup-user").innerText = userPoin;
  document.getElementById("popup-sisa").innerText = poinSisa >= 0 ? poinSisa : 0;

  // NOTIF POIN KURANG
  const notif = document.getElementById("popup-notif");
  if (userPoin < data.poin) {
    notif.style.display = "block";
  } else {
    notif.style.display = "none";
  }

  // Input nomor hanya untuk kategori E-Money
  const inputGroup = document.getElementById("popup-input-group");
  const label = document.getElementById("popup-label");
  const input = document.getElementById("popup-input");

  if (data.kategori.toLowerCase().replace(/\s+/g, "") === "e-money") {
    inputGroup.classList.remove("hidden");
    label.innerText = "Nomor E-Money";
    input.placeholder = "Masukkan nomor akun";
  } else {
    inputGroup.classList.add("hidden");
  }

  popup.classList.remove("hidden");
  overlay.classList.remove("hidden");
}

// ===== CLOSE POPUP =====
function closePopup() {
  popup.classList.add("hidden");
  overlay.classList.add("hidden");
}

closeBtn.onclick = closePopup;
batalBtn.onclick = closePopup;
overlay.onclick = closePopup;

// === BUTTON TUKAR ===
document.querySelectorAll(".btn-tukar").forEach((btn) => {
  btn.addEventListener("click", function () {
    const data = {
      id: this.dataset.id,
      nama: this.dataset.nama,
      poin: parseInt(this.dataset.poin),
      img: this.dataset.img,
      kategori: this.dataset.kategori,
      desc: this.dataset.desc,
    };

    window.currentReward = data;
    openPopup(data);
  });
});

// === KONFIRMASI TUKAR ===
document.getElementById("btn-konfirmasi").addEventListener("click", function () {
  let id_reward = currentReward.id;
  let poin = currentReward.poin;
  let nomor = document.getElementById("popup-input").value.trim();

  // Cek poin dulu
  if (userPoin < poin) {
    showNotif("Poin Anda tidak cukup.", "error");
    return;
  }

  let form = new FormData();
  form.append("id_reward", id_reward);
  form.append("poin", poin);
  form.append("nomor", nomor);

  fetch("tukar_poin_proses.php", {
    method: "POST",
    body: form
  })
  .then(res => res.text())
  .then(res => {
      if (res === "success") {
          showNotif("Permintaan reward telah dikirim ke admin!", "success");
          closePopup();
      }
      else if (res === "not_enough") {
          showNotif("Poin Anda tidak cukup.", "error");
      }
      else if (res === "invalid") {
          showNotif("Data tidak valid.", "error");
      }
      else {
          showNotif("Terjadi kesalahan, coba lagi.", "error");
      }
  });
});

// === NOTIFIKASI ===
function showNotif(msg, type) {
  let box = document.createElement("div");
  box.className = "notif " + type;
  box.innerText = msg;

  document.body.appendChild(box);
  setTimeout(() => box.remove(), 2000);
}
