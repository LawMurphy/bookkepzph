document.addEventListener("DOMContentLoaded", () => {
  const profileBtn = document.querySelector(".profile-circle");
  const dropdown = document.querySelector(".profile-dropdown");

  // ✅ Toggle Profile Dropdown
  profileBtn.addEventListener("click", () => dropdown.classList.toggle("show"));
  document.addEventListener("click", (e) => {
    if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove("show");
    }
  });

  // ✅ Real-Time Date and Time (Philippine Time)
  const dateEl = document.getElementById("current-date");
  const timeEl = document.getElementById("current-time");

  function updateDateTime() {
    const now = new Date().toLocaleString("en-US", { timeZone: "Asia/Manila" });
    const dateObj = new Date(now);

    const dateStr = dateObj.toLocaleDateString("en-PH", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric"
    });

    const timeStr = dateObj.toLocaleTimeString("en-PH", {
      hour12: true,
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit"
    });

    dateEl.textContent = dateStr + " |";
    timeEl.textContent = " " + timeStr;
  }

  updateDateTime();
  setInterval(updateDateTime, 1000);
});
