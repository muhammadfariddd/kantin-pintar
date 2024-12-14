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
