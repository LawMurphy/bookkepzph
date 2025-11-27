function showNotification(message, type = "info", position = "center") {
  const notif = document.createElement("div");
  notif.className = `notification ${type} ${position}`;
  notif.innerHTML = message;
  document.body.appendChild(notif);

  // Animation in/out
  setTimeout(() => {
    notif.classList.add("show");
  }, 10);
  setTimeout(() => {
    notif.classList.remove("show");
    setTimeout(() => notif.remove(), 300);
  }, 3000);
}

const notifStyle = document.createElement("style");
notifStyle.innerHTML = `
.custom-notif {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0.9);
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 1rem 2rem;
  border-radius: 16px;
  font-size: 1.05rem;
  font-weight: 600;
  letter-spacing: 0.3px;
  color: #333;
  text-align: left;
  opacity: 0;
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 8px 35px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(10px);
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 9999;
  max-width: 90%;
  width: fit-content;
  min-width: 300px;
  pointer-events: none;
  border: 1px solid rgba(0,0,0,0.05);
}

.custom-notif.show {
  opacity: 1;
  transform: translate(-50%, -50%) scale(1);
}

.custom-notif.success { color: #2ecc71; }
.custom-notif.error { color: #e74c3c; }
.custom-notif.warning { color: #f39c12; }

@keyframes notifPop {
  0% { transform: scale(0.6); opacity: 0; }
  80% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(1); }
}
.custom-notif i {
  font-size: 1.4rem;
  animation: notifPop 0.3s ease;
}
`;
document.head.appendChild(notifStyle);
