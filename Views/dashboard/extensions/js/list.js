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
  updateStateEl(extensionId, "安装中，请稍后", "block");
  CDZXHTTP.post("_extension/install", {
    extension_id: extensionId,
  })
    .then((res) => {
      CMessage("安装成功");
      updateStateEl(extensionId, "更新数据中，请稍后", "block");
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
  updateStateEl(extensionId, "更新中，请稍后", "block");
  CDZXHTTP.post("_extension/upgrade", {
    extension_id: extensionId,
  })
    .then((res) => {
      CMessage("更新成功");
      updateStateEl(extensionId, "更新数据中，请稍后", "block");
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
  updateStateEl(extensionId, "卸载中，请稍后", "block");
  CDZXHTTP.post("_extension/uninstall", {
    extension_id: extensionId,
  })
    .then((res) => {
      CMessage("卸载完成");
      updateStateEl(extensionId, "更新数据中，请稍后", "block");
      setTimeout(() => {
        location.reload();
      }, 500);
    })
    .catch((err) => {
      updateStateEl(extensionId, "", "none");
      CMessage(err.message);
    });
}
