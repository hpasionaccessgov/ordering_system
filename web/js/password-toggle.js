(() => {
  "use strict";

  const initializePasswordToggle = (input) => {
    if (input.dataset.passwordToggleInitialized === "true") {
      return;
    }

    const container = input.closest(".input-group") || input.parentElement;
    if (!container) {
      return;
    }

    container.classList.add("password-toggle-wrapper");
    input.classList.add("password-toggle-input");

    const button = document.createElement("button");
    button.type = "button";
    button.className = "password-toggle-button";
    button.setAttribute("aria-label", "Show password");
    button.setAttribute("aria-pressed", "false");
    button.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';

    button.addEventListener("click", () => {
      const isVisible = input.type === "text";
      input.type = isVisible ? "password" : "text";
      button.setAttribute("aria-label", isVisible ? "Show password" : "Hide password");
      button.setAttribute("aria-pressed", String(!isVisible));
      button.innerHTML = isVisible
        ? '<i class="bi bi-eye" aria-hidden="true"></i>'
        : '<i class="bi bi-eye-slash" aria-hidden="true"></i>';
    });

    container.append(button);
    input.dataset.passwordToggleInitialized = "true";
  };

  const initializePasswordFields = () => {
    document.querySelectorAll('input[type="password"]').forEach(initializePasswordToggle);
  };

  document.addEventListener("DOMContentLoaded", initializePasswordFields);
})();
