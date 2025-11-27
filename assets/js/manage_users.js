document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll(".user-form");
  const removeButtons = document.querySelectorAll(".remove-btn");

  // ✅ Play click sound
  function playClickSound() {
    const audio = new Audio("../assets/sound/click.mp3");
    audio.volume = 0.5;
    audio.play();
  }

  // ✅ Popup display
  function showPopup(type, message) {
    const popup = document.createElement("div");
    popup.className = `popup-message ${type}`;
    popup.innerHTML = `<div class="popup-content"><p>${message}</p></div>`;
    document.body.appendChild(popup);

    setTimeout(() => popup.classList.add("show"), 50);
    setTimeout(() => popup.classList.remove("show"), 2500);
    setTimeout(() => popup.remove(), 3000);
  }

  // ✅ Disable buttons based on group state
  forms.forEach(form => {
    const select = form.querySelector("select[name='group']");
    const updateBtn = form.querySelector(".update-btn");
    const removeBtn = form.querySelector(".remove-btn");
    const currentGroup = form.dataset.currentGroup;

    // Disable Remove if no group
    if (!currentGroup || currentGroup.trim() === "") {
      removeBtn.disabled = true;
    }

    // Disable Update if same group or no change
    select.addEventListener("change", () => {
      const newGroup = select.value;
      if (newGroup === currentGroup || newGroup === "") {
        updateBtn.disabled = true;
      } else {
        updateBtn.disabled = false;
      }
    });

    // Initialize on load
    if (select.value === currentGroup || select.value === "") {
      updateBtn.disabled = true;
    }
  });

  // ✅ Handle group update
  forms.forEach(form => {
    form.addEventListener("submit", async e => {
      e.preventDefault();
      const formData = new FormData(form);

      try {
        const res = await fetch("update_user.php", { method: "POST", body: formData });
        const data = await res.json();

        if (data.status === "success") {
          showPopup("success", data.message);
          playClickSound();
          setTimeout(() => location.reload(), 1500);
        } else if (data.status === "info") {
          showPopup("info", data.message);
        } else {
          showPopup("error", data.message);
        }
      } catch {
        showPopup("error", "An error occurred while updating user.");
      }
    });
  });

  // ✅ Handle remove group
  removeButtons.forEach(btn => {
    btn.addEventListener("click", async () => {
      if (btn.disabled) return;

      const userId = btn.dataset.id;
      const formData = new FormData();
      formData.append("user_id", userId);
      formData.append("remove_group", "1");

      try {
        const res = await fetch("update_user.php", { method: "POST", body: formData });
        const data = await res.json();

        if (data.status === "success") {
          showPopup("success", data.message);
          playClickSound();
          setTimeout(() => location.reload(), 1500);
        } else {
          showPopup("error", data.message);
        }
      } catch {
        showPopup("error", "Failed to remove group.");
      }
    });
  });
});
