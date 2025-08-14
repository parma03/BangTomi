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
  console.log("Dashboard script loaded");

  // Initialize dashboard
  initializeDashboard();

  // Global variables
  let calendar;
  let currentFilter = "month";

  function initializeDashboard() {
    loadDashboardStats();
    // Delay calendar initialization to ensure FullCalendar is loaded
    setTimeout(initializeCalendar, 100);
    loadUpcomingEvents();

    // Auto refresh every 5 minutes
    setInterval(function () {
      loadDashboardStats();
      loadUpcomingEvents();
    }, 300000);
  }

  function loadDashboardStats() {
    console.log("Loading dashboard stats...");

    $.ajax({
      type: "POST",
      url: "controller/IndexController.php",
      data: {
        request: "get_dashboard_stats",
      },
      dataType: "json",
      success: function (response) {
        console.log("Dashboard stats response:", response);

        if (response.status === "success") {
          const stats = response.data;

          // Update statistics with animation
          animateCounter("#totalKegiatan", stats.total_kegiatan);
          animateCounter("#kegiatanPending", stats.kegiatan_pending);
          animateCounter("#kegiatanSelesai", stats.kegiatan_selesai);
          animateCounter("#totalPetugas", stats.total_petugas);
          animateCounter("#kegiatanHariIni", stats.kegiatan_hari_ini);
          animateCounter("#kegiatanMingguIni", stats.kegiatan_minggu_ini);
        } else {
          console.error("Error loading dashboard stats:", response.message);
          showAlert("Gagal memuat statistik dashboard", "error");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error loading dashboard stats:", error);
        showAlert("Terjadi kesalahan saat memuat statistik", "error");
      },
    });
  }

  function initializeCalendar() {
    // Check if FullCalendar is loaded
    if (typeof FullCalendar === "undefined") {
      console.error("FullCalendar not loaded, retrying...");
      setTimeout(initializeCalendar, 500);
      return;
    }

    const calendarEl = document.getElementById("calendar");

    if (!calendarEl) {
      console.error("Calendar element not found");
      return;
    }

    calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      locale: "id",
      height: "auto",
      themeSystem: "bootstrap5",
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,listWeek",
      },
      buttonText: {
        today: "Hari Ini",
        month: "Bulan",
        week: "Minggu",
        list: "Daftar",
      },
      events: function (fetchInfo, successCallback, failureCallback) {
        loadCalendarEvents(fetchInfo, successCallback, failureCallback);
      },
      eventClick: function (info) {
        info.jsEvent.preventDefault();
        showEventDetail(info.event.id);
      },
      eventMouseEnter: function (info) {
        // Enhanced tooltip
        const tooltip = `
          <div class="fc-tooltip">
            <strong>${info.event.title}</strong><br>
            <small>Petugas: ${
              info.event.extendedProps.petugas_count || 0
            } orang</small><br>
            <small>Status: ${
              info.event.extendedProps.status || "Unknown"
            }</small>
          </div>
        `;

        $(info.el).attr(
          "title",
          info.event.title +
            " - " +
            (info.event.extendedProps.petugas_count || 0) +
            " petugas"
        );
      },
      dateClick: function (info) {
        console.log("Date clicked:", info.dateStr);
        // Optional: Could add new event functionality here
      },
      // Add loading state
      loading: function (bool) {
        if (bool) {
          $("#calendar").append('<div class="fc-loading">Loading...</div>');
        } else {
          $(".fc-loading").remove();
        }
      },
    });

    calendar.render();
    console.log("Calendar initialized successfully");
  }

  function loadCalendarEvents(fetchInfo, successCallback, failureCallback) {
    const startDate = fetchInfo.start.toISOString().split("T")[0];
    const endDate = fetchInfo.end.toISOString().split("T")[0];

    $.ajax({
      type: "POST",
      url: "controller/IndexController.php",
      data: {
        request: "get_calendar_data",
        filter: currentFilter,
        start_date: startDate,
        end_date: endDate,
      },
      dataType: "json",
      success: function (response) {
        console.log("Calendar events response:", response);

        if (response.status === "success") {
          // Enhanced event formatting
          const formattedEvents = response.events.map((event) => {
            return {
              id: event.id,
              title: event.title,
              start: event.start,
              description: event.description,
              backgroundColor: getEventColor(event.status),
              borderColor: getEventColor(event.status, true),
              textColor: "#fff",
              extendedProps: {
                status: event.status,
                petugas_count: event.petugas_count,
                petugas_info: event.petugas_info,
                description: event.description,
              },
            };
          });

          successCallback(formattedEvents);
        } else {
          console.error("Error loading calendar events:", response.message);
          failureCallback();
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error loading calendar events:", error);
        failureCallback();
      },
    });
  }

  function getEventColor(status, isBorder = false) {
    const colors = {
      selesai: isBorder ? "#1e7e34" : "#28a745",
      pending: isBorder ? "#e0a800" : "#ffc107",
      default: isBorder ? "#6c757d" : "#6c757d",
    };
    return colors[status] || colors.default;
  }

  // Rest of the functions remain the same...
  function loadUpcomingEvents() {
    console.log("Loading upcoming events...");

    $.ajax({
      type: "POST",
      url: "controller/IndexController.php",
      data: {
        request: "get_upcoming_events",
      },
      dataType: "json",
      success: function (response) {
        console.log("Upcoming events response:", response);

        if (response.status === "success") {
          renderUpcomingEvents(response.events);
        } else {
          $("#upcomingEvents").html(`
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-2"></i>
                            ${
                              response.message || "Tidak ada kegiatan mendatang"
                            }
                        </div>
                    `);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error loading upcoming events:", error);
        $("#upcomingEvents").html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error me-2"></i>
                        Gagal memuat kegiatan mendatang
                    </div>
                `);
      },
    });
  }

  function renderUpcomingEvents(events) {
    let html = "";

    if (events.length === 0) {
      html = `
                <div class="text-center py-3 text-muted">
                    <i class="bx bx-calendar-x" style="font-size: 3rem;"></i>
                    <p class="mt-2">Tidak ada kegiatan mendatang</p>
                </div>
            `;
    } else {
      events.forEach(function (event) {
        const eventDate = new Date(event.jadwal_kegiatan);
        const formattedDate = eventDate.toLocaleDateString("id-ID", {
          weekday: "short",
          year: "numeric",
          month: "short",
          day: "numeric",
        });
        const formattedTime = eventDate.toLocaleTimeString("id-ID", {
          hour: "2-digit",
          minute: "2-digit",
        });

        const statusBadge =
          event.status_kegiatan === "selesai"
            ? '<span class="badge bg-success">Selesai</span>'
            : '<span class="badge bg-warning">Pending</span>';

        html += `
                    <div class="d-flex align-items-start mb-3 p-3 border rounded hover-shadow" style="cursor: pointer;" onclick="showEventDetail(${event.id_kegiatan})">
                        <div class="avatar flex-shrink-0 me-3">
                            <i class="bx bx-calendar-event text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${event.judul_kegiatan}</h6>
                            <p class="text-muted mb-1 small">
                                <i class="bx bx-time me-1"></i>${formattedDate} ${formattedTime}
                            </p>
                            <p class="text-muted mb-1 small">
                                <i class="bx bx-group me-1"></i>${event.jumlah_petugas} Petugas
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                ${statusBadge}
                            </div>
                        </div>
                    </div>
                `;
      });
    }

    $("#upcomingEvents").html(html);
  }

  function showEventDetail(eventId) {
    console.log("Showing event detail for ID:", eventId);

    // Show loading in modal
    $("#eventDetailContent").html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat detail kegiatan...</p>
            </div>
        `);

    $("#eventDetailModal").modal("show");

    $.ajax({
      type: "POST",
      url: "controller/IndexController.php",
      data: {
        request: "get_event_details",
        event_id: eventId,
      },
      dataType: "json",
      success: function (response) {
        console.log("Event detail response:", response);

        if (response.status === "success") {
          renderEventDetail(response.event);
        } else {
          $("#eventDetailContent").html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-error me-2"></i>
                            ${
                              response.message || "Gagal memuat detail kegiatan"
                            }
                        </div>
                    `);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error loading event detail:", error);
        $("#eventDetailContent").html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error me-2"></i>
                        Terjadi kesalahan saat memuat detail kegiatan
                    </div>
                `);
      },
    });
  }

  function renderEventDetail(event) {
    const eventDate = new Date(event.jadwal_kegiatan);
    const formattedDate = eventDate.toLocaleDateString("id-ID", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    });
    const formattedTime = eventDate.toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    });

    const statusBadge =
      event.status_kegiatan === "selesai"
        ? '<span class="badge bg-success fs-6">Selesai</span>'
        : '<span class="badge bg-warning fs-6">Pending</span>';

    let petugasHtml = "";
    if (event.petugas && event.petugas.length > 0) {
      petugasHtml = `
                <div class="row">
                    ${event.petugas
                      .map(
                        (petugas) => `
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            ${
                                              petugas.photo_profile
                                                ? `<img src="../../../assets/img/avatars/${petugas.photo_profile}" class="rounded-circle" width="40" height="40" alt="Profile">`
                                                : `<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bx bx-user"></i>
                                                </div>`
                                            }
                                        </div>
                                        <div>
                                            <h6 class="mb-1">${
                                              petugas.nama
                                            }</h6>
                                            <p class="text-muted mb-0 small">${
                                              petugas.email
                                            }</p>
                                            <p class="text-muted mb-0 small">${
                                              petugas.nohp
                                            }</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `
                      )
                      .join("")}
                </div>
            `;
    } else {
      petugasHtml = `
                <div class="text-center py-3 text-muted">
                    <i class="bx bx-user-x" style="font-size: 3rem;"></i>
                    <p class="mt-2">Belum ada petugas yang ditugaskan</p>
                </div>
            `;
    }

    const html = `
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 bg-primary text-white mb-4">
                        <div class="card-body">
                            <h4 class="card-title text-white mb-2">${
                              event.judul_kegiatan
                            }</h4>
                            <p class="card-text mb-3">${
                              event.deksripsi_kegiatan
                            }</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="bx bx-calendar me-2"></i>
                                        ${formattedDate}
                                    </p>
                                    <p class="mb-1">
                                        <i class="bx bx-time me-2"></i>
                                        ${formattedTime}
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p class="mb-1">Status: ${statusBadge}</p>
                                    <p class="mb-0">
                                        <i class="bx bx-group me-1"></i>
                                        ${
                                          event.petugas
                                            ? event.petugas.length
                                            : 0
                                        } Petugas
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <h5 class="mb-3">
                        <i class="bx bx-group me-2"></i>
                        Petugas yang Ditugaskan
                    </h5>
                    ${petugasHtml}
                </div>
            </div>
            
            ${
              event.kehadiran_kegiatan
                ? `
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bx bx-link me-2"></i>
                                    Link Kehadiran
                                </h6>
                                <a href="${event.kehadiran_kegiatan}" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-link-external me-1"></i>
                                    Buka Link Kehadiran
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `
                : ""
            }
        `;

    $("#eventDetailContent").html(html);
  }

  function animateCounter(selector, endValue) {
    const element = $(selector);
    const startValue = parseInt(element.text()) || 0;
    const duration = 1000;
    const startTime = Date.now();

    function updateCounter() {
      const elapsed = Date.now() - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const currentValue = Math.floor(
        startValue + (endValue - startValue) * progress
      );

      element.text(currentValue);

      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      }
    }

    requestAnimationFrame(updateCounter);
  }

  // Calendar filter functions
  window.filterCalendar = function (filter) {
    console.log("Filtering calendar:", filter);
    currentFilter = filter;

    const filterText = {
      day: "Hari Ini",
      week: "Minggu Ini",
      month: "Bulan Ini",
    };

    $("#calendarFilter").html(
      `<i class="bx bx-filter me-1"></i>${filterText[filter]}`
    );

    if (calendar) {
      calendar.refetchEvents();
    }
  };

  // Enhanced date range filter
  window.filterByDateRange = function () {
    const startDate = document.getElementById("startDate").value;
    const endDate = document.getElementById("endDate").value;

    if (startDate && endDate) {
      if (calendar) {
        calendar.gotoDate(startDate);
        calendar.refetchEvents();
      }
    }
  };

  // Refresh dashboard function
  window.refreshDashboard = function () {
    console.log("Refreshing dashboard...");
    showAlert("Memperbarui dashboard...", "info");

    loadDashboardStats();
    loadUpcomingEvents();

    if (calendar) {
      calendar.refetchEvents();
    }

    setTimeout(() => {
      showAlert("Dashboard berhasil diperbarui!", "success");
    }, 1000);
  };

  function showAlert(message, type) {
    const alertTypes = {
      success: "alert-success",
      error: "alert-danger",
      warning: "alert-warning",
      info: "alert-info",
    };

    const iconTypes = {
      success: "bx-check-circle",
      error: "bx-error",
      warning: "bx-error-circle",
      info: "bx-info-circle",
    };

    const alertClass = alertTypes[type] || "alert-info";
    const iconClass = iconTypes[type] || "bx-info-circle";

    const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bx ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

    $("#alertContainer").html(alertHtml);

    setTimeout(() => {
      $(".alert").fadeOut();
    }, 5000);
  }

  // Add hover effects to cards
  $(document)
    .on("mouseenter", ".card", function () {
      $(this).addClass("shadow-sm");
    })
    .on("mouseleave", ".card", function () {
      $(this).removeClass("shadow-sm");
    });
});

// Add custom CSS for enhanced styling
const style = document.createElement("style");
style.textContent = `
    .hover-shadow:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        transition: box-shadow 0.15s ease-in-out;
    }
    
    .fc-event {
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: 4px;
    }
    
    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .card {
        transition: box-shadow 0.15s ease-in-out;
    }
    
    .fc-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 999;
        background: rgba(255, 255, 255, 0.9);
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
    }
    
    .fc-tooltip {
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .calendar-filter-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
`;
document.head.appendChild(style);
