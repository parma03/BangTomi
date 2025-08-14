// kegiatan/controller/PenugasanController.js

function showAlert(message, type = "info", duration = 5000) {
  const existingAlerts = document.querySelectorAll(".custom-alert");
  existingAlerts.forEach((alert) => alert.remove());

  // Buat elemen alert
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type} alert-dismissible custom-alert`;
  alertDiv.setAttribute("role", "alert");
  alertDiv.style.position = "fixed";
  alertDiv.style.top = "20px";
  alertDiv.style.right = "20px";
  alertDiv.style.zIndex = "9999";
  alertDiv.style.minWidth = "300px";
  alertDiv.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.1)";

  alertDiv.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;

  // Tambahkan ke body
  document.body.appendChild(alertDiv);

  // Auto remove setelah duration
  if (duration > 0) {
    setTimeout(() => {
      if (alertDiv && alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, duration);
  }
}

function showConfirmation(message, onConfirm, onCancel = null) {
  // Hapus modal konfirmasi yang sudah ada
  const existingModal = document.getElementById("confirmationModal");
  if (existingModal) {
    existingModal.remove();
  }

  // Buat modal konfirmasi
  const modalDiv = document.createElement("div");
  modalDiv.innerHTML = `
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Konfirmasi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            ${message}
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">Batal</button>
            <button type="button" class="btn btn-primary" id="confirmBtn">Ya</button>
          </div>
        </div>
      </div>
    </div>
  `;

  document.body.appendChild(modalDiv);

  const modal = new bootstrap.Modal(
    document.getElementById("confirmationModal")
  );

  // Handle konfirmasi
  document.getElementById("confirmBtn").addEventListener("click", () => {
    modal.hide();
    if (onConfirm) onConfirm();
  });

  // Handle batal
  document.getElementById("cancelBtn").addEventListener("click", () => {
    modal.hide();
    if (onCancel) onCancel();
  });

  // Hapus modal setelah ditutup
  document
    .getElementById("confirmationModal")
    .addEventListener("hidden.bs.modal", () => {
      modalDiv.remove();
    });

  modal.show();
}

function logout() {
  showConfirmation("Apakah Anda yakin ingin logout?", function () {
    fetch("controller/KegiatanController.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "request=logout",
    })
      .then((response) => {
        if (response.redirected) {
          window.location.href = response.url;
        } else {
          window.location.href = "../../../index.php";
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showAlert("Terjadi kesalahan saat logout", "danger");
        setTimeout(() => {
          window.location.href = "../../../index.php";
        }, 2000);
      });
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const photoInput = document.getElementById("profile");
  const profilePreview = document.getElementById("profilePreview");

  if (photoInput && profilePreview) {
    photoInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        // Validasi ukuran file (2MB)
        if (file.size > 2 * 1024 * 1024) {
          showAlert("Ukuran file maksimal 2MB", "warning");
          this.value = "";
          return;
        }

        // Validasi tipe file
        const allowedTypes = [
          "image/jpeg",
          "image/jpg",
          "image/png",
          "image/gif",
        ];
        if (!allowedTypes.includes(file.type)) {
          showAlert(
            "Hanya file JPG, PNG, dan GIF yang diperbolehkan",
            "warning"
          );
          this.value = "";
          return;
        }

        // Preview gambar
        const reader = new FileReader();
        reader.onload = function (e) {
          profilePreview.innerHTML = `
            <div class="text-center">
              <div class="position-relative d-inline-block">
                <img src="${
                  e.target.result
                }" class="img-thumbnail rounded-circle shadow-sm" 
                     style="width: 120px; height: 120px; object-fit: cover;">
                <div class="position-absolute top-0 start-100 translate-middle">
                  <span class="badge bg-success rounded-pill">
                    <i class="fas fa-check"></i>
                  </span>
                </div>
              </div>
              <p class="mt-3 text-success mb-0">
                <i class="fas fa-check-circle me-1"></i>Foto siap diupload
              </p>
              <small class="text-muted">${(file.size / 1024 / 1024).toFixed(
                2
              )} MB</small>
            </div>
          `;
          profilePreview.style.display = "block";
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Handle form submit edit profile
  const editProfileForm = document.getElementById("editProfileForm");
  if (editProfileForm) {
    editProfileForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // Validasi password jika diisi
      const currentPassword = document.getElementById("current_password").value;
      const newPassword = document.getElementById("new_password").value;
      const confirmPassword = document.getElementById("confirm_password").value;

      if (newPassword && !currentPassword) {
        showAlert(
          "Masukkan password saat ini untuk mengubah password",
          "warning"
        );
        return;
      }

      if (newPassword && newPassword !== confirmPassword) {
        showAlert("Konfirmasi password tidak sama", "danger");
        return;
      }

      if (newPassword && newPassword.length < 6) {
        showAlert("Password baru minimal 6 karakter", "warning");
        return;
      }

      // Submit form
      const formData = new FormData(this);
      formData.append("request", "update_profile");

      // Disable submit button
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = "Menyimpan...";

      fetch("controller/KegiatanController.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showAlert("Profile berhasil diupdate!", "success");

            // Update profile photo di navbar jika ada perubahan
            if (data.photo_profile) {
              const profileImages =
                document.querySelectorAll('img[alt="Profile"]');
              profileImages.forEach((img) => {
                img.src = data.photo_profile + "?t=" + new Date().getTime();
              });
            }

            // Update nama di navbar
            const nameElements = document.querySelectorAll(".fw-semibold");
            nameElements.forEach((el) => {
              if (el.classList.contains("d-block")) {
                el.textContent = document.getElementById("nama").value;
              }
            });

            // Tutup modal
            const modal = bootstrap.Modal.getInstance(
              document.getElementById("editProfileModal")
            );
            modal.hide();

            // Reload halaman setelah delay singkat
            setTimeout(() => {
              location.reload();
            }, 1000);
          } else {
            showAlert("Error: " + data.message, "danger");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showAlert("Terjadi kesalahan saat menyimpan data", "danger");
        })
        .finally(() => {
          // Enable submit button
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        });
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const editProfileModal = document.getElementById("editProfileModal");
  if (editProfileModal) {
    editProfileModal.addEventListener("hidden.bs.modal", function () {
      // Reset form
      const form = document.getElementById("editProfileForm");
      if (form) {
        form.reset();

        // Reset preview gambar ke original
        const profilePreview = document.getElementById("profilePreview");
        if (profilePreview) {
          const originalSrc =
            profilePreview.getAttribute("data-original-src") ||
            profilePreview.src;
          profilePreview.src = originalSrc;
        }
      }
    });

    // Set original image source saat modal dibuka
    editProfileModal.addEventListener("show.bs.modal", function () {
      const profilePreview = document.getElementById("profilePreview");
      if (profilePreview && !profilePreview.getAttribute("data-original-src")) {
        profilePreview.setAttribute("data-original-src", profilePreview.src);
      }
    });
  }
});

function formatDate(dateString) {
  const options = {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  return new Date(dateString).toLocaleDateString("id-ID", options);
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function isValidPhoneNumber(phone) {
  const phoneRegex = /^[0-9+\-\s()]+$/;
  return phoneRegex.test(phone) && phone.length >= 10;
}

function showSuccessAlert(message) {
  showAlert(message, "success");
}

function showErrorAlert(message) {
  showAlert(message, "danger");
}

function showWarningAlert(message) {
  showAlert(message, "warning");
}

function showInfoAlert(message) {
  showAlert(message, "info");
}

function showLoading(message = "Loading...") {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      title: "Mohon Tunggu",
      text: message,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
  } else {
    // Fallback jika SweetAlert tidak tersedia
    showAlert(message, "info", 0);
  }
}

function hideLoading() {
  if (typeof Swal !== "undefined") {
    Swal.close();
  }
}

$(document).ready(function () {
  // Komentar Section
  console.log("Document ready - Komentar script loaded");

  // Load initial data
  loadKomentarTersembunyi();
  loadKomentarDitampilkan();

  // Event handlers untuk tombol show komentar
  $(document).on("click", ".show-komentar-btn", function () {
    const komentarId = $(this).data("id");
    const nama = $(this).data("nama");

    console.log("Show komentar button clicked:", {
      komentarId,
      nama,
    });

    if (!komentarId || !nama) {
      showAlert(
        "Data komentar tidak lengkap! Refresh halaman dan coba lagi.",
        "warning"
      );
      return;
    }

    showKomentar(komentarId, nama);
  });

  // Event handlers untuk tombol hide komentar
  $(document).on("click", ".hide-komentar-btn", function () {
    const komentarId = $(this).data("id");
    const nama = $(this).data("nama");

    console.log("Hide komentar button clicked:", {
      komentarId,
      nama,
    });

    if (!komentarId || !nama) {
      showAlert(
        "Data komentar tidak lengkap! Refresh halaman dan coba lagi.",
        "warning"
      );
      return;
    }

    hideKomentar(komentarId, nama);
  });

  // Event handlers untuk tombol delete komentar
  $(document).on("click", ".delete-komentar-btn", function () {
    const komentarId = $(this).data("id");
    const nama = $(this).data("nama");

    console.log("Delete komentar button clicked:", {
      komentarId,
      nama,
    });

    if (!komentarId || !nama) {
      showAlert(
        "Data komentar tidak lengkap! Refresh halaman dan coba lagi.",
        "warning"
      );
      return;
    }

    deleteKomentar(komentarId, nama);
  });

  // Initialize tooltips
  initializeTooltips();
});

// Function untuk load komentar tersembunyi
function loadKomentarTersembunyi() {
  const container = $("#komentarTersembunyiContainer");

  console.log("Loading Komentar Tersembunyi data");

  container.html(`
    <div class="text-center py-4">
      <div class="spinner-border text-warning" role="status">
          <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Memuat komentar tersembunyi...</p>
    </div>
  `);

  $.ajax({
    type: "POST",
    url: "controller/KomentarController.php",
    data: {
      request: "get_komentar_tersembunyi",
    },
    dataType: "json",
    success: function (response) {
      console.log("Get Komentar Tersembunyi response:", response);

      if (response.status === "success") {
        container.html(response.html);

        if ($("#komentarTersembunyiTable").length > 0) {
          $("#komentarTersembunyiTable").DataTable({
            responsive: true,
            order: [[0, "desc"]],
            columnDefs: [{ targets: [-1], orderable: false }],
            pageLength: 10,
            language: {
              search: "Cari:",
              lengthMenu: "Tampilkan _MENU_ data per halaman",
              info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
              infoEmpty: "Tidak ada data yang tersedia",
              infoFiltered: "(difilter dari _MAX_ total data)",
              paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya",
              },
              emptyTable: "Tidak ada data yang tersedia dalam tabel",
            },
          });
        }

        // Initialize tooltips after content loaded
        initializeTooltips();
      } else {
        container.html(`
          <div class="alert alert-danger m-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>${
              response.message || "Terjadi kesalahan saat memuat data."
            }
          </div>
        `);
      }
    },
    error: function (xhr, status, error) {
      console.error(
        "AJAX error when loading komentar tersembunyi:",
        xhr,
        status,
        error
      );
      container.html(`
        <div class="alert alert-danger m-3" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memuat data komentar tersembunyi.
          <br><small>Error: ${error}</small>
        </div>
      `);
    },
  });
}

// Function untuk load komentar ditampilkan
function loadKomentarDitampilkan() {
  const container = $("#komentarDitampilkanContainer");

  console.log("Loading Komentar Ditampilkan data");

  container.html(`
    <div class="text-center py-4">
      <div class="spinner-border text-success" role="status">
          <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Memuat komentar ditampilkan...</p>
    </div>
  `);

  $.ajax({
    type: "POST",
    url: "controller/KomentarController.php",
    data: {
      request: "get_komentar_ditampilkan",
    },
    dataType: "json",
    success: function (response) {
      console.log("Get Komentar Ditampilkan response:", response);

      if (response.status === "success") {
        container.html(response.html);

        if ($("#komentarDitampilkanTable").length > 0) {
          $("#komentarDitampilkanTable").DataTable({
            responsive: true,
            order: [[0, "desc"]],
            columnDefs: [{ targets: [-1], orderable: false }],
            pageLength: 10,
            language: {
              search: "Cari:",
              lengthMenu: "Tampilkan _MENU_ data per halaman",
              info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
              infoEmpty: "Tidak ada data yang tersedia",
              infoFiltered: "(difilter dari _MAX_ total data)",
              paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya",
              },
              emptyTable: "Tidak ada data yang tersedia dalam tabel",
            },
          });
        }

        // Initialize tooltips after content loaded
        initializeTooltips();
      } else {
        container.html(`
          <div class="alert alert-danger m-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>${
              response.message || "Terjadi kesalahan saat memuat data."
            }
          </div>
        `);
      }
    },
    error: function (xhr, status, error) {
      console.error(
        "AJAX error when loading komentar ditampilkan:",
        xhr,
        status,
        error
      );
      container.html(`
        <div class="alert alert-danger m-3" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memuat data komentar ditampilkan.
          <br><small>Error: ${error}</small>
        </div>
      `);
    },
  });
}

// Function untuk menampilkan komentar
function showKomentar(komentarId, nama) {
  Swal.fire({
    title: "Tampilkan Komentar",
    html: `
      <div class="text-center">
        <div class="mb-3">
          <i class="fas fa-eye fa-3x text-success mb-3"></i>
        </div>
        <p>Apakah Anda yakin ingin menampilkan komentar dari</p>
        <strong class="text-primary">"${nama}"</strong>
        <p>di website?</p>
        <div class="alert alert-info mt-3 border-0 rounded-3">
          <i class="fas fa-info-circle me-2"></i>
          <small>Komentar yang ditampilkan akan terlihat oleh pengunjung website</small>
        </div>
      </div>
    `,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    cancelButtonColor: "#6c757d",
    confirmButtonText: '<i class="fas fa-eye me-1"></i>Ya, Tampilkan',
    cancelButtonText: '<i class="fas fa-times me-1"></i>Batal',
    customClass: {
      popup: "rounded-4 shadow-lg",
      confirmButton: "btn-lg",
      cancelButton: "btn-lg",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      // Show loading
      showLoading("Menampilkan komentar...");

      $.ajax({
        type: "POST",
        url: "controller/KomentarController.php",
        data: {
          request: "show_komentar",
          komentar_id: komentarId,
          nama: nama,
        },
        dataType: "json",
        success: function (response) {
          hideLoading();

          if (response.status === "success") {
            Swal.fire({
              title: "Berhasil!",
              text: response.message,
              icon: "success",
              confirmButtonText: "OK",
              customClass: {
                popup: "rounded-4 shadow-lg",
              },
            });

            // Reload both containers
            loadKomentarTersembunyi();
            loadKomentarDitampilkan();
          } else {
            showAlert(response.message, "danger");
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", xhr, status, error);
          hideLoading();
          showAlert("Terjadi kesalahan saat menampilkan komentar!", "danger");
        },
      });
    }
  });
}

// Function untuk menyembunyikan komentar
function hideKomentar(komentarId, nama) {
  Swal.fire({
    title: "Sembunyikan Komentar",
    html: `
      <div class="text-center">
        <div class="mb-3">
          <i class="fas fa-eye-slash fa-3x text-warning mb-3"></i>
        </div>
        <p>Apakah Anda yakin ingin menyembunyikan komentar dari</p>
        <strong class="text-primary">"${nama}"</strong>
        <p>dari website?</p>
        <div class="alert alert-warning mt-3 border-0 rounded-3">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <small>Komentar yang disembunyikan tidak akan terlihat oleh pengunjung website</small>
        </div>
      </div>
    `,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#ffc107",
    cancelButtonColor: "#6c757d",
    confirmButtonText: '<i class="fas fa-eye-slash me-1"></i>Ya, Sembunyikan',
    cancelButtonText: '<i class="fas fa-times me-1"></i>Batal',
    customClass: {
      popup: "rounded-4 shadow-lg",
      confirmButton: "btn-lg",
      cancelButton: "btn-lg",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      // Show loading
      showLoading("Menyembunyikan komentar...");

      $.ajax({
        type: "POST",
        url: "controller/KomentarController.php",
        data: {
          request: "hide_komentar",
          komentar_id: komentarId,
          nama: nama,
        },
        dataType: "json",
        success: function (response) {
          hideLoading();

          if (response.status === "success") {
            Swal.fire({
              title: "Berhasil!",
              text: response.message,
              icon: "success",
              confirmButtonText: "OK",
              customClass: {
                popup: "rounded-4 shadow-lg",
              },
            });

            // Reload both containers
            loadKomentarTersembunyi();
            loadKomentarDitampilkan();
          } else {
            showAlert(response.message, "danger");
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", xhr, status, error);
          hideLoading();
          showAlert(
            "Terjadi kesalahan saat menyembunyikan komentar!",
            "danger"
          );
        },
      });
    }
  });
}

// Function untuk menghapus komentar
function deleteKomentar(komentarId, nama) {
  Swal.fire({
    title: "Hapus Komentar",
    html: `
      <div class="text-center">
        <div class="mb-3">
          <i class="fas fa-trash fa-3x text-danger mb-3"></i>
        </div>
        <p>Apakah Anda yakin ingin menghapus komentar dari</p>
        <strong class="text-primary">"${nama}"</strong>?
        <div class="alert alert-danger mt-3 border-0 rounded-3">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <small>Data yang dihapus tidak dapat dikembalikan!</small>
        </div>
      </div>
    `,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#6c757d",
    confirmButtonText: '<i class="fas fa-trash me-1"></i>Ya, Hapus!',
    cancelButtonText: '<i class="fas fa-times me-1"></i>Batal',
    customClass: {
      popup: "rounded-4 shadow-lg",
      confirmButton: "btn-lg",
      cancelButton: "btn-lg",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      // Show loading
      showLoading("Menghapus komentar...");

      $.ajax({
        type: "POST",
        url: "controller/KomentarController.php",
        data: {
          request: "delete_komentar",
          komentar_id: komentarId,
          nama: nama,
        },
        dataType: "json",
        success: function (response) {
          hideLoading();

          if (response.status === "success") {
            Swal.fire({
              title: "Berhasil!",
              text: response.message,
              icon: "success",
              confirmButtonText: "OK",
              customClass: {
                popup: "rounded-4 shadow-lg",
              },
            });

            // Reload both containers
            loadKomentarTersembunyi();
            loadKomentarDitampilkan();
          } else {
            showAlert(response.message, "danger");
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", xhr, status, error);
          hideLoading();
          showAlert("Terjadi kesalahan saat menghapus komentar!", "danger");
        },
      });
    }
  });
}

// Function untuk initialize tooltips
function initializeTooltips() {
  // Destroy existing tooltips first
  $('[data-bs-toggle="tooltip"]').tooltip("dispose");

  // Initialize new tooltips
  $('[data-bs-toggle="tooltip"]').tooltip({
    trigger: "hover",
  });
}

// Helper functions
function formatDate(dateString) {
  const options = {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  return new Date(dateString).toLocaleDateString("id-ID", options);
}

function showSuccessAlert(message) {
  showAlert(message, "success");
}

function showErrorAlert(message) {
  showAlert(message, "danger");
}

function showWarningAlert(message) {
  showAlert(message, "warning");
}

function showInfoAlert(message) {
  showAlert(message, "info");
}

// Global functions to be called from HTML
window.loadKomentarTersembunyi = loadKomentarTersembunyi;
window.loadKomentarDitampilkan = loadKomentarDitampilkan;
