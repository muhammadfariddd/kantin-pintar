document.addEventListener("DOMContentLoaded", function () {
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebarIcon = sidebarToggle.querySelector("i");
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.getElementById("main-content");
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
  sidebar.addEventListener("click", function (e) {
    e.stopPropagation();
  });
});
