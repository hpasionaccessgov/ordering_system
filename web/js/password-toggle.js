(() => {
  "use strict";

  const initializePasswordToggle = (input) => {
    if (input.dataset.passwordToggleInitialized === "true") {
      return;
    }

    const inputGroup = input.closest(".input-group");
    const container = inputGroup || document.createElement("div");
    if (!inputGroup) {
      container.className = "password-toggle-field";
      input.parentNode.insertBefore(container, input);
      container.append(input);
    } else {
      container.classList.add("password-toggle-wrapper");
    }

    if (!container) {
      return;
    }

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
