/*!
 * Color mode toggler for Bootstrap 5.3+
 * Based on https://getbootstrap.com/docs/5.3/customize/color-modes/
 */
(() => {
  "use strict";

  const getStoredTheme = () => localStorage.getItem("theme");
  const setStoredTheme = (theme) => localStorage.setItem("theme", theme);

  const getPreferredTheme = () => {
    const stored = getStoredTheme();
    if (stored) return stored;
    return window.matchMedia("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
  };

  const setOptionColor = (options, path, color) => {
    let node = options;
    for (const key of path.slice(0, -1)) {
      if (!node[key] || typeof node[key] !== "object") return;
      node = node[key];
    }
    node[path[path.length - 1]] = color;
  };

  const updateChartColors = () => {
    if (!window.Chart) return;
    const color = getComputedStyle(document.documentElement)
      .getPropertyValue("--bs-body-color")
      .trim() || "#666";
    window.Chart.defaults.color = color;
    if (typeof window.Chart.getChart !== "function") return;

    document.querySelectorAll("canvas").forEach((canvas) => {
      const chart = window.Chart.getChart(canvas);
      if (!chart) return;
      setOptionColor(chart.options, ["plugins", "legend", "labels", "color"], color);
      setOptionColor(chart.options, ["plugins", "title", "color"], color);
      Object.values(chart.options.scales || {}).forEach((scale) => {
        if (scale.ticks) scale.ticks.color = color;
        if (scale.title) scale.title.color = color;
      });
      chart.update("none");
    });
  };

  const setTheme = (theme) => {
    document.documentElement.setAttribute("data-bs-theme", theme);
    document.dispatchEvent(new CustomEvent("cafebara:themechange", { detail: { theme } }));
  };

  document.addEventListener("cafebara:themechange", updateChartColors);

  const updateToggle = (theme) => {
    const toggle = document.getElementById("theme-toggle");
    if (!toggle) return;

    const icon = toggle.querySelector("[data-theme-icon]");
    const isDark = theme === "dark";

    if (icon) {
      icon.classList.toggle("bi-moon-stars-fill", !isDark);
      icon.classList.toggle("bi-sun-fill", isDark);
    }

    const label = isDark ? "Switch to light mode" : "Switch to dark mode";
    toggle.setAttribute("aria-label", label);
    toggle.setAttribute("title", label);
    window.bootstrap?.Tooltip?.getInstance(toggle)?.setContent({ ".tooltip-inner": label });
  };

  setTheme(getPreferredTheme());

  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", () => {
      if (!getStoredTheme()) {
        const theme = getPreferredTheme();
        setTheme(theme);
        updateToggle(theme);
      }
    });

  document.addEventListener("DOMContentLoaded", () => {
    updateToggle(getPreferredTheme());

    const toggle = document.getElementById("theme-toggle");
    if (toggle) {
      toggle.addEventListener("click", () => {
        const newTheme =
          document.documentElement.getAttribute("data-bs-theme") === "dark"
            ? "light"
            : "dark";
        setStoredTheme(newTheme);
        setTheme(newTheme);
        updateToggle(newTheme);
      });
    }
  });
})();
