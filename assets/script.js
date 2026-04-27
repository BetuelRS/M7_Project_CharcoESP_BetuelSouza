// assets/script.js
// 1. Centralized Threshold System
const thresholds = {
  temperatura: { min: 0, max: 30, unit: "°C", label: "Temperatura" },
  humidade: { min: 20, max: 80, unit: "%", label: "Humidade" },
  luminosidade: { min: 100, max: 900, unit: "lux", label: "Luminosidade" },
  qualidade_do_ar: { min: 0, max: 40, unit: "µg/m3", label: "Partículas" },
  nivel_agua: { min: 15, max: 60, unit: "cm", label: "Nível da Água" },
  // Extend here...
};

// Controla parâmetros ignorados (timestamp de expiração)
let ignoredAlertsExpiry = {};
const IGNORE_DURATION_MS = 5 * 60 * 1000; // 5 minutos

// Utility functions for DOM/alert
function cardFor(labelContains) {
  const cards = document.querySelectorAll(".dashboard-card");
  for (let card of cards) {
    const h3 = card.querySelector("h3");
    if (
      h3 &&
      h3.textContent.toLowerCase().includes(labelContains.toLowerCase())
    )
      return card;
  }
  return null;
}

function showAlert(msg, color = "#ffc107", affectedKeys = []) {
  let alertBox = document.getElementById("dashboard-alerts");
  if (!alertBox) {
    alertBox = document.createElement("div");
    alertBox.id = "dashboard-alerts";
    alertBox.style.position = "fixed";
    alertBox.style.top = "10px";
    alertBox.style.right = "10px";
    alertBox.style.zIndex = 9999;
    alertBox.style.padding = "1rem 2rem";
    alertBox.style.borderRadius = "8px";
    alertBox.style.fontWeight = "bold";
    alertBox.style.boxShadow = "0 6px 18px rgba(0,0,0,.15)";
    document.body.appendChild(alertBox);
  }
  alertBox.innerHTML = msg;
  alertBox.style.backgroundColor = color;
  alertBox.style.color = "#000";
  alertBox.style.display = "block";
  alertBox.style.cursor = "pointer";

  // Armazena os parâmetros afetados no próprio elemento
  alertBox.dataset.alertKeys = JSON.stringify(affectedKeys);

  // Ao clicar, IGNORA os alertas para os parâmetros listados e desaparece
  alertBox.onclick = function () {
    const keys = JSON.parse(alertBox.dataset.alertKeys || "[]");
    const now = Date.now();
    keys.forEach(key => {
      ignoredAlertsExpiry[key] = now + IGNORE_DURATION_MS;
    });
    hideAlert();
    // Reavalia o dashboard para que os alertas ignorados não reappareçam imediatamente
    monitor();
  };
}

function hideAlert() {
  const alertBox = document.getElementById("dashboard-alerts");
  if (alertBox) alertBox.style.display = "none";
}

function parseNumber(str) {
  if (!str) return null;
  return parseFloat(str.replace(",", ".").replace(/[^\d\.\-]/g, ""));
}

function mapLabelToKey(label) {
  let key = label.trim().toLowerCase();
  if (key.includes("temperatura")) return "temperatura";
  if (key.includes("humidade")) return "humidade";
  if (key.includes("nível") || key.includes("nível da água"))
    return "nivel_agua";
  if (key.includes("lumin") || key.includes("lux")) return "luminosidade";
  if (key.includes("partículas") || key.includes("qualidade"))
    return "qualidade_do_ar";
  return key;
}

function getDashboardReadings() {
  const results = [];
  document.querySelectorAll(".dashboard-card").forEach((card) => {
    const labelEl = card.querySelector("h3");
    const valueEl = card.querySelector(".card-value");
    if (labelEl && valueEl) {
      const label = labelEl.textContent;
      const rawValue = valueEl.textContent;
      const paramKey = mapLabelToKey(label);
      if (thresholds[paramKey]) {
        results.push({
          key: paramKey,
          value: parseNumber(rawValue),
          unit: thresholds[paramKey].unit,
          card,
          label,
        });
      }
    }
  });
  return results;
}

function monitor() {
  const readings = getDashboardReadings();
  let globalAlertsMsgs = [];
  let globalAlertKeys = [];

  readings.forEach((r) => {
    const { min, max, label, unit } = thresholds[r.key];
    let status = "ok";
    let alertMsg = "";
    let color = "#ccffcc";

    if (r.value > max) {
      status = "high";
      color = "#ffcccc";
      alertMsg = `⚠️ ALERTA: ${label} acima do máximo (${r.value}${unit} > ${max}${unit})`;
    } else if (r.value < min) {
      status = "low";
      color = "#ffd9b3";
      alertMsg = `⚠️ ALERTA: ${label} abaixo do mínimo (${r.value}${unit} < ${min}${unit})`;
      if (r.key === "humidade" && r.value < 20) {
        alertMsg = "⚠️ WARNING: Risco de seca";
        color = "#ffe082";
      }
      if (r.key === "nivel_agua") {
        alertMsg = `⚠️ ALERTA: Nível da água crítico (${r.value}${unit})`;
        color = "#e57373";
      }
    }

    r.card.style.backgroundColor = color;
    r.card.style.border =
      status === "ok" ? "2px solid #2e7d32" : "2.5px solid #ff5722";

    // Verifica se este parâmetro está sendo ignorado (ainda dentro do tempo)
    const isIgnored = ignoredAlertsExpiry[r.key] && Date.now() < ignoredAlertsExpiry[r.key];
    if (status !== "ok" && !isIgnored) {
      globalAlertsMsgs.push(alertMsg);
      globalAlertKeys.push(r.key);
    }
  });

  if (globalAlertsMsgs.length) {
    showAlert(globalAlertsMsgs.join("<br>"), "#ffcccb", globalAlertKeys);
  } else {
    hideAlert();
  }
}

document.addEventListener("DOMContentLoaded", function () {
  monitor();
  // Se o dashboard atualizar em tempo real, chame monitor() após cada atualização
  // setInterval(monitor, 15000); // opcional: verificação periódica
  document.addEventListener("visibilitychange", () => {
    if (!document.hidden) monitor();
  });
});