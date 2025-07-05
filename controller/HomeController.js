// controller/HomeController.js

// Check login status on page load
document.addEventListener("DOMContentLoaded", function () {
  checkLoginStatus();
});

// Function to check if user is logged in
function checkLoginStatus() {
  fetch("controller/HomeController.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "request=check_login",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.logged_in) {
        showUserSection(data.user);
      } else {
        showLoginSection();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showLoginSection();
    });
}

// Show login section
function showLoginSection() {
  document.getElementById("loginSection").style.display = "block";
  document.getElementById("userSection").style.display = "none";
}

// Show user section
function showUserSection(user) {
  document.getElementById("loginSection").style.display = "none";
  document.getElementById("userSection").style.display = "block";

  // Update user info
  document.getElementById("userName").textContent = user.nama;
  document.getElementById("userNameDropdown").textContent = user.nama;
  document.getElementById("userRole").textContent =
    user.role.charAt(0).toUpperCase() + user.role.slice(1);

  // Update avatar
  const avatar =
    "assets/img/avatars/" + user.photo_profile || "assets/img/avatars/1.png";
  document.getElementById("userAvatar").src = avatar;
  document.getElementById("userAvatarDropdown").src = avatar;

  // Set dashboard link based on role
  const dashboardLink = document.getElementById("dashboardLink");
  if (user.role === "admin") {
    dashboardLink.href = "dashboard/admin/index.php";
  } else {
    dashboardLink.href = "dashboard/petugas/index.php";
  }

  // Load user data to profile form
  loadUserDataToForm(user);
}

// Load user data to profile form
function loadUserDataToForm(user) {
  document.getElementById("nama").value = user.nama || "";
  document.getElementById("profile_email").value = user.email || "";
  document.getElementById("nohp").value = user.nohp || "";
  document.getElementById("profilePreview").src =
    "assets/img/avatars/" + user.photo_profile || "assets/img/avatars/1.png";
}

// Handle login form submission
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);
  formData.append("request", "login");

  // Disable submit button
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.disabled = true;
  submitBtn.textContent = "Logging in...";

  fetch("controller/HomeController.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Close modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("loginModal")
        );
        modal.hide();

        // Reset form
        this.reset();

        // Show user section
        showUserSection(data.user);

        alert("Login berhasil!");
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan saat login");
    })
    .finally(() => {
      // Enable submit button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    });
});

// Handle profile form submission
document.getElementById("profileForm").addEventListener("submit", function (e) {
  e.preventDefault();

  // Validasi password jika diisi
  const currentPassword = document.getElementById("current_password").value;
  const newPassword = document.getElementById("new_password").value;
  const confirmPassword = document.getElementById("confirm_password").value;

  if (newPassword && !currentPassword) {
    alert("Masukkan password saat ini untuk mengubah password");
    return;
  }

  if (newPassword && newPassword !== confirmPassword) {
    alert("Konfirmasi password tidak sama");
    return;
  }

  if (newPassword && newPassword.length < 6) {
    alert("Password baru minimal 6 karakter");
    return;
  }

  const formData = new FormData(this);
  formData.append("request", "update_profile");

  // Disable submit button
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.disabled = true;
  submitBtn.textContent = "Menyimpan...";

  fetch("controller/HomeController.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Profile berhasil diupdate!");

        // Update UI with new data
        if (data.user) {
          showUserSection(data.user);
        }

        // Close modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("profileModal")
        );
        modal.hide();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan saat menyimpan data");
    })
    .finally(() => {
      // Enable submit button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    });
});

// Handle logout
function logout() {
  if (confirm("Apakah Anda yakin ingin logout?")) {
    fetch("controller/HomeController.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "request=logout",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showLoginSection();
          alert("Logout berhasil!");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showLoginSection();
      });
  }
}

// Preview foto profile saat dipilih
document
  .getElementById("photo_profile")
  .addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (file) {
      // Validasi ukuran file (2MB)
      if (file.size > 2 * 1024 * 1024) {
        alert("Ukuran file maksimal 2MB");
        this.value = "";
        return;
      }

      // Validasi tipe file
      const allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
      if (!allowedTypes.includes(file.type)) {
        alert("Hanya file JPG dan PNG yang diperbolehkan");
        this.value = "";
        return;
      }

      // Preview gambar
      const reader = new FileReader();
      reader.onload = function (e) {
        document.getElementById("profilePreview").src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

// Reset form saat modal ditutup
document
  .getElementById("loginModal")
  .addEventListener("hidden.bs.modal", function () {
    document.getElementById("loginForm").reset();
  });

document
  .getElementById("profileModal")
  .addEventListener("hidden.bs.modal", function () {
    document.getElementById("profileForm").reset();
    // Reset preview gambar
    checkLoginStatus(); // Reload user data
  });
