// petugas/controller/PetugasController.js

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
    fetch("controller/PetugasController.php", {
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

      fetch("controller/PetugasController.php", {
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
  // Petugas Section
  console.log("Document ready - Petugas script loaded");
  console.log("Petugas tab shown, loading data");
  loadPetugasData();

  // Petugas Function
  $(document).on("click", ".view-petugas-btn", function () {
    const petugasId = $(this).data("id");
    showPetugasDetail(petugasId);
  });

  $(document).on("click", ".edit-petugas-btn", function () {
    const petugasId = $(this).data("id");
    showPetugasForm(petugasId);
  });

  $(document).on("click", ".add-petugas-btn", function () {
    showPetugasForm();
  });

  $(document).on("click", ".delete-petugas-btn", function () {
    const petugasId = $(this).data("id");
    const petugasName = $(this).data("name");

    console.log("Delete button clicked:", { petugasId, petugasName });

    // Validasi data sebelum memanggil fungsi delete
    if (!petugasId || !petugasName) {
      showAlert(
        "Data petugas tidak lengkap! Refresh halaman dan coba lagi.",
        "warning"
      );
      return;
    }

    deletePetugas(petugasId, petugasName);
  });

  $("#petugasForm").on("submit", function (e) {
    e.preventDefault();
    savePetugas();
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
    $("#profile").trigger("click");
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
      $("#profile").trigger("click");
    }
  });

  $("#profile").on("change", function () {
    const file = this.files[0];
    const preview = $("#profilePreview");
    const removeBtn = $("#removeProfileBtn");
    const uploadPlaceholder = $("#uploadPlaceholder");

    if (file) {
      console.log("File selected:", file.name, file.size, file.type);

      // Validate file type
      const validTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
      if (!validTypes.includes(file.type)) {
        showAlert(
          "Format file tidak valid! Gunakan JPG, PNG, atau GIF.",
          "warning"
        );
        this.value = "";
        return;
      }

      // Validate file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        showAlert("Ukuran file terlalu besar! Maksimal 2MB.", "warning");
        this.value = "";
        return;
      }

      // Show preview
      const reader = new FileReader();
      reader.onload = function (e) {
        console.log("File loaded for preview");
        uploadPlaceholder.hide();
        preview
          .html(
            `<div class="text-center">
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
            </div>`
          )
          .show();
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
    $("#profile").val("");
    $("#profilePreview").hide();
    $("#uploadPlaceholder").show();
    $(this).hide();
    $("#removeExistingProfile").val("1");
    showAlert("Foto telah dihapus dari preview.", "info");
  });

  $(document).on("click", "#removeCurrentProfile", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $("#currentProfileDisplay").hide();
    $("#removeExistingProfile").val("1");
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
      console.log("File dropped:", files[0].name);
      const input = document.getElementById("profile");
      input.files = files;
      $(input).trigger("change");
    }
  });

  // Petugas Functions
  function loadPetugasData() {
    const container = $(".data-container");

    console.log("Loading Petugas data");

    // Show loading indicator
    container.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat data Petugas...</p>
      </div>
    `);

    // Fetch Petugas data via AJAX
    $.ajax({
      type: "POST",
      url: "controller/PetugasController.php",
      data: {
        request: "get_petugas",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get Petugas response:", response);

        if (response.status === "success") {
          container.html(response.html);

          // Initialize DataTable if table exists
          if ($("#petugasTable").length > 0) {
            $("#petugasTable").DataTable({
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
        console.error("AJAX error when loading petugas:", xhr, status, error);
        console.log("Response text:", xhr.responseText);

        // Show error message
        container.html(`
          <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memuat data Petugas.
            <br><small>Error: ${error}</small>
          </div>
        `);
      },
    });
  }

  function showPetugasDetail(petugasId) {
    const modal = $("#petugasDetailModal");
    const content = $("#petugasDetailContent");

    console.log("Showing petugas detail for ID:", petugasId);

    // Show loading
    content.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat detail Petugas...</p>
      </div>
    `);

    modal.modal("show");

    // Load petugas detail
    $.ajax({
      type: "POST",
      url: "controller/PetugasController.php",
      data: {
        request: "get_petugas_detail",
        petugas_id: petugasId,
      },
      dataType: "json",
      success: function (response) {
        console.log("Petugas detail response:", response);
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

  function showPetugasForm(petugasId = null) {
    const modal = $("#petugasFormModal");
    const form = $("#petugasForm");
    const modalTitle = $("#petugasFormModalLabel");
    const submitBtn = form.find('button[type="submit"]');

    // Reset form
    form[0].reset();
    $("#profilePreview").hide();
    $("#removeProfileBtn").hide();
    $("#uploadPlaceholder").show();
    $("#currentProfileDisplay").hide();
    $("#removeExistingProfile").val("0");

    // Clear validation classes
    form.find(".is-invalid").removeClass("is-invalid");

    if (petugasId) {
      // Mode Edit
      modalTitle.html('<i class="fas fa-edit me-2"></i>Edit Petugas');
      submitBtn.html('<i class="fas fa-save me-1"></i>Update Petugas');

      // Show loading
      showLoading("Memuat data petugas...");

      // Load petugas data for editing
      $.ajax({
        type: "POST",
        url: "controller/PetugasController.php",
        data: {
          request: "get_petugas_by_id",
          petugas_id: petugasId,
        },
        dataType: "json",
        success: function (response) {
          hideLoading();

          console.log("Get petugas by ID response:", response);
          console.log("Petugas data:", response.data);

          if (response.status === "success") {
            const petugas = response.data;

            const modalSelector = "#petugasFormModal ";

            if (petugas.id) {
              $(modalSelector + "#id").val(petugas.id);
            }

            if (petugas.email) {
              $(modalSelector + "#email").val(petugas.email);
            }

            if (petugas.nama) {
              $(modalSelector + "#nama").val(petugas.nama);
            }

            if (petugas.nohp) {
              $(modalSelector + "#nohp").val(petugas.nohp);
            }

            // Verification setelah modal ditampilkan
            setTimeout(() => {
              console.log("=== VERIFICATION AFTER MODAL SHOWN ===");
              console.log(
                "Modal visible:",
                $("#petugasFormModal").is(":visible")
              );
              console.log("ID field value:", $(modalSelector + "#id").val());
              console.log(
                "Email field value:",
                $(modalSelector + "#email").val()
              );
              console.log(
                "Nama field value:",
                $(modalSelector + "#nama").val()
              );
              console.log(
                "No HP field value:",
                $(modalSelector + "#nohp").val()
              );

              // Cek apakah elemen yang benar yang terisi
              debugFormElements();
            }, 500);

            // Handle profile image
            if (petugas.photo_profile) {
              $("#currentProfileDisplay")
                .html(
                  `
                      <div class="mb-4">
                          <label class="form-label fw-semibold">
                              <i class="fas fa-image text-primary me-1"></i>Foto Profile Saat Ini:
                          </label>
                          <div class="current-profile-container p-4 border rounded-4 bg-gradient" style="background: linear-gradient(145deg, #f8f9fa, #ffffff); border: 2px dashed #dee2e6 !important;">
                              <div class="row align-items-center">
                                  <div class="col-auto">
                                      <div class="position-relative">
                                          <img src="../../../assets/img/avatars/${petugas.photo_profile}" 
                                                class="img-thumbnail rounded-circle shadow-sm" 
                                                style="width: 100px; height: 100px; object-fit: cover;"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                          <div class="position-absolute top-0 start-100 translate-middle">
                                              <span class="badge bg-success rounded-pill">
                                                  <i class="fas fa-check"></i>
                                              </span>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="col">
                                      <h6 class="mb-2 text-dark">${petugas.nama}</h6>
                                      <div class="d-flex align-items-center mb-2">
                                          <span class="badge bg-light text-dark me-2">
                                              <i class="fas fa-user me-1"></i>Profile aktif
                                          </span>
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
              response.message || "Gagal memuat data petugas",
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
        '<i class="fas fa-user-plus me-2"></i>Tambah Petugas Baru'
      );
      submitBtn.html('<i class="fas fa-save me-1"></i>Simpan Petugas');
      $("#id").val("");
      $("#password").attr("required", "required");
      $("#password").attr("placeholder", "Masukkan password");
      $("#confirm_passwords").attr("required", "required");
      $("#confirm_passwords").attr("placeholder", "Konfirmasi password");
      modal.modal("show");
    }
  }

  function savePetugas() {
    const form = $("#petugasForm");
    const formData = new FormData(form[0]);
    const petugasId = $("#id").val();
    const password = $("#password").val();
    const confirmPassword = $("#confirm_passwords").val();
    const submitBtn = form.find('button[type="submit"]');

    // Clear previous validation
    form.find(".is-invalid").removeClass("is-invalid");

    // Validate required fields
    const requiredFields = ["email", "nama", "nohp"];
    let isValid = true;
    let firstInvalidField = null;

    requiredFields.forEach((fieldName) => {
      const field = $(`#${fieldName}`);
      const value = field.val().trim();

      if (!value) {
        field.addClass("is-invalid");
        if (!firstInvalidField) {
          firstInvalidField = field;
        }
        isValid = false;
      }
    });

    // Validate password for new petugas
    if (!petugasId && !password) {
      $("#password").addClass("is-invalid");
      showAlert("Password wajib diisi untuk petugas baru!", "warning");
      isValid = false;
    }

    // Validate password match if password is provided
    if (password || confirmPassword) {
      if (password !== confirmPassword) {
        $("#password, #confirm_passwords").addClass("is-invalid");
        showAlert("Password dan konfirmasi password tidak cocok!", "warning");
        isValid = false;
      }

      // Validate password length
      if (password && password.length < 6) {
        $("#password").addClass("is-invalid");
        showAlert("Password minimal 6 karakter!", "warning");
        isValid = false;
      }
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

    // Determine request type based on petugasId
    const requestType = petugasId ? "update_petugas" : "add_petugas";
    formData.append("request", requestType);

    console.log(
      "Saving petugas with request type:",
      requestType,
      "Petugas ID:",
      petugasId
    ); // Debug log

    // Show loading
    showLoading(
      petugasId ? "Mengupdate data petugas..." : "Menyimpan data petugas..."
    );

    $.ajax({
      type: "POST",
      url: "controller/PetugasController.php",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html(
            petugasId
              ? '<i class="fas fa-save me-1"></i>Update Petugas'
              : '<i class="fas fa-save me-1"></i>Simpan Petugas'
          );

        console.log("Save petugas response:", response); // Debug log

        if (response.status === "success") {
          showAlert(response.message, "success");
          $("#petugasFormModal").modal("hide");
          loadPetugasData(); // Reload data
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
            petugasId
              ? '<i class="fas fa-save me-1"></i>Update Petugas'
              : '<i class="fas fa-save me-1"></i>Simpan Petugas'
          );
        showAlert("Terjadi kesalahan saat menyimpan data!", "danger");
      },
    });
  }

  function deletePetugas(petugasId, petugasName) {
    Swal.fire({
      title: "Hapus Petugas",
      html: `
      <div class="text-center">
        <div class="mb-3">
          <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
        </div>
        <p>Apakah Anda yakin ingin menghapus petugas</p>
        <strong class="text-danger">"${petugasName}"</strong>?
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
        showLoading("Menghapus petugas...");

        $.ajax({
          type: "POST",
          url: "controller/PetugasController.php",
          data: {
            request: "delete_petugas",
            petugas_id: petugasId,
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
              loadPetugasData(); // Reload data
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
});
