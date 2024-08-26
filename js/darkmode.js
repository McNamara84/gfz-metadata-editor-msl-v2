document.addEventListener("DOMContentLoaded", function () {
  const themeDropdown = document.getElementById("bd-theme");
  const themeDropdownItems = themeDropdown.parentElement.querySelectorAll(".dropdown-item");

  function setTheme(theme) {
    document.documentElement.setAttribute("data-bs-theme", theme);
    localStorage.setItem("theme", theme);
    themeDropdownItems.forEach((item) => {
      item.classList.toggle("active", item.getAttribute("data-bs-theme-value") === theme);
    });
  }

  function setAutoTheme() {
    const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");
    if (prefersDarkScheme.matches) {
      setTheme("dark");
    } else {
      setTheme("light");
    }
  }

  themeDropdownItems.forEach((item) => {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      const theme = this.getAttribute("data-bs-theme-value");
      if (theme === "auto") {
        setAutoTheme();
      } else {
        setTheme(theme);
      }
    });
  });

  const storedTheme = localStorage.getItem("theme");
  if (storedTheme) {
    if (storedTheme === "auto") {
      setAutoTheme();
    } else {
      setTheme(storedTheme);
    }
  } else {
    setAutoTheme();
  }
});
