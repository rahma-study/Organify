// ==============================
// FILE: firebase-login.js
// ==============================

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.2/firebase-app.js";
import {
  getAuth,
  GoogleAuthProvider,
  signInWithPopup,
} from "https://www.gstatic.com/firebasejs/10.7.2/firebase-auth.js";

const firebaseConfig = {
  apiKey: "AIzaSyBdyaBK9qnTAXFWcBWOyDjAGGKtVryXgYo",
  authDomain: "organify-f5aef.firebaseapp.com",
  projectId: "organify-f5aef",
  storageBucket: "organify-f5aef.appspot.com",
  messagingSenderId: "507592217754",
  appId: "1:507592217754:web:1ba790ac52fa24cf797b23",
  measurementId: "G-HGY2QTJ8G7",
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const provider = new GoogleAuthProvider();

document.addEventListener("DOMContentLoaded", () => {
  // Bisa pakai di login & register
  const googleBtn =
    document.getElementById("googleLogin") ||
    document.getElementById("googleSignUp");
  if (!googleBtn) return; // kalau ga ada tombolnya, diam aja

  googleBtn.addEventListener("click", () => {
    signInWithPopup(auth, provider)
      .then((result) => {
        const user = result.user;
        console.log("✅ Google login berhasil:", user);

        fetch("save_google_user.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            nama: user.displayName,
            email: user.email,
          }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              // kalau login berhasil, arahkan ke halaman utama
              window.location.href = "landing_page.php";
            } else {
              alert(data.message);
            }
          });
      })
      .catch((error) => {
        console.error("❌ Error Google login:", error);
        alert("Gagal login dengan Google.");
      });
  });
});
