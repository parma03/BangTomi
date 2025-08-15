// kegiatan/controller/KegiatanController.js

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
  // Kegiatan Section
  console.log("Document ready - Kegiatan script loaded");
  console.log("Kegiatan tab shown, loading data");
  loadKegiatanData();

  // Kegiatan Function
  $(document).on("click", ".view-kegiatan-btn", function () {
    const kegiatanId = $(this).data("id");
    showKegiatanDetail(kegiatanId);
  });

  $(document).on("click", ".edit-kegiatan-btn", function () {
    const kegiatanId = $(this).data("id");
    showKegiatanForm(kegiatanId);
  });

  $(document).on("click", ".add-kegiatan-btn", function () {
    showKegiatanForm();
  });

  $(document).on("click", ".kehadiran-kegiatan-btn", function () {
    const kegiatanId = $(this).data("id");
    console.log("Kehadiran button clicked for ID:", kegiatanId);

    if (kegiatanId) {
      showKehadiranModal(kegiatanId);
    } else {
      console.error("Kegiatan ID not found");
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "ID Kegiatan tidak ditemukan",
      });
    }
  });

  $(document).on("click", ".delete-kegiatan-btn", function () {
    const kegiatanId = $(this).data("id");
    const kegiatanName = $(this).data("name");

    console.log("Delete button clicked:", { kegiatanId, kegiatanName });

    // Validasi data sebelum memanggil fungsi delete
    if (!kegiatanId || !kegiatanName) {
      showAlert(
        "Data kegiatan tidak lengkap! Refresh halaman dan coba lagi.",
        "warning"
      );
      return;
    }

    deleteKegiatan(kegiatanId, kegiatanName);
  });

  $("#kegiatanForm").on("submit", function (e) {
    e.preventDefault();
    saveKegiatan();
  });

  $("#togglePassword").on("click", function () {
    const passwordField = $("#password");
    const passwordFieldType = passwordField.attr("type");
    const icon = $(this).find("i");

    if (passwordFieldType === "password") {
      passwordField.attr("type", "text");
      icon.removeClass("bx-show").addClass("bx-hide");
    } else {
      passwordField.attr("type", "password");
      icon.removeClass("bx-hide").addClass("bx-show");
    }
  });

  $(document).on("click", ".upload-placeholder", function (e) {
    e.preventDefault();
    e.stopPropagation();
    console.log("Upload placeholder clicked");
    $("#thumbnailImg").trigger("click");
  });

  $(document).on("click", ".profile-upload-area", function (e) {
    // Only trigger if clicking on the area itself, not on buttons inside it
    if (
      e.target === this ||
      $(e.target).hasClass("upload-placeholder") ||
      $(e.target).closest(".upload-placeholder").length > 0
    ) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Profile upload area clicked");
      $("#thumbnailImg").trigger("click");
    }
  });

  $("#thumbnailImg").on("change", function () {
    const file = this.files[0];
    const preview = $("#profilePreview");
    const removeBtn = $("#removeProfileBtn");
    const uploadPlaceholder = $("#uploadPlaceholder");

    if (file) {
      console.log("File selected:", file.name, file.size, file.type);

      // Validate file type - tambahkan video types
      const validImageTypes = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/gif",
      ];
      const validVideoTypes = [
        "video/mp4",
        "video/avi",
        "video/mov",
        "video/wmv",
        "video/webm",
      ];
      const validTypes = [...validImageTypes, ...validVideoTypes];

      if (!validTypes.includes(file.type)) {
        showAlert(
          "Format file tidak valid! Gunakan JPG, PNG, GIF untuk gambar atau MP4, AVI, MOV, WMV, WEBM untuk video.",
          "warning"
        );
        this.value = "";
        return;
      }

      // Validate file size - tingkatkan limit untuk video (max 10MB)
      const maxSize = 50 * 1024 * 1024; // 50MB
      if (file.size > maxSize) {
        showAlert("Ukuran file terlalu besar! Maksimal 50MB.", "warning");
        this.value = "";
        return;
      }

      // Show preview berdasarkan tipe file
      const reader = new FileReader();
      reader.onload = function (e) {
        console.log("File loaded for preview");
        uploadPlaceholder.hide();

        let previewContent = "";

        if (validImageTypes.includes(file.type)) {
          // Preview untuk gambar
          previewContent = `
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
                            <i class="fas fa-image me-1"></i>Gambar siap diupload
                        </p>
                        <small class="text-muted">${(
                          file.size /
                          1024 /
                          1024
                        ).toFixed(2)} MB</small>
                    </div>
                `;
        } else if (validVideoTypes.includes(file.type)) {
          // Preview untuk video
          previewContent = `
                    <div class="text-center">
                        <div class="position-relative d-inline-block">
                            <video class="rounded shadow-sm" style="width: 200px; height: 120px; object-fit: cover;" 
                                   controls muted>
                                <source src="${e.target.result}" type="${
            file.type
          }">
                                Browser Anda tidak mendukung tag video.
                            </video>
                            <div class="position-absolute top-0 start-100 translate-middle">
                                <span class="badge bg-success rounded-pill">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mt-3 text-success mb-0">
                            <i class="fas fa-video me-1"></i>Video siap diupload
                        </p>
                        <small class="text-muted">${(
                          file.size /
                          1024 /
                          1024
                        ).toFixed(2)} MB</small>
                    </div>
                `;
        }

        preview.html(previewContent).show();
        removeBtn.show();
      };
      reader.readAsDataURL(file);
    } else {
      console.log("No file selected");
      preview.hide();
      removeBtn.hide();
      uploadPlaceholder.show();
    }
  });

  $("#removeProfileBtn").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    console.log("Remove profile button clicked");
    $("#thumbnailImg").val("");
    $("#profilePreview").hide();
    $("#uploadPlaceholder").show();
    $(this).hide();
    $("#removeExistingKegiatan").val("1");
    showAlert("Foto telah dihapus dari preview.", "info");
  });

  $(document).on("click", "#removeCurrentProfile", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $("#currentProfileDisplay").hide();
    $("#removeExistingKegiatan").val("1");
    showAlert("Foto profile akan dihapus saat menyimpan.", "info");
  });

  $(".profile-upload-area").on("dragover", function (e) {
    e.preventDefault();
    $(this).addClass("border-primary bg-light");
  });

  $(".profile-upload-area").on("dragleave", function (e) {
    e.preventDefault();
    $(this).removeClass("border-primary bg-light");
  });

  $(".profile-upload-area").on("drop", function (e) {
    e.preventDefault();
    $(this).removeClass("border-primary bg-light");

    const files = e.originalEvent.dataTransfer.files;
    if (files.length > 0) {
      const file = files[0];
      console.log("File dropped:", file.name, file.type);

      // Validasi tipe file saat drop
      const validImageTypes = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/gif",
      ];
      const validVideoTypes = [
        "video/mp4",
        "video/avi",
        "video/mov",
        "video/wmv",
        "video/webm",
      ];
      const validTypes = [...validImageTypes, ...validVideoTypes];

      if (!validTypes.includes(file.type)) {
        showAlert(
          "Format file tidak valid! Gunakan JPG, PNG, GIF untuk gambar atau MP4, AVI, MOV, WMV, WEBM untuk video.",
          "warning"
        );
        return;
      }

      const input = document.getElementById("profile");
      input.files = files;
      $(input).trigger("change");
    }
  });

  function displayThumbnailInDetail(thumbnailPath, isVideo = false) {
    if (isVideo) {
      return `
            <video class="rounded shadow-sm" style="width: 100%; max-width: 300px; height: auto;" 
                   controls>
                <source src="${thumbnailPath}" type="video/mp4">
                Browser Anda tidak mendukung tag video.
            </video>
        `;
    } else {
      return `
            <img src="${thumbnailPath}" class="img-fluid rounded shadow-sm" 
                 style="max-width: 300px; height: auto;" 
                 alt="Thumbnail Kegiatan">
        `;
    }
  }

  // Update CSS untuk mendukung video preview
  const additionalCSS = `
<style>
/* Video preview styling */
.modal-body video {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

/* Responsive video in detail modal */
@media (max-width: 768px) {
    .modal-body video {
        width: 100%;
        max-height: 200px;
        object-fit: cover;
    }
}

/* Upload area animation for video files */
.profile-upload-area.video-ready {
    border-color: #6f42c1;
    background: linear-gradient(145deg, #f3e5f5, #f8f9fa);
}

/* Video file indicator */
.video-indicator {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(111, 66, 193, 0.9);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}
</style>
`;
  // Kegiatan Functions
  function loadKegiatanData() {
    const container = $(".data-container");

    console.log("Loading Kegiatan data");

    // Show loading indicator
    container.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat data Kegiatan...</p>
      </div>
    `);

    // Fetch Kegiatan data via AJAX
    $.ajax({
      type: "POST",
      url: "controller/KegiatanController.php",
      data: {
        request: "get_kegiatan",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get Kegiatan response:", response);

        if (response.status === "success") {
          container.html(response.html);

          // Initialize DataTable if table exists
          if ($("#kegiatanTable").length > 0) {
            $("#kegiatanTable").DataTable({
              responsive: true,
              order: [[1, "asc"]], // Sort by nama
              columnDefs: [
                { targets: [-1], orderable: false }, // Disable sorting on action column
              ],
            });
          }
        } else {
          // Show error message
          container.html(`
            <div class="alert alert-danger" role="alert">
              <i class="fas fa-exclamation-circle me-2"></i>${
                response.message || "Terjadi kesalahan saat memuat data."
              }
            </div>
          `);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error when loading kegiatan:", xhr, status, error);
        console.log("Response text:", xhr.responseText);

        // Show error message
        container.html(`
          <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memuat data Kegiatan.
            <br><small>Error: ${error}</small>
          </div>
        `);
      },
    });
  }

  function showKehadiranModal(kegiatanId) {
    console.log("Loading kehadiran data for kegiatan ID:", kegiatanId);

    // Show loading modal first
    Swal.fire({
      title: "Memuat Data Kehadiran...",
      html: `
      <div class="text-center">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mb-0">Mengambil data dari Google Sheets...</p>
      </div>
    `,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    // Fetch kehadiran data via AJAX
    $.ajax({
      type: "POST",
      url: "controller/KegiatanController.php",
      data: {
        request: "get_kehadiran_kegiatan",
        kegiatan_id: kegiatanId,
      },
      dataType: "json",
      success: function (response) {
        console.log("Get Kehadiran response:", response);

        // Close loading modal
        Swal.close();

        if (response.status === "success") {
          // Remove existing modal if any
          $("#kehadiranModal").remove();

          // Append new modal to body
          $("body").append(response.html);

          // Show the modal
          const kehadiranModal = new bootstrap.Modal(
            document.getElementById("kehadiranModal"),
            {
              backdrop: "static",
              keyboard: false,
            }
          );
          kehadiranModal.show();

          // Add event listener for modal hidden
          $("#kehadiranModal").on("hidden.bs.modal", function () {
            $(this).remove();
          });
        } else {
          // Show error message
          Swal.fire({
            icon: "error",
            title: "Gagal Memuat Data",
            text:
              response.message ||
              "Terjadi kesalahan saat memuat data kehadiran.",
            confirmButtonText: "OK",
            confirmButtonColor: "#3085d6",
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error when loading kehadiran:", xhr, status, error);
        console.log("Response text:", xhr.responseText);

        // Close loading modal
        Swal.close();

        // Show error message
        Swal.fire({
          icon: "error",
          title: "Kesalahan Koneksi",
          html: `
          <p>Terjadi kesalahan saat memuat data kehadiran.</p>
          <small class="text-muted">Error: ${error}</small>
        `,
          confirmButtonText: "OK",
          confirmButtonColor: "#3085d6",
        });
      },
    });
  }

  function showKegiatanDetail(kegiatanId) {
    const modal = $("#kegiatanDetailModal");
    const content = $("#kegiatanDetailContent");

    console.log("Showing kegiatan detail for ID:", kegiatanId);

    // Show loading
    content.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat detail Kegiatan...</p>
      </div>
    `);

    modal.modal("show");

    // Load kegiatan detail
    $.ajax({
      type: "POST",
      url: "controller/KegiatanController.php",
      data: {
        request: "get_kegiatan_detail",
        kegiatan_id: kegiatanId,
      },
      dataType: "json",
      success: function (response) {
        console.log("Kegiatan detail response:", response);
        if (response.status === "success") {
          content.html(response.html);
        } else {
          content.html(`
            <div class="alert alert-danger" role="alert">
              <i class="bx bx-error-circle me-2"></i>${response.message}
            </div>
          `);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", xhr, status, error);
        console.log("Response text:", xhr.responseText);
        content.html(`
          <div class="alert alert-danger" role="alert">
            <i class="bx bx-error-circle me-2"></i>Terjadi kesalahan saat memuat detail.
            <br><small>Error: ${error}</small>
          </div>
        `);
      },
    });
  }

  function showKegiatanForm(kegiatanId = null) {
    const modal = $("#kegiatanFormModal");
    const form = $("#kegiatanForm");
    const modalTitle = $("#kegiatanFormModalLabel");
    const submitBtn = form.find('button[type="submit"]');

    // Reset form
    form[0].reset();
    $("#profilePreview").hide();
    $("#removeProfileBtn").hide();
    $("#uploadPlaceholder").show();
    $("#currentProfileDisplay").hide();
    $("#removeExistingKegiatan").val("0");

    // Clear validation classes
    form.find(".is-invalid").removeClass("is-invalid");

    if (kegiatanId) {
      // Mode Edit
      modalTitle.html('<i class="fas fa-edit me-2"></i>Edit Kegiatan');
      submitBtn.html('<i class="fas fa-save me-1"></i>Update Kegiatan');

      // Show loading
      showLoading("Memuat data kegiatan...");

      // Load kegiatan data for editing
      $.ajax({
        type: "POST",
        url: "controller/KegiatanController.php",
        data: {
          request: "get_kegiatan_by_id",
          kegiatan_id: kegiatanId,
        },
        dataType: "json",
        success: function (response) {
          hideLoading();

          console.log("Get kegiatan by ID response:", response);
          console.log("Kegiatan data:", response.data);

          if (response.status === "success") {
            const kegiatan = response.data;

            const modalSelector = "#kegiatanFormModal ";

            if (kegiatan.id_kegiatan) {
              $(modalSelector + "#idKegiatan").val(kegiatan.id_kegiatan);
            }

            if (kegiatan.judul_kegiatan) {
              $(modalSelector + "#judul_kegiatan").val(kegiatan.judul_kegiatan);
            }

            if (kegiatan.jadwal_kegiatan) {
              $(modalSelector + "#jadwal_kegiatan").val(
                kegiatan.jadwal_kegiatan
              );
            }

            if (kegiatan.deksripsi_kegiatan) {
              $(modalSelector + "#deksripsi_kegiatan").val(
                kegiatan.deksripsi_kegiatan
              );
            }

            if (kegiatan.kehadiran_kegiatan) {
              $(modalSelector + "#kehadiran_kegiatan").val(
                kegiatan.kehadiran_kegiatan
              );
            }

            // Verification setelah modal ditampilkan
            setTimeout(() => {
              console.log("=== VERIFICATION AFTER MODAL SHOWN ===");
              console.log(
                "Modal visible:",
                $("#kegiatanFormModal").is(":visible")
              );
              console.log(
                "ID field value:",
                $(modalSelector + "#idKegiatan").val()
              );
              console.log(
                "Judul field value:",
                $(modalSelector + "#judul_kegiatan").val()
              );
              console.log(
                "Jadwal field value:",
                $(modalSelector + "#jadwal_kegiatan").val()
              );
              console.log(
                "Deskripsi field value:",
                $(modalSelector + "#deksripsi_kegiatan").val()
              );

              console.log(
                "Kehadiran field value:",
                $(modalSelector + "#kehadiran_kegiatan").val()
              );

              // Cek apakah elemen yang benar yang terisi
              debugFormElements();
            }, 500);

            // Handle profile image
            if (kegiatan.thumbnails_kegiatan) {
              $("#currentProfileDisplay")
                .html(
                  `
                      <div class="mb-4">
                          <label class="form-label fw-semibold">
                              <i class="fas fa-image text-primary me-1"></i>Thumbnail Saat Ini:
                          </label>
                          <div class="current-profile-container p-4 border rounded-4 bg-gradient" style="background: linear-gradient(145deg, #f8f9fa, #ffffff); border: 2px dashed #dee2e6 !important;">
                              <div class="row align-items-center">
                                  <div class="col">
                                      <h6 class="mb-2 text-dark">${kegiatan.thumbnails_kegiatan}</h6>
                                      <div class="d-flex align-items-center mb-2">
                                          <small class="text-muted">Diupload sebelumnya</small>
                                      </div>
                                  </div>
                                  <div class="col-auto">
                                      <button type="button" class="btn btn-outline-danger btn-sm" id="removeCurrentProfile">
                                          <i class="fas fa-trash me-1"></i>Hapus
                                      </button>
                                  </div>
                              </div>
                          </div>
                      </div>
                    `
                )
                .show();
            }

            // Make password optional for edit
            $(modalSelector + "#password").removeAttr("required");
            $(modalSelector + "#password").attr(
              "placeholder",
              "Kosongkan jika tidak ingin mengubah password"
            );
            $(modalSelector + "#confirm_passwords").removeAttr("required");
            $(modalSelector + "#confirm_passwords").attr(
              "placeholder",
              "Konfirmasi password baru"
            );

            // Show modal
            modal.modal("show");
          } else {
            showAlert(
              response.message || "Gagal memuat data kegiatan",
              "danger"
            );
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", xhr, status, error);
          console.log("Response text:", xhr.responseText);
          hideLoading();
          showAlert("Terjadi kesalahan saat memuat data!", "danger");
        },
      });
    } else {
      // Mode Add - existing code
      modalTitle.html(
        '<i class="fas fa-user-plus me-2"></i>Tambah Kegiatan Baru'
      );
      submitBtn.html('<i class="fas fa-save me-1"></i>Simpan Kegiatan');
      $("#id").val("");
      $("#password").attr("required", "required");
      $("#password").attr("placeholder", "Masukkan password");
      $("#confirm_passwords").attr("required", "required");
      $("#confirm_passwords").attr("placeholder", "Konfirmasi password");
      modal.modal("show");
    }
  }

  function saveKegiatan() {
    const form = $("#kegiatanForm");
    const formData = new FormData(form[0]);
    const kegiatanId = $("#idKegiatan").val();
    const judulKegiatan = $("#judul_kegiatan").val();
    const jadwalKegiatan = $("#jadwal_kegiatan").val();
    const submitBtn = form.find('button[type="submit"]');

    // Clear previous validation
    form.find(".is-invalid").removeClass("is-invalid");

    // Validate required fields
    const requiredFields = [
      "judul_kegiatan",
      "jadwal_kegiatan",
      "status_kegiatan",
    ];
    let isValid = true;
    let firstInvalidField = null;

    requiredFields.forEach((fieldName) => {
      const field = $(`#${fieldName}`);

      if (field.length === 0) {
        console.warn(`Field ${fieldName} tidak ditemukan di form`);
        return;
      }

      const value = field.val();

      if (!value || (typeof value === "string" && value.trim() === "")) {
        field.addClass("is-invalid");
        if (!firstInvalidField) {
          firstInvalidField = field;
        }
        isValid = false;
      }
    });

    // Validate judulKegiatan for new kegiatan
    if (!kegiatanId && !judulKegiatan) {
      $("#judul_kegiatan").addClass("is-invalid");
      showAlert("Judul wajib diisi untuk kegiatan baru!", "warning");
      isValid = false;
    }

    // Validate jadwalKegiatan for new kegiatan
    if (!kegiatanId && !jadwalKegiatan) {
      $("#jadwal_kegiatan").addClass("is-invalid");
      showAlert("Jadwal wajib diisi untuk kegiatan baru!", "warning");
      isValid = false;
    }

    if (!isValid) {
      if (firstInvalidField) {
        firstInvalidField.focus();
      }
      return;
    }

    // Disable submit button
    submitBtn
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');

    // Determine request type based on kegiatanId
    const requestType = kegiatanId ? "update_kegiatan" : "add_kegiatan";
    formData.append("request", requestType);

    console.log(
      "Saving kegiatan with request type:",
      requestType,
      "Kegiatan ID:",
      kegiatanId
    ); // Debug log

    // Show loading
    showLoading(
      kegiatanId ? "Mengupdate data kegiatan..." : "Menyimpan data kegiatan..."
    );

    $.ajax({
      type: "POST",
      url: "controller/KegiatanController.php",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html(
            kegiatanId
              ? '<i class="fas fa-save me-1"></i>Update Kegiatan'
              : '<i class="fas fa-save me-1"></i>Simpan Kegiatan'
          );

        console.log("Save kegiatan response:", response); // Debug log

        if (response.status === "success") {
          showAlert(response.message, "success");
          $("#kegiatanFormModal").modal("hide");
          loadKegiatanData(); // Reload data
        } else {
          showAlert(response.message, "danger");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", xhr, status, error);
        console.log("Response text:", xhr.responseText); // Debug log
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html(
            kegiatanId
              ? '<i class="fas fa-save me-1"></i>Update Kegiatan'
              : '<i class="fas fa-save me-1"></i>Simpan Kegiatan'
          );
        showAlert("Terjadi kesalahan saat menyimpan data!", "danger");
      },
    });
  }

  function deleteKegiatan(kegiatanId, kegiatanName) {
    Swal.fire({
      title: "Hapus Kegiatan",
      html: `
      <div class="text-center">
        <div class="mb-3">
          <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
        </div>
        <p>Apakah Anda yakin ingin menghapus kegiatan</p>
        <strong class="text-danger">"${kegiatanName}"</strong>?
        <div class="alert alert-warning mt-3 border-0 rounded-3">
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
        showLoading("Menghapus kegiatan...");

        $.ajax({
          type: "POST",
          url: "controller/KegiatanController.php",
          data: {
            request: "delete_kegiatan",
            kegiatan_id: kegiatanId,
            kegiatan_name: kegiatanName,
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
              loadKegiatanData(); // Reload data
            } else {
              showAlert(response.message, "danger");
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX error:", xhr, status, error);
            hideLoading();
            showAlert("Terjadi kesalahan saat menghapus data!", "danger");
          },
        });
      }
    });
  }

  $(document).on("click", ".complete-kegiatan-btn", function () {
    const kegiatanId = $(this).data("id");
    const kegiatanName = $(this).data("name");
    showSelesaikanKegiatanForm(kegiatanId, kegiatanName);
  });

  // Handler untuk form submit selesaikan kegiatan
  $("#selesaikanKegiatanForm").on("submit", function (e) {
    e.preventDefault();
    selesaikanKegiatan();
  });

  // Handler untuk upload multiple files
  $("#dokumentasiFiles").on("change", function () {
    previewMultipleFiles(this.files);
  });

  // Handler untuk drag & drop
  $(".photo-upload-area").on("dragover", function (e) {
    e.preventDefault();
    $(this).addClass("dragover");
  });

  $(".photo-upload-area").on("dragleave", function (e) {
    e.preventDefault();
    $(this).removeClass("dragover");
  });

  $(".photo-upload-area").on("drop", function (e) {
    e.preventDefault();
    $(this).removeClass("dragover");

    const files = e.originalEvent.dataTransfer.files;
    if (files.length > 0) {
      document.getElementById("dokumentasiFiles").files = files;
      previewMultipleFiles(files);
    }
  });

  // Handler untuk klik area upload
  $(document).on("click", ".photo-upload-area", function (e) {
    if (
      e.target === this ||
      $(e.target).hasClass("upload-placeholder") ||
      $(e.target).closest(".upload-placeholder").length > 0
    ) {
      $("#dokumentasiFiles").trigger("click");
    }
  });

  // Handler untuk hapus semua file
  $("#clearAllFiles").on("click", function () {
    $("#dokumentasiFiles").val("");
    $("#filesPreviewContainer").hide();
    $("#uploadPlaceholderDokumentasi").show();
  });

  // Fungsi untuk preview multiple files
  function previewMultipleFiles(files) {
    const previewContainer = $("#filesPreviewContainer");
    const previewList = $("#filesPreviewList");
    const placeholder = $("#uploadPlaceholderDokumentasi");

    if (files.length === 0) {
      previewContainer.hide();
      placeholder.show();
      return;
    }

    previewList.empty();

    Array.from(files).forEach((file, index) => {
      if (!validateFile(file)) {
        return;
      }

      const reader = new FileReader();
      reader.onload = function (e) {
        const isVideo = file.type.startsWith("video/");
        const previewHtml = `
                <div class="col-md-3 col-sm-4 col-6 mb-3">
                    <div class="file-preview-item">
                        <button type="button" class="btn btn-danger btn-sm remove-file" onclick="removeFilePreview(this, ${index})">
                            <i class="bx bx-x"></i>
                        </button>
                        ${
                          isVideo
                            ? `<video class="file-preview-video" controls>
                                <source src="${e.target.result}" type="${file.type}">
                            </video>`
                            : `<img src="${e.target.result}" class="file-preview-image" alt="Preview">`
                        }
                        <div class="mt-2">
                            <small class="text-muted d-block">${
                              file.name
                            }</small>
                            <small class="text-muted">${(
                              file.size /
                              1024 /
                              1024
                            ).toFixed(2)} MB</small>
                        </div>
                    </div>
                </div>
            `;
        previewList.append(previewHtml);
      };
      reader.readAsDataURL(file);
    });

    placeholder.hide();
    previewContainer.show();
  }

  // Fungsi untuk validasi file
  function validateFile(file) {
    const allowedTypes = [
      "image/jpeg",
      "image/jpg",
      "image/png",
      "image/gif",
      "image/webp",
      "image/bmp",
      "video/mp4",
      "video/avi",
      "video/mov",
      "video/wmv",
      "video/webm",
    ];
    const maxSize = 50 * 1024 * 1024; // 50MB

    if (!allowedTypes.includes(file.type)) {
      showAlert(
        `File ${file.name} memiliki format yang tidak didukung!`,
        "warning"
      );
      return false;
    }

    if (file.size > maxSize) {
      showAlert(`File ${file.name} terlalu besar! Maksimal 50MB.`, "warning");
      return false;
    }

    return true;
  }

  // Fungsi untuk menampilkan form selesaikan kegiatan
  function showSelesaikanKegiatanForm(kegiatanId, kegiatanName) {
    $("#kegiatanIdSelesai").val(kegiatanId);
    $("#namaKegiatanSelesai").text(kegiatanName);

    // Reset form
    $("#selesaikanKegiatanForm")[0].reset();
    $("#filesPreviewContainer").hide();
    $("#uploadPlaceholderDokumentasi").show();

    $("#selesaikanKegiatanModal").modal("show");
  }

  // Fungsi untuk selesaikan kegiatan
  function selesaikanKegiatan() {
    const form = $("#selesaikanKegiatanForm");
    const formData = new FormData(form[0]);
    const submitBtn = $("#submitSelesaiBtn");
    const files = document.getElementById("dokumentasiFiles").files;

    // Validasi minimal 1 file
    if (files.length === 0) {
      showAlert("Minimal upload 1 file dokumentasi!", "warning");
      return;
    }

    // Disable submit button
    submitBtn
      .prop("disabled", true)
      .html('<i class="bx bx-loader-alt bx-spin me-1"></i>Memproses...');

    formData.append("request", "selesaikan_kegiatan");

    // Show loading
    showLoading("Menyelesaikan kegiatan dan mengupload dokumentasi...");

    $.ajax({
      type: "POST",
      url: "controller/KegiatanController.php",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html('<i class="bx bx-check me-1"></i>Selesaikan Kegiatan');

        if (response.status === "success") {
          showAlert(response.message, "success");
          $("#selesaikanKegiatanModal").modal("hide");
          loadKegiatanData(); // Reload data
        } else {
          showAlert(response.message, "danger");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", xhr, status, error);
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html('<i class="bx bx-check me-1"></i>Selesaikan Kegiatan');
        showAlert("Terjadi kesalahan saat memproses data!", "danger");
      },
    });
  }
});

// Fungsi untuk remove file dari preview
window.removeFilePreview = function (button, index) {
  console.log("Removing file at index:", index);

  // Remove preview item
  $(button).closest(".col-md-3, .col-sm-4, .col-6").remove();

  // Update file input
  const input = document.getElementById("dokumentasiFiles");
  if (!input || !input.files) return;

  const dt = new DataTransfer();
  const files = Array.from(input.files);

  files.forEach((file, i) => {
    if (i !== index) {
      dt.items.add(file);
    }
  });

  input.files = dt.files;

  // Check if no files left
  if (input.files.length === 0) {
    $("#filesPreviewContainer").hide();
    $("#uploadPlaceholderDokumentasi").show();
  }

  // Re-index remaining buttons
  $("#filesPreviewList .file-preview-item").each(function (newIndex) {
    const removeBtn = $(this).find(".remove-file");
    removeBtn.attr("onclick", `removeFilePreview(this, ${newIndex})`);
  });
};
