// appsetting/controller/AppSettingController.js

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
    fetch("controller/AppSettingController.php", {
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

// Media Preview Modal Function - Improved
function showMediaPreview(src, type, title) {
  const modal = document.getElementById("mediaPreviewModal");
  const modalTitle = document.getElementById("mediaPreviewModalLabel");
  const contentDiv = document.getElementById("mediaPreviewContent");

  modalTitle.innerHTML = `<i class="bx bx-show me-2"></i>${title}`;

  // Loading state
  contentDiv.innerHTML = `
    <div class="text-center text-white">
      <div class="spinner-border text-primary mb-3" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p>Memuat media...</p>
    </div>
  `;

  if (type === "image") {
    const img = new Image();
    img.onload = function () {
      contentDiv.innerHTML = `
        <img src="${src}" 
             class="img-fluid rounded shadow" 
             alt="${title}" 
             style="max-height: 80vh; max-width: 100%; object-fit: contain;">
      `;
    };
    img.onerror = function () {
      contentDiv.innerHTML = `
        <div class="text-center text-white p-4">
          <i class="bx bx-error-circle display-1 text-danger mb-3"></i>
          <h5>Gagal memuat gambar</h5>
          <p class="text-muted">File mungkin rusak atau tidak ditemukan</p>
        </div>
      `;
    };
    img.src = src;
  } else if (type === "video") {
    contentDiv.innerHTML = `
      <video controls 
             class="rounded shadow" 
             style="max-height: 80vh; max-width: 100%;"
             onloadstart="this.style.display='block'"
             onerror="this.outerHTML='<div class=\\"text-center text-white p-4\\"><i class=\\"bx bx-error-circle display-1 text-danger mb-3\\"></i><h5>Gagal memuat video</h5><p class=\\"text-muted\\">File mungkin rusak atau format tidak didukung</p></div>'">
        <source src="${src}" type="video/mp4">
        <source src="${src}" type="video/webm">
        <source src="${src}" type="video/ogg">
        Browser Anda tidak mendukung video HTML5.
      </video>
    `;
  }

  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
}

document.addEventListener("DOMContentLoaded", function () {
  // File Upload Handler for Logo
  const logoInput = document.getElementById("logo");
  const logoPreview = document.getElementById("logoPreview");
  const logoUploadArea = document.getElementById("logoUploadArea");

  if (logoInput && logoPreview && logoUploadArea) {
    // Click handler
    logoUploadArea.addEventListener("click", function (e) {
      e.preventDefault();
      logoInput.click();
    });

    // Drag and drop handlers
    logoUploadArea.addEventListener("dragover", function (e) {
      e.preventDefault();
      this.classList.add("dragover");
    });

    logoUploadArea.addEventListener("dragleave", function (e) {
      e.preventDefault();
      this.classList.remove("dragover");
    });

    logoUploadArea.addEventListener("drop", function (e) {
      e.preventDefault();
      this.classList.remove("dragover");

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        const file = files[0];
        if (file.type.startsWith("image/")) {
          logoInput.files = files;
          handleFilePreview(file, logoPreview, "Logo");
        } else {
          showAlert(
            "Hanya file gambar yang diperbolehkan untuk logo",
            "warning"
          );
        }
      }
    });

    logoInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        // Validasi ukuran file (2MB)
        if (file.size > 2 * 1024 * 1024) {
          showAlert("Ukuran file logo maksimal 2MB", "warning");
          this.value = "";
          logoPreview.style.display = "none";
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
            "Hanya file JPG, PNG, dan GIF yang diperbolehkan untuk logo",
            "warning"
          );
          this.value = "";
          logoPreview.style.display = "none";
          return;
        }

        handleFilePreview(file, logoPreview, "Logo");
      }
    });
  }

  // File Upload Handler for Background Header
  const backgroundInput = document.getElementById("background_header");
  const backgroundPreview = document.getElementById("backgroundPreview");
  const backgroundUploadArea = document.getElementById("backgroundUploadArea");

  if (backgroundInput && backgroundPreview && backgroundUploadArea) {
    // Click handler
    backgroundUploadArea.addEventListener("click", function (e) {
      e.preventDefault();
      backgroundInput.click();
    });

    // Drag and drop handlers
    backgroundUploadArea.addEventListener("dragover", function (e) {
      e.preventDefault();
      this.classList.add("dragover");
    });

    backgroundUploadArea.addEventListener("dragleave", function (e) {
      e.preventDefault();
      this.classList.remove("dragover");
    });

    backgroundUploadArea.addEventListener("drop", function (e) {
      e.preventDefault();
      this.classList.remove("dragover");

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        const file = files[0];
        if (file.type.startsWith("image/")) {
          backgroundInput.files = files;
          handleFilePreview(file, backgroundPreview, "Background Header");
        } else {
          showAlert(
            "Hanya file gambar yang diperbolehkan untuk background",
            "warning"
          );
        }
      }
    });

    backgroundInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        // Validasi ukuran file (5MB)
        if (file.size > 5 * 1024 * 1024) {
          showAlert("Ukuran file background maksimal 5MB", "warning");
          this.value = "";
          backgroundPreview.style.display = "none";
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
            "Hanya file JPG, PNG, dan GIF yang diperbolehkan untuk background",
            "warning"
          );
          this.value = "";
          backgroundPreview.style.display = "none";
          return;
        }

        handleFilePreview(file, backgroundPreview, "Background Header");
      }
    });
  }

  // File Upload Handler for Video Header
  const videoInput = document.getElementById("video_header");
  const videoPreview = document.getElementById("videoPreview");
  const videoUploadArea = document.getElementById("videoUploadArea");

  if (videoInput && videoPreview && videoUploadArea) {
    // Click handler
    videoUploadArea.addEventListener("click", function (e) {
      e.preventDefault();
      videoInput.click();
    });

    // Drag and drop handlers
    videoUploadArea.addEventListener("dragover", function (e) {
      e.preventDefault();
      this.classList.add("dragover");
    });

    videoUploadArea.addEventListener("dragleave", function (e) {
      e.preventDefault();
      this.classList.remove("dragover");
    });

    videoUploadArea.addEventListener("drop", function (e) {
      e.preventDefault();
      this.classList.remove("dragover");

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        const file = files[0];
        if (file.type.startsWith("video/")) {
          videoInput.files = files;
          handleVideoPreview(file, videoPreview, "Video Header");
        } else {
          showAlert("Hanya file video yang diperbolehkan", "warning");
        }
      }
    });

    videoInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        // Validasi ukuran file (10MB)
        if (file.size > 100 * 1024 * 1024) {
          showAlert("Ukuran file video maksimal 100MB", "warning");
          this.value = "";
          videoPreview.style.display = "none";
          return;
        }

        // Validasi tipe file
        const allowedTypes = [
          "video/mp4",
          "video/avi",
          "video/mov",
          "video/quicktime",
        ];
        if (!allowedTypes.includes(file.type)) {
          showAlert(
            "Hanya file MP4, AVI, dan MOV yang diperbolehkan untuk video",
            "warning"
          );
          this.value = "";
          videoPreview.style.display = "none";
          return;
        }

        handleVideoPreview(file, videoPreview, "Video Header");
      }
    });
  }

  // Form submission handler
  const appSettingForm = document.getElementById("appSettingForm");
  if (appSettingForm) {
    appSettingForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // Validasi form
      const appName = document.getElementById("app_name").value.trim();
      if (!appName) {
        showAlert("Nama aplikasi harus diisi!", "warning");
        document.getElementById("app_name").focus();
        return;
      }

      // Submit form
      const formData = new FormData(this);
      formData.append("request", "update_app_setting");

      // Disable submit button
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="bx bx-loader-alt bx-spin me-1"></i>Menyimpan...';

      // Show loading
      showLoading("Menyimpan pengaturan aplikasi...");

      fetch("controller/AppSettingController.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          hideLoading();

          if (data.success) {
            showAlert("Pengaturan aplikasi berhasil diupdate!", "success");

            // Reload halaman setelah delay singkat untuk menampilkan perubahan
            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            showAlert("Error: " + data.message, "danger");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          hideLoading();
          showAlert("Terjadi kesalahan saat menyimpan data", "danger");
        })
        .finally(() => {
          // Enable submit button
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        });
    });
  }
});

// Helper function untuk preview file gambar - Improved
function handleFilePreview(file, previewContainer, label) {
  const reader = new FileReader();
  reader.onload = function (e) {
    previewContainer.innerHTML = `
      <div class="text-center">
        <div class="position-relative d-inline-block">
          <div class="preview-container">
            <img src="${e.target.result}" 
                 alt="${label} Preview" 
                 class="img-fluid rounded shadow-sm"
                 style="cursor: pointer;"
                 onclick="showMediaPreview('${
                   e.target.result
                 }', 'image', '${label} Preview')">
          </div>
          <div class="position-absolute top-0 start-100 translate-middle">
            <span class="badge bg-success rounded-pill">
              <i class="bx bx-check"></i>
            </span>
          </div>
        </div>
        <p class="mt-3 text-success mb-0">
          <i class="bx bx-check-circle me-1"></i>${label} siap diupload
        </p>
        <small class="text-muted">${(file.size / 1024 / 1024).toFixed(
          2
        )} MB</small>
        <br>
        <small class="text-info">Klik gambar untuk memperbesar</small>
        <br>
        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="clearPreview('${label
          .toLowerCase()
          .replace(" ", "")}')">
          <i class="bx bx-trash me-1"></i>Hapus
        </button>
      </div>
    `;
    previewContainer.style.display = "block";
  };
  reader.readAsDataURL(file);
}

// Helper function untuk preview video - Improved
function handleVideoPreview(file, previewContainer, label) {
  const reader = new FileReader();
  reader.onload = function (e) {
    previewContainer.innerHTML = `
      <div class="text-center">
        <div class="position-relative d-inline-block">
          <div class="preview-container">
            <video controls class="img-fluid rounded shadow-sm" 
                   style="cursor: pointer; max-height: 200px;"
                   onclick="showMediaPreview('${
                     e.target.result
                   }', 'video', '${label} Preview')">
              <source src="${e.target.result}" type="${file.type}">
              Browser Anda tidak mendukung video HTML5.
            </video>
          </div>
          <div class="position-absolute top-0 start-100 translate-middle">
            <span class="badge bg-success rounded-pill">
              <i class="bx bx-check"></i>
            </span>
          </div>
        </div>
        <p class="mt-3 text-success mb-0">
          <i class="bx bx-check-circle me-1"></i>${label} siap diupload
        </p>
        <small class="text-muted">${(file.size / 1024 / 1024).toFixed(
          2
        )} MB</small>
        <br>
        <small class="text-info">Klik video untuk memperbesar</small>
        <br>
        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="clearPreview('${label
          .toLowerCase()
          .replace(" ", "")}')">
          <i class="bx bx-trash me-1"></i>Hapus
        </button>
      </div>
    `;
    previewContainer.style.display = "block";
  };
  reader.readAsDataURL(file);
}

// Function to clear preview
function clearPreview(type) {
  let inputId, previewId;

  switch (type) {
    case "logo":
      inputId = "logo";
      previewId = "logoPreview";
      break;
    case "backgroundheader":
      inputId = "background_header";
      previewId = "backgroundPreview";
      break;
    case "videoheader":
      inputId = "video_header";
      previewId = "videoPreview";
      break;
  }

  if (inputId && previewId) {
    document.getElementById(inputId).value = "";
    document.getElementById(previewId).style.display = "none";
    document.getElementById(previewId).innerHTML = "";
  }
}

// Utility functions
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
