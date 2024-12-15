document.addEventListener("DOMContentLoaded", function () {
  // Sidebar Toggle
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebarIcon = sidebarToggle.querySelector("i");
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");
  const sidebarOverlay = document.getElementById("sidebarOverlay");

  function toggleSidebar(show) {
    if (window.innerWidth <= 991) {
      if (show) {
        sidebar.classList.add("active");
        sidebarOverlay.style.display = "block";
        requestAnimationFrame(() => {
          sidebarOverlay.style.opacity = "1";
        });
      } else {
        sidebar.classList.remove("active");
        sidebarOverlay.style.opacity = "0";
        setTimeout(() => {
          sidebarOverlay.style.display = "none";
        }, 300);
      }
    } else {
      sidebar.classList.toggle("collapsed");
      mainContent.classList.toggle("expanded");
    }
    updateToggleIcon();
  }

  function updateToggleIcon() {
    if (window.innerWidth <= 991) {
      if (!sidebar.classList.contains("active")) {
        sidebarIcon.classList.remove("fa-arrow-right");
        sidebarIcon.classList.add("fa-bars");
      } else {
        sidebarIcon.classList.remove("fa-bars");
        sidebarIcon.classList.add("fa-arrow-right");
      }
    } else {
      if (sidebar.classList.contains("collapsed")) {
        sidebarIcon.classList.remove("fa-bars");
        sidebarIcon.classList.add("fa-arrow-right");
      } else {
        sidebarIcon.classList.remove("fa-arrow-right");
        sidebarIcon.classList.add("fa-bars");
      }
    }
  }

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (window.innerWidth <= 991) {
        toggleSidebar(!sidebar.classList.contains("active"));
      } else {
        toggleSidebar();
      }
    });
  }

  // Handle overlay and outside click
  document.addEventListener("click", function (e) {
    if (
      window.innerWidth <= 991 &&
      sidebar.classList.contains("active") &&
      !sidebar.contains(e.target) &&
      !sidebarToggle.contains(e.target)
    ) {
      toggleSidebar(false);
    }
  });

  // Prevent clicks inside sidebar from closing it
  sidebar?.addEventListener("click", function (e) {
    e.stopPropagation();
  });

  // Handle window resize
  window.addEventListener("resize", function () {
    if (window.innerWidth > 991) {
      sidebarOverlay.style.display = "none";
      sidebarOverlay.style.opacity = "0";
    }
  });
});

// Enhanced Parallax Effect
window.addEventListener("scroll", function () {
  const parallax = document.querySelector(".parallax-bg");
  const shapes = document.querySelectorAll(".shape");
  const content = document.querySelector(".parallax-content");

  if (parallax && shapes.length) {
    let scrollPosition = window.pageYOffset;
    let speed = 0.5;

    // Parallax background
    parallax.style.transform = `translateY(${scrollPosition * speed}px)`;

    // Parallax shapes with different speeds
    shapes.forEach((shape, index) => {
      let shapeSpeed = 0.2 + index * 0.1;
      let yPos = scrollPosition * shapeSpeed;
      let rotation = scrollPosition * (0.02 + index * 0.01);
      shape.style.transform = `translate3d(0, ${yPos}px, 0) rotate(${rotation}deg)`;
    });

    // Fade content based on scroll
    if (content) {
      let opacity = 1 - scrollPosition * 0.003;
      opacity = Math.max(opacity, 0);
      content.style.opacity = opacity;
    }
  }
});

// Add smooth scroll for all anchor links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    document.querySelector(this.getAttribute("href")).scrollIntoView({
      behavior: "smooth",
    });
  });
});

// Fungsi untuk menandai notifikasi sudah dibaca
function markNotificationRead(notifId) {
  fetch("/api/mark_notification_read.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ notification_id: notifId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const notifElement = document.querySelector(
          `[data-notif-id="${notifId}"]`
        );
        if (notifElement) {
          notifElement.classList.remove("unread");
          updateNotificationBadge();
        }
      }
    });
}

// Fungsi untuk menghapus notifikasi
function deleteNotification(notifId, event) {
  event.stopPropagation();
  if (confirm("Hapus notifikasi ini?")) {
    fetch("/api/delete_notification.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ notification_id: notifId }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const notifElement = document.querySelector(
            `[data-notif-id="${notifId}"]`
          );
          if (notifElement) {
            notifElement.remove();
            updateNotificationBadge();
          }
        }
      });
  }
}

// Fungsi untuk melihat detail pesanan dari notifikasi
function viewOrderFromNotif(orderId, notifId) {
  markNotificationRead(notifId);
  window.location.href = `/orders/history.php?order_id=${orderId}`;
}

// Fungsi untuk menandai semua notifikasi sudah dibaca
function markAllAsRead() {
  fetch("/api/mark_all_read.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Hapus class unread dari semua notifikasi
        document
          .querySelectorAll(".notification-item.unread")
          .forEach((item) => item.classList.remove("unread"));

        // Hapus badge notifikasi
        const badge = document.querySelector("#notificationsDropdown .badge");
        if (badge) {
          badge.remove();
        }
      } else {
        alert(data.message || "Gagal menandai notifikasi sebagai sudah dibaca");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan saat memproses permintaan");
    });
}

// Update badge notifikasi
function updateNotificationBadge() {
  const unreadCount = document.querySelectorAll(
    ".notification-item.unread"
  ).length;
  const badge = document.querySelector("#notificationsDropdown .badge");

  if (unreadCount > 0) {
    if (badge) {
      badge.textContent = unreadCount;
    } else {
      const newBadge = document.createElement("span");
      newBadge.className =
        "position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger";
      newBadge.textContent = unreadCount;
      document.querySelector("#notificationsDropdown").appendChild(newBadge);
    }
  } else if (badge) {
    badge.remove();
  }
}
