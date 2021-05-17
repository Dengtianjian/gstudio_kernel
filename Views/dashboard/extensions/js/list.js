const extensionStatusEls = {};

function checkAndAddStateEl(extensionId) {
  if (!extensionStatusEls[extensionId]) {
    extensionStatusEls[extensionId] = document.querySelector(
      "li[data-extension='" + extensionId + "'] .operation-status"
    );
  }
  return extensionStatusEls[extensionId];
}
function updateStateEl(extesionId, textContent, display = "none") {
  let statusEl = checkAndAddStateEl(extesionId);
  statusEl.innerText = textContent;
  statusEl.style.display = display;
}
function installExtension(extensionId) {
  updateStateEl(extensionId, GLANG["kernel"]["extensionInstalling"], "block");
  CDZXHTTP.post("_extension/install", {
    extension_id: extensionId,
  })
    .then((res) => {
      CMessage(GLANG["kernel"]["extensionInstalledSuccessfully"]);
      updateStateEl(
        extensionId,
        GLANG["kernel"]["extensionUpdatingData"],
        "block"
      );
      setTimeout(() => {
        location.reload();
      }, 500);
    })
    .catch((err) => {
      updateStateEl(extensionId, "", "none");
      CMessage(err.message);
    });
}

function upgradeExtension(extensionId) {
  updateStateEl(extensionId, GLANG["kernel"]["extensionUpgrading"], "block");
  CDZXHTTP.post("_extension/upgrade", {
    extension_id: extensionId,
  })
    .then((res) => {
      CMessage(GLANG["kernel"]["extensionUpdateSuccessed"]);
      updateStateEl(
        extensionId,
        GLANG["kernel"]["extensionUpdatingData"],
        "block"
      );
      setTimeout(() => {
        location.reload();
      }, 500);
    })
    .catch((err) => {
      updateStateEl(extensionId, "", "none");
      CMessage(err.message);
    });
}

function uninstallExtension(extensionId) {
  updateStateEl(extensionId, GLANG["kernel"]["extensionUninstalling"], "block");
  CDZXHTTP.post("_extension/uninstall", {
    extension_id: extensionId,
  })
    .then((res) => {
      CMessage(GLANG["kernel"]["extensionUninstallComplete"]);
      updateStateEl(
        extensionId,
        GLANG["kernel"]["extensionUpdatingData"],
        "block"
      );
      setTimeout(() => {
        location.reload();
      }, 500);
    })
    .catch((err) => {
      updateStateEl(extensionId, "", "none");
      CMessage(err.message);
    });
}

function openOrCloseExtension(extensionId, enabled) {
  updateStateEl(
    extensionId,
    enabled == 1
      ? GLANG["kernel"]["extensionClosing"]
      : GLANG["kernel"]["extensionOpening"],
    "block"
  );
  CDZXHTTP.post("_extension/openClose", {
    extension_id: extensionId,
    enabled: enabled == 1 ? 0 : 1,
  })
    .then((res) => {
      CMessage(
        enabled == 1
          ? GLANG["kernel"]["extensionClosed"]
          : GLANG["kernel"]["extensionTurnedOn"]
      );
      updateStateEl(
        extensionId,
        GLANG["kernel"]["extensionUpdatingData"],
        "block"
      );
      setTimeout(() => {
        location.reload();
      }, 500);
    })
    .catch((err) => {
      updateStateEl(extensionId, "", "none");
      CMessage(err.message);
    });
}
