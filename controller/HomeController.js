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

// Function to load kegiatan data
function loadKegiatan() {
  fetch("controller/HomeController.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "request=get_kegiatan",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        createKegiatanTabs(data.data);
      } else {
        displayNoKegiatan();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      displayNoKegiatan();
    });
}

// Function to create tabs based on kegiatan data
function createKegiatanTabs(kegiatanList) {
  if (kegiatanList.length === 0) {
    displayNoKegiatan();
    return;
  }

  // Group kegiatan by month
  const kegiatanByMonth = {};
  kegiatanList.forEach((kegiatan) => {
    const date = new Date(kegiatan.jadwal_kegiatan);
    const monthKey = `${date.getFullYear()}-${String(
      date.getMonth() + 1
    ).padStart(2, "0")}`;
    const monthName = date.toLocaleDateString("id-ID", {
      month: "long",
      year: "numeric",
    });

    if (!kegiatanByMonth[monthKey]) {
      kegiatanByMonth[monthKey] = {
        name: monthName,
        kegiatan: [],
      };
    }
    kegiatanByMonth[monthKey].kegiatan.push(kegiatan);
  });

  // Create tabs
  createTabNavigation(kegiatanByMonth);
  createTabContent(kegiatanByMonth);
}

// Function to create tab navigation
function createTabNavigation(kegiatanByMonth) {
  const tabNav = document.getElementById("kegiatanTabs");
  const months = Object.keys(kegiatanByMonth);

  let tabsHtml = "";
  months.forEach((monthKey, index) => {
    const monthData = kegiatanByMonth[monthKey];
    const isActive = index === 0 ? "active show" : "";
    const tabId = `kegiatan-tab-${index + 1}`;

    // Calculate column width based on number of tabs
    const colWidth = Math.floor(12 / Math.min(months.length, 4));

    tabsHtml += `
      <li class="nav-item col-${colWidth}">
        <a class="nav-link ${isActive}" data-bs-toggle="tab" data-bs-target="#${tabId}">
          <i class="bi bi-calendar-event"></i>
          <h4 class="d-none d-lg-block">${monthData.name}</h4>
          <span class="d-lg-none">${monthData.name}</span>
        </a>
      </li>
    `;
  });

  tabNav.innerHTML = tabsHtml;
}

// Function to create tab content
function createTabContent(kegiatanByMonth) {
  const tabContent = document.getElementById("kegiatanTabContent");
  const months = Object.keys(kegiatanByMonth);

  let contentHtml = "";
  months.forEach((monthKey, index) => {
    const monthData = kegiatanByMonth[monthKey];
    const isActive = index === 0 ? "active show" : "";
    const tabId = `kegiatan-tab-${index + 1}`;

    contentHtml += `
      <div class="tab-pane fade ${isActive}" id="${tabId}">
        <div class="row">
          ${monthData.kegiatan
            .map((kegiatan) => createKegiatanCard(kegiatan))
            .join("")}
        </div>
      </div>
    `;
  });

  tabContent.innerHTML = contentHtml;
}

// Function to create kegiatan card (updated with accordion for contact persons)
function createKegiatanCard(kegiatan) {
  const jadwal = new Date(kegiatan.jadwal_kegiatan);
  const jadwalFormatted = jadwal.toLocaleDateString("id-ID", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
  const waktuFormatted = jadwal.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
  });

  // Tentukan apakah thumbnail adalah video atau gambar
  const isVideo =
    kegiatan.thumbnails_kegiatan.toLowerCase().includes(".mp4") ||
    kegiatan.thumbnails_kegiatan.toLowerCase().includes(".webm") ||
    kegiatan.thumbnails_kegiatan.toLowerCase().includes(".ogg");

  const thumbnailPath = `assets/img/thumb/${kegiatan.thumbnails_kegiatan}`;

  // Create unique accordion ID for each kegiatan
  const accordionId = `contactAccordion-${kegiatan.id_kegiatan}`;
  const collapseId = `contactCollapse-${kegiatan.id_kegiatan}`;

  // Create contact persons accordion HTML
  let contactsHtml = "";
  if (kegiatan.petugas && kegiatan.petugas.length > 0) {
    contactsHtml = `
      <div class="kegiatan-contacts">
        <div class="accordion accordion-flush" id="${accordionId}">
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" 
                      data-bs-toggle="collapse" data-bs-target="#${collapseId}" 
                      aria-expanded="false" aria-controls="${collapseId}">
                <i class="bi bi-person-check me-2"></i>
                Kontak Person untuk Absensi (${kegiatan.petugas.length} orang)
              </button>
            </h2>
            <div id="${collapseId}" class="accordion-collapse collapse" 
                 data-bs-parent="#${accordionId}">
              <div class="accordion-body">
                ${kegiatan.petugas
                  .map(
                    (petugas) => `
                  <div class="contact-person">
                    <div class="contact-person-info">
                      <i class="bi bi-person-circle"></i>
                      <div class="contact-info">
                        <div class="contact-name">${petugas.nama}</div>
                        <div class="contact-phone">${petugas.nohp}</div>
                      </div>
                    </div>
                    <a href="https://wa.me/${petugas.nohp.replace(
                      /[^0-9]/g,
                      ""
                    )}" 
                       target="_blank" 
                       class="wa-button">
                      <i class="bi bi-whatsapp"></i> WA
                    </a>
                  </div>
                `
                  )
                  .join("")}
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  return `
    <div class="col-lg-6 col-md-6 col-sm-12" data-aos="fade-up" data-aos-delay="100">
      <div class="kegiatan-card">
        <div class="kegiatan-thumbnail">
          ${
            isVideo
              ? `<video src="${thumbnailPath}" controls muted>
              <source src="${thumbnailPath}" type="video/mp4">
              Browser Anda tidak mendukung video.
            </video>`
              : `<img src="${thumbnailPath}" alt="${kegiatan.judul_kegiatan}" onerror="this.src='assets/img/about-2.jpg'">`
          }
          <div class="kegiatan-date-badge">
            ${jadwal.getDate()} ${jadwal.toLocaleDateString("id-ID", {
    month: "short",
  })}
          </div>
        </div>
        <div class="kegiatan-content">
          <h3 class="kegiatan-title">${kegiatan.judul_kegiatan}</h3>
          <p class="kegiatan-description">${kegiatan.deksripsi_kegiatan}</p>
          <div class="kegiatan-schedule">
            <i class="bi bi-calendar-event"></i>
            <div>
              <strong>${jadwalFormatted}</strong><br>
              <small>Pukul ${waktuFormatted} WIB</small>
            </div>
          </div>
          <div class="kegiatan-schedule">
            <i class="bi bi-geo-alt"></i>
            <div>
              <strong>${kegiatan.alamat_kegiatan ?? "-"}</strong><br>
              <small>Lokasi: ${kegiatan.lokasi_kegiatan ?? "-"}</small>
            </div>
          </div>
          ${contactsHtml}
        </div>
      </div>
    </div>
  `;
}

// Function to display no kegiatan message
function displayNoKegiatan() {
  const container = document.getElementById("kegiatanContainer");
  container.innerHTML = `
    <div class="col-12">
      <div class="no-kegiatan">
        <i class="bi bi-calendar-x"></i>
        <h4>Belum Ada Kegiatan Mendatang</h4>
        <p>Saat ini belum ada kegiatan yang dijadwalkan.</p>
      </div>
    </div>
  `;
}

// Function to load histori kegiatan data
function loadHistoriKegiatan(kategori = "all", limit = null) {
  const requestData = {
    request: "get_histori_kegiatan",
  };

  if (kategori && kategori !== "all") {
    requestData.kategori = kategori;
  }

  if (limit) {
    requestData.limit = limit;
  }

  // Show loading state
  const container = document.querySelector("#portfolio .isotope-container");
  if (container) {
    container.innerHTML = `
      <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Memuat history kegiatan...</p>
      </div>
    `;
  }

  fetch("controller/HomeController.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(requestData).toString(),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        if (data.data && data.data.length > 0) {
          createHistoriTabs(data.data, data.categories);
        } else {
          displayNoHistori();
        }
      } else {
        throw new Error(data.message || "Gagal memuat data");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      displayNoHistoriError(error.message);
    });
}

function displayNoHistoriError(errorMessage = "Terjadi kesalahan") {
  const container = document.querySelector("#portfolio .isotope-container");
  if (!container) return;

  container.innerHTML = `
    <div class="col-12 text-center py-5">
      <div class="error-message">
        <i class="bi bi-exclamation-triangle mb-4" style="font-size: 4rem; color: #dc3545;"></i>
        <h3 class="mb-3" style="color: #dc3545;">Gagal Memuat Data</h3>
        <p class="text-muted mb-4">${errorMessage}</p>
        <button class="btn btn-primary" onclick="loadHistoriKegiatan()">
          <i class="bi bi-arrow-clockwise me-2"></i>Coba Lagi
        </button>
      </div>
    </div>
  `;

  // Clear filters
  const tabNav = document.querySelector("#portfolio .portfolio-filters");
  if (tabNav) {
    tabNav.innerHTML = '<li data-filter="*" class="filter-active">Error</li>';
  }
}

// Function to create histori tabs based on kegiatan data
function createHistoriTabs(historiList, categories) {
  if (!historiList || historiList.length === 0) {
    displayNoHistori();
    return;
  }

  // Group histori by categories if available, otherwise by month
  let historiByCategory = {};

  if (categories && categories.length > 0) {
    // Use predefined categories
    categories.forEach((cat) => {
      historiByCategory[cat.month_key] = {
        name: cat.month_name,
        count: cat.count,
        kegiatan: [],
      };
    });

    // Assign kegiatan to categories
    historiList.forEach((kegiatan) => {
      const date = new Date(kegiatan.jadwal_kegiatan);
      const monthKey = `${date.getFullYear()}-${String(
        date.getMonth() + 1
      ).padStart(2, "0")}`;

      if (historiByCategory[monthKey]) {
        historiByCategory[monthKey].kegiatan.push(kegiatan);
      }
    });
  } else {
    // Group by month like before
    historiList.forEach((kegiatan) => {
      const date = new Date(kegiatan.jadwal_kegiatan);
      const monthKey = `${date.getFullYear()}-${String(
        date.getMonth() + 1
      ).padStart(2, "0")}`;
      const monthName = date.toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
      });

      if (!historiByCategory[monthKey]) {
        historiByCategory[monthKey] = {
          name: monthName,
          kegiatan: [],
        };
      }
      historiByCategory[monthKey].kegiatan.push(kegiatan);
    });
  }

  // Check if we have any data after grouping
  const hasData = Object.keys(historiByCategory).some(
    (key) => historiByCategory[key].kegiatan.length > 0
  );

  if (!hasData) {
    displayNoHistori();
    return;
  }

  // Create tabs
  createHistoriTabNavigation(historiByCategory);
  createHistoriTabContent(historiByCategory);
}

// Function to create histori tab navigation
function createHistoriTabNavigation(historiByCategory) {
  const tabNav = document.querySelector("#portfolio .portfolio-filters");
  if (!tabNav) return;

  const months = Object.keys(historiByCategory);
  let tabsHtml = '<li data-filter="*" class="filter-active">Semua</li>';

  months.forEach((monthKey) => {
    const monthData = historiByCategory[monthKey];
    const count = monthData.count || monthData.kegiatan.length;

    tabsHtml += `
      <li data-filter=".filter-${monthKey}" data-month="${monthKey}">
        ${monthData.name} (${count})
      </li>
    `;
  });

  tabNav.innerHTML = tabsHtml;

  // Reinitialize isotope filters
  initializeHistoriFilters(historiByCategory);
}

// Function to create histori tab content
function createHistoriTabContent(historiByCategory) {
  const container = document.querySelector("#portfolio .isotope-container");
  if (!container) return;

  let contentHtml = "";
  const months = Object.keys(historiByCategory);

  months.forEach((monthKey) => {
    const monthData = historiByCategory[monthKey];

    monthData.kegiatan.forEach((kegiatan) => {
      contentHtml += createHistoriCard(kegiatan, monthKey);
    });
  });

  container.innerHTML = contentHtml;

  // Reinitialize isotope layout if available
  if (typeof Isotope !== "undefined") {
    const iso = new Isotope(container, {
      itemSelector: ".portfolio-item",
      layoutMode: "masonry",
    });

    // Update isotope layout
    setTimeout(() => {
      iso.layout();
    }, 100);
  }
}

// Function to create histori card
function createHistoriCard(kegiatan, monthKey) {
  const jadwal = new Date(kegiatan.jadwal_kegiatan);
  const jadwalFormatted = jadwal.toLocaleDateString("id-ID", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
  const waktuFormatted = jadwal.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
  });

  // Tentukan apakah thumbnail adalah video atau gambar
  const isVideo =
    kegiatan.thumbnails_kegiatan.toLowerCase().includes(".mp4") ||
    kegiatan.thumbnails_kegiatan.toLowerCase().includes(".webm") ||
    kegiatan.thumbnails_kegiatan.toLowerCase().includes(".ogg");

  const thumbnailPath = `assets/img/thumb/${kegiatan.thumbnails_kegiatan}`;

  // Create contact persons HTML
  let contactsHtml = "";
  if (kegiatan.petugas && kegiatan.petugas.length > 0) {
    contactsHtml = kegiatan.petugas
      .map(
        (petugas) => `
        <span class="badge bg-primary me-1 mb-1">
          <i class="bi bi-person-check me-1"></i>${petugas.nama}
        </span>
      `
      )
      .join("");
  }

  // Kehadiran link
  let kehadiranHtml = "";
  if (
    kegiatan.kehadiran_kegiatan &&
    kegiatan.kehadiran_kegiatan.trim() !== ""
  ) {
    kehadiranHtml = `
      <div class="mt-2">
        <a href="${kegiatan.kehadiran_kegiatan}" target="_blank" class="btn btn-sm btn-outline-success">
          <i class="bi bi-file-earmark-spreadsheet me-1"></i>Lihat Kehadiran
        </a>
      </div>
    `;
  }

  return `
    <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-${monthKey}">
      <div class="portfolio-content h-100">
        ${
          isVideo
            ? `<video src="${thumbnailPath}" controls muted style="width: 100%; height: 250px; object-fit: cover;">
                <source src="${thumbnailPath}" type="video/mp4">
                Browser Anda tidak mendukung video.
              </video>`
            : `<img src="${thumbnailPath}" class="img-fluid" alt="${kegiatan.judul_kegiatan}" 
                 style="width: 100%; height: 250px; object-fit: cover;"
                 onerror="this.src='assets/img/about-2.jpg'">`
        }
        <div class="portfolio-info">
          <h4>${kegiatan.judul_kegiatan}</h4>
          <p class="mb-2">${kegiatan.deksripsi_kegiatan.substring(0, 100)}${
    kegiatan.deksripsi_kegiatan.length > 100 ? "..." : ""
  }</p>
          <small class="text-muted">
            <i class="bi bi-calendar-event me-1"></i>${jadwalFormatted} - ${waktuFormatted}
          </small>
          ${contactsHtml ? `<div class="mt-2">${contactsHtml}</div>` : ""}
          ${kehadiranHtml}
          <a href="${thumbnailPath}" title="${kegiatan.judul_kegiatan}" 
             data-gallery="portfolio-gallery-histori" class="glightbox preview-link">
            <i class="bi bi-zoom-in"></i>
          </a>
          <a href="portfolio-details.php?id=${
            kegiatan.id_kegiatan
          }" title="More Details" class="details-link">
            <i class="bi bi-link-45deg"></i>
          </a>
        </div>
      </div>
    </div>
  `;
}

// Function to initialize histori filters
function initializeHistoriFilters(historiByCategory) {
  const filterButtons = document.querySelectorAll(
    "#portfolio .portfolio-filters li"
  );
  const container = document.querySelector("#portfolio .isotope-container");

  if (!container) return;

  filterButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Remove active class from all buttons
      filterButtons.forEach((btn) => btn.classList.remove("filter-active"));
      // Add active class to clicked button
      this.classList.add("filter-active");

      const filterValue = this.getAttribute("data-filter");
      const monthKey = this.getAttribute("data-month");

      if (filterValue === "*") {
        // Show all items
        const items = container.querySelectorAll(".portfolio-item");
        items.forEach((item) => {
          item.style.display = "block";
        });
      } else {
        // Filter items
        const items = container.querySelectorAll(".portfolio-item");
        items.forEach((item) => {
          if (item.classList.contains(`filter-${monthKey}`)) {
            item.style.display = "block";
          } else {
            item.style.display = "none";
          }
        });
      }

      // Trigger layout update if isotope is available
      if (typeof Isotope !== "undefined") {
        const iso = new Isotope(container, {
          itemSelector: '.portfolio-item:not([style*="display: none"])',
          layoutMode: "masonry",
        });
      }
    });
  });
}

// Function to display no histori message
function displayNoHistori() {
  const container = document.querySelector("#portfolio .isotope-container");
  if (!container) return;

  container.innerHTML = `
    <div class="col-12 text-center py-5">
      <div class="no-history-message">
        <i class="bi bi-folder-x mb-4" style="font-size: 4rem; color: #ddd;"></i>
        <h3 class="mb-3" style="color: #666;">Belum Ada History Kegiatan</h3>
        <p class="text-muted mb-4">Saat ini belum ada kegiatan yang telah selesai dilaksanakan.</p>
        <div class="alert alert-info d-inline-block" style="max-width: 500px;">
          <i class="bi bi-info-circle me-2"></i>
          History kegiatan akan muncul setelah ada kegiatan yang statusnya diubah menjadi "Selesai"
        </div>
      </div>
    </div>
  `;

  // Clear filters
  const tabNav = document.querySelector("#portfolio .portfolio-filters");
  if (tabNav) {
    tabNav.innerHTML =
      '<li data-filter="*" class="filter-active">Belum Ada Data</li>';
  }
}

// Tambahkan pemanggilan loadKegiatan() di document ready
document.addEventListener("DOMContentLoaded", function () {
  checkLoginStatus();
  loadKegiatan();
  loadHistoriKegiatan();
});
