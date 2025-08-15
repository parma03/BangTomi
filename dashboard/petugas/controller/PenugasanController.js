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
  // Penugasan Section
  console.log("Document ready - Penugasan script loaded");
  loadPenugasanData();
  loadKegiatanOptions();
  loadPetugasOptions();

  // Initialize Select2
  $("#id_pegawai").select2({
    theme: "bootstrap-5",
    placeholder: "Pilih Petugas",
    allowClear: true,
    dropdownParent: $("#penugasanFormModal"),
  });

  $(document).on("click", ".send-notification-btn", function () {
    const penugasanId = $(this).data("id");
    const kegiatanName = $(this).data("name");
    const petugasName = $(this).data("petugas");

    console.log("Send notification button clicked:", {
      penugasanId,
      kegiatanName,
      petugasName,
    });

    if (!penugasanId || !kegiatanName || !petugasName) {
      showAlert(
        "Data penugasan tidak lengkap! Refresh halaman dan coba lagi.",
        "warning"
      );
      return;
    }

    sendNotification(penugasanId, kegiatanName, petugasName);
  });

  // Event handlers
  $(document).on("click", ".add-penugasan-btn", function () {
    showPenugasanForm();
  });

  $(document).on("click", ".delete-penugasan-btn", function () {
    const penugasanId = $(this).data("id");
    const kegiatanName = $(this).data("name");
    const petugasName = $(this).data("petugas");

    console.log("Delete button clicked:", {
      penugasanId,
      kegiatanName,
      petugasName,
    });

    if (!penugasanId || !kegiatanName || !petugasName) {
      showAlert(
        "Data penugasan tidak lengkap! Refresh halaman dan coba lagi.",
        "warning"
      );
      return;
    }

    deletePenugasan(penugasanId, kegiatanName, petugasName);
  });

  // Form submission
  $("#penugasanForm").on("submit", function (e) {
    e.preventDefault();
    savePenugasan();
  });

  // Kegiatan change handler
  $("#id_kegiatan").on("change", function () {
    const kegiatanId = $(this).val();
    if (kegiatanId) {
      checkJadwalKegiatan(kegiatanId);
    } else {
      $("#jadwalInfo").addClass("d-none");
      $("#conflictWarning").addClass("d-none");
    }
  });

  // Petugas change handler
  $("#id_pegawai").on("change", function () {
    const kegiatanId = $("#id_kegiatan").val();
    const selectedPetugas = $(this).val();

    if (kegiatanId && selectedPetugas && selectedPetugas.length > 0) {
      checkPetugasConflict(kegiatanId, selectedPetugas);
    } else {
      $("#conflictWarning").addClass("d-none");
    }
  });

  // Functions
  function loadPenugasanData() {
    const container = $(".data-container");

    console.log("Loading Penugasan data");

    container.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat data Penugasan...</p>
      </div>
    `);

    $.ajax({
      type: "POST",
      url: "controller/PenugasanController.php",
      data: {
        request: "get_penugasan",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get Penugasan response:", response);

        if (response.status === "success") {
          container.html(response.html);

          if ($("#penugasanTable").length > 0) {
            $("#penugasanTable").DataTable({
              responsive: true,
              order: [[1, "asc"]],
              columnDefs: [{ targets: [-1], orderable: false }],
            });
          }
        } else {
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
        console.error("AJAX error when loading penugasan:", xhr, status, error);
        container.html(`
          <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memuat data Penugasan.
            <br><small>Error: ${error}</small>
          </div>
        `);
      },
    });
  }

  function loadKegiatanOptions() {
    $.ajax({
      type: "POST",
      url: "controller/PenugasanController.php",
      data: {
        request: "get_kegiatan_options",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get Kegiatan Options response:", response);

        if (response.status === "success") {
          const select = $("#id_kegiatan");
          select.empty();
          select.append('<option value="">Pilih Kegiatan</option>');

          response.data.forEach(function (kegiatan) {
            select.append(
              `<option value="${kegiatan.id_kegiatan}">${kegiatan.judul_kegiatan}</option>`
            );
          });
        } else {
          console.error("Error loading kegiatan options:", response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error(
          "AJAX error when loading kegiatan options:",
          xhr,
          status,
          error
        );
      },
    });
  }

  function loadPetugasOptions() {
    $.ajax({
      type: "POST",
      url: "controller/PenugasanController.php",
      data: {
        request: "get_petugas_options",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get Petugas Options response:", response);

        if (response.status === "success") {
          const select = $("#id_pegawai");
          select.empty();

          response.data.forEach(function (petugas) {
            select.append(
              `<option value="${petugas.id}">${petugas.nama}</option>`
            );
          });
        } else {
          console.error("Error loading petugas options:", response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error(
          "AJAX error when loading petugas options:",
          xhr,
          status,
          error
        );
      },
    });
  }

  function showPenugasanForm() {
    const modal = $("#penugasanFormModal");
    const form = $("#penugasanForm");
    const modalTitle = $("#penugasanFormModalLabel");
    const submitBtn = form.find('button[type="submit"]');

    // Reset form
    form[0].reset();
    $("#id_pegawai").val(null).trigger("change");

    // Clear validation classes
    form.find(".is-invalid").removeClass("is-invalid");
    $("#jadwalInfo").addClass("d-none");
    $("#conflictWarning").addClass("d-none");

    modalTitle.html(
      '<i class="fas fa-user-plus me-2"></i>Tambah Penugasan Baru'
    );
    submitBtn.html('<i class="fas fa-save me-1"></i>Simpan Penugasan');
    $("#idPenugasan").val("");

    modal.modal("show");
  }

  function sendNotification(penugasanId, kegiatanName, petugasName) {
    Swal.fire({
      title: "Kirim Notifikasi",
      html: `
        <div class="text-center">
          <div class="mb-3">
            <i class="fas fa-bell fa-3x text-primary mb-3"></i>
          </div>
          <p>Apakah Anda yakin ingin mengirim notifikasi kepada</p>
          <strong class="text-primary">"${petugasName}"</strong>
          <p>untuk kegiatan</p>
          <strong class="text-info">"${kegiatanName}"</strong>?
          <div class="alert alert-info mt-3 border-0 rounded-3">
            <i class="fas fa-info-circle me-2"></i>
            <small>Notifikasi akan dikirim melalui Telegram Bot</small>
          </div>
        </div>
      `,
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#0d6efd",
      cancelButtonColor: "#6c757d",
      confirmButtonText:
        '<i class="fas fa-paper-plane me-1"></i>Kirim Notifikasi',
      cancelButtonText: '<i class="fas fa-times me-1"></i>Batal',
      customClass: {
        popup: "rounded-4 shadow-lg",
        confirmButton: "btn-lg",
        cancelButton: "btn-lg",
      },
    }).then((result) => {
      if (result.isConfirmed) {
        // Show loading
        showLoading("Mengirim notifikasi...");

        $.ajax({
          type: "POST",
          url: "controller/PenugasanController.php",
          data: {
            request: "send_notification",
            penugasan_id: penugasanId,
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
            } else {
              showAlert(response.message, "danger");
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX error:", xhr, status, error);
            hideLoading();
            showAlert("Terjadi kesalahan saat mengirim notifikasi!", "danger");
          },
        });
      }
    });
  }

  function checkJadwalKegiatan(kegiatanId) {
    $.ajax({
      type: "POST",
      url: "controller/PenugasanController.php",
      data: {
        request: "get_jadwal_kegiatan",
        kegiatan_id: kegiatanId,
      },
      dataType: "json",
      success: function (response) {
        console.log("Get Jadwal Kegiatan response:", response);

        if (response.status === "success") {
          $("#jadwalText").text(response.jadwal);
          $("#jadwalInfo").removeClass("d-none");
        } else {
          $("#jadwalInfo").addClass("d-none");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error when checking jadwal:", xhr, status, error);
      },
    });
  }

  function checkPetugasConflict(kegiatanId, selectedPetugas) {
    $.ajax({
      type: "POST",
      url: "controller/PenugasanController.php",
      data: {
        request: "check_petugas_conflict",
        kegiatan_id: kegiatanId,
        petugas_ids: selectedPetugas,
      },
      dataType: "json",
      success: function (response) {
        console.log("Check Petugas Conflict response:", response);

        if (response.status === "success") {
          if (response.has_conflict) {
            $("#conflictText").text(response.message);
            $("#conflictWarning").removeClass("d-none");
          } else {
            $("#conflictWarning").addClass("d-none");
          }
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error when checking conflict:", xhr, status, error);
      },
    });
  }

  function savePenugasan() {
    const form = $("#penugasanForm");
    const formData = new FormData(form[0]);
    const kegiatanId = $("#id_kegiatan").val();
    const petugasIds = $("#id_pegawai").val();
    const submitBtn = form.find('button[type="submit"]');

    // Clear previous validation
    form.find(".is-invalid").removeClass("is-invalid");

    // Validate required fields
    let isValid = true;
    let firstInvalidField = null;

    if (!kegiatanId) {
      $("#id_kegiatan").addClass("is-invalid");
      isValid = false;
      firstInvalidField = $("#id_kegiatan");
    }

    if (!petugasIds || petugasIds.length === 0) {
      $("#id_pegawai").addClass("is-invalid");
      isValid = false;
      if (!firstInvalidField) {
        firstInvalidField = $("#id_pegawai");
      }
    }

    if (!isValid) {
      if (firstInvalidField) {
        firstInvalidField.focus();
      }
      showAlert("Mohon lengkapi semua field yang diperlukan!", "warning");
      return;
    }

    // Disable submit button
    submitBtn
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');

    formData.append("request", "add_penugasan");

    console.log("Saving penugasan with data:", {
      kegiatan_id: kegiatanId,
      petugas_ids: petugasIds,
    });

    // Show loading
    showLoading("Menyimpan data penugasan...");

    $.ajax({
      type: "POST",
      url: "controller/PenugasanController.php",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html('<i class="fas fa-save me-1"></i>Simpan Penugasan');

        console.log("Save penugasan response:", response);

        if (response.status === "success") {
          showAlert(response.message, "success");
          $("#penugasanFormModal").modal("hide");
          loadPenugasanData();
        } else {
          showAlert(response.message, "danger");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", xhr, status, error);
        console.log("Response text:", xhr.responseText);
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html('<i class="fas fa-save me-1"></i>Simpan Penugasan');
        showAlert("Terjadi kesalahan saat menyimpan data!", "danger");
      },
    });
  }

  function deletePenugasan(penugasanId, kegiatanName, petugasName) {
    Swal.fire({
      title: "Hapus Penugasan",
      html: `
        <div class="text-center">
          <div class="mb-3">
            <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
          </div>
          <p>Apakah Anda yakin ingin menghapus penugasan</p>
          <strong class="text-primary">"${petugasName}"</strong>
          <p>dari kegiatan</p>
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
        showLoading("Menghapus penugasan...");

        $.ajax({
          type: "POST",
          url: "controller/PenugasanController.php",
          data: {
            request: "delete_penugasan",
            penugasan_id: penugasanId,
            kegiatan_name: kegiatanName,
            petugas_name: petugasName,
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
              loadPenugasanData();
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

  // Helper functions - Menggunakan fungsi yang sama dengan KegiatanController
  function showAlert(message, type) {
    // Pastikan fungsi global showAlert tersedia
    if (typeof window.showAlert === "function") {
      window.showAlert(message, type);
    } else {
      // Fallback jika fungsi global tidak tersedia
      const alertClass =
        type === "success"
          ? "alert-success"
          : type === "warning"
          ? "alert-warning"
          : "alert-danger";

      const icon =
        type === "success"
          ? "check-circle"
          : type === "warning"
          ? "exclamation-triangle"
          : "exclamation-circle";

      const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
          <i class="fas fa-${icon} me-2"></i>
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;

      // Remove existing alerts
      $(".alert").remove();

      // Add new alert to the top of the container
      $(".container-xxl").prepend(alertHtml);

      // Auto dismiss after 5 seconds
      setTimeout(() => {
        $(".alert").fadeOut();
      }, 5000);
    }
  }

  function showLoading(message) {
    // Pastikan fungsi global showLoading tersedia
    if (typeof window.showLoading === "function") {
      window.showLoading(message);
    } else {
      // Fallback jika fungsi global tidak tersedia
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
    }
  }

  function hideLoading() {
    // Pastikan fungsi global hideLoading tersedia
    if (typeof window.hideLoading === "function") {
      window.hideLoading();
    } else {
      // Fallback jika fungsi global tidak tersedia
      Swal.close();
    }
  }

  // Debug function
  function debugFormElements() {
    console.log("=== FORM ELEMENTS DEBUG ===");
    console.log("Form exists:", $("#penugasanForm").length > 0);
    console.log("Kegiatan select:", $("#id_kegiatan").length > 0);
    console.log("Petugas select:", $("#id_pegawai").length > 0);
    console.log("Submit button:", $("#submitBtn").length > 0);
  }
});
