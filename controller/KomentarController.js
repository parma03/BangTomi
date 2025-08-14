document.addEventListener("DOMContentLoaded", function () {
  // Handle komentar form submission
  const komentarForm = document.getElementById("komentarForm");

  if (komentarForm) {
    komentarForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      formData.append("action", "submit_komentar");

      const loading = this.querySelector(".loading");
      const errorMessage = this.querySelector(".error-message");
      const sentMessage = this.querySelector(".sent-message");
      const submitButton = this.querySelector('button[type="submit"]');

      // Reset messages
      loading.style.display = "none";
      errorMessage.style.display = "none";
      sentMessage.style.display = "none";

      // Show loading
      loading.style.display = "block";
      submitButton.disabled = true;

      fetch("controller/KomentarController.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          loading.style.display = "none";

          if (data.success) {
            sentMessage.style.display = "block";
            sentMessage.textContent = data.message;
            this.reset(); // Reset form

            // Reset star rating
            const radioButtons = this.querySelectorAll('input[name="rating"]');
            radioButtons.forEach((radio) => (radio.checked = false));
          } else {
            errorMessage.style.display = "block";
            errorMessage.textContent = data.message;
          }
        })
        .catch((error) => {
          loading.style.display = "none";
          errorMessage.style.display = "block";
          errorMessage.textContent = "Terjadi kesalahan. Silakan coba lagi.";
          console.error("Error:", error);
        })
        .finally(() => {
          submitButton.disabled = false;
        });
    });
  }

  // Handle star rating interaction
  const starInputs = document.querySelectorAll(".star-rating input");
  starInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const rating = this.value;
      console.log("Rating selected:", rating);
    });
  });
});

// Function untuk update navigation menu (jika perlu)
function updateNavigationMenu() {
  const navMenu = document.querySelector("#navmenu ul");
  if (navMenu) {
    // Ganti link contact dengan komentar
    const contactLink = navMenu.querySelector('a[href="#contact"]');
    if (contactLink) {
      contactLink.setAttribute("href", "#komentar");
      contactLink.textContent = "Komentar";
    }
  }
}

// Call function when DOM is loaded
document.addEventListener("DOMContentLoaded", updateNavigationMenu);
