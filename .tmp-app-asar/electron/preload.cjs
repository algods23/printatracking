const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('desktopApp', {
  getNetworkInfo: () => ipcRenderer.invoke('desktop:getNetworkInfo'),
});
