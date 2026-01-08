// 🌿 Efek Navbar Saat Scroll
window.addEventListener("scroll", function () {
  const header = document.getElementById("header");

  if (window.scrollY > 50) {
    header.classList.add("scrolled");
  } else {
    header.classList.remove("scrolled");
  }
});

// 🌿 FAQ Toggle
document.querySelectorAll(".faq-question").forEach(function (item) {
  item.addEventListener("click", function () {
    item.parentElement.classList.toggle("active");
  });
});
