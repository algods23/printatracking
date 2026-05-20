const { app, BrowserWindow, Menu, ipcMain, dialog } = require('electron');
const path = require('path');
const isDev = require('electron-is-dev');
const { spawn } = require('child_process');
const fs = require('fs');

// Keep a global reference of the window object
let mainWindow;
let phpProcess;

const createWindow = () => {
    // Create the browser window
    mainWindow = new BrowserWindow({
        width: 1400,
        height: 900,
        minWidth: 1200,
        minHeight: 800,
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            nodeIntegration: false,
            contextIsolation: true,
            enableRemoteModule: false,
        },
        icon: path.join(__dirname, '../assets/icon.png'),
        show: false,
    });

    // Show window when ready
    mainWindow.once('ready-to-show', () => {
        mainWindow.show();
    });

    // Open DevTools in development
    if (isDev) {
        mainWindow.webContents.openDevTools();
    }

    // Handle window closed
    mainWindow.on('closed', () => {
        mainWindow = null;
    });

    // Load the app
    if (isDev) {
        mainWindow.loadURL('http://localhost:5173');
    } else {
        mainWindow.loadURL(`file://${path.join(__dirname, '../dist/index.html')}`);
    }
};

const startLaravelServer = () => {
    const laravelPath = path.join(__dirname, '../');
    const phpPath = process.platform === 'win32' ? 'php' : 'php';
    
    phpProcess = spawn(phpPath, ['artisan', 'serve', '--host=127.0.0.1', '--port=8000'], {
        cwd: laravelPath,
        stdio: 'pipe',
        detached: false,
    });

    phpProcess.stdout.on('data', (data) => {
        if (isDev) {
            console.log(`Laravel: ${data}`);
        }
    });

    phpProcess.stderr.on('data', (data) => {
        if (isDev) {
            console.error(`Laravel Error: ${data}`);
        }
    });

    // Wait for Laravel to start
    return new Promise((resolve) => {
        setTimeout(() => resolve(), 3000);
    });
};

const stopLaravelServer = () => {
    if (phpProcess) {
        if (process.platform === 'win32') {
            require('child_process').exec(`taskkill /pid ${phpProcess.pid} /T /F`);
        } else {
            phpProcess.kill();
        }
    }
};

// App event listeners
app.on('ready', async () => {
    try {
        // Start Laravel server
        await startLaravelServer();
        
        // Create main window
        createWindow();
        
        // Create menu
        createMenu();
    } catch (error) {
        console.error('Failed to start application:', error);
        app.quit();
    }
});

app.on('window-all-closed', () => {
    // Stop Laravel server
    stopLaravelServer();
    
    // On macOS, applications stay active until explicitly quit
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('activate', () => {
    // On macOS, re-create window when dock icon is clicked
    if (mainWindow === null) {
        createWindow();
    }
});

// Create application menu
const createMenu = () => {
    const template = [
        {
            label: 'File',
            submenu: [
                {
                    label: 'Exit',
                    accelerator: 'CmdOrCtrl+Q',
                    click: () => {
                        stopLaravelServer();
                        app.quit();
                    },
                },
            ],
        },
        {
            label: 'Edit',
            submenu: [
                { role: 'undo' },
                { role: 'redo' },
                { type: 'separator' },
                { role: 'cut' },
                { role: 'copy' },
                { role: 'paste' },
            ],
        },
        {
            label: 'View',
            submenu: [
                { role: 'reload' },
                { role: 'forceReload' },
                { role: 'toggleDevTools' },
                { type: 'separator' },
                { role: 'resetZoom' },
                { role: 'zoomIn' },
                { role: 'zoomOut' },
                { type: 'separator' },
                { role: 'togglefullscreen' },
            ],
        },
        {
            label: 'Help',
            submenu: [
                {
                    label: 'About',
                    click: () => {
                        dialog.showMessageBox(mainWindow, {
                            type: 'info',
                            title: 'About Printa Signages',
                            message: 'Printa Signages & Stickers Management System',
                            detail: 'Version 1.0.0\n\nA professional desktop application for managing signages and stickers production.',
                        });
                    },
                },
            ],
        },
    ];

    const menu = Menu.buildFromTemplate(template);
    Menu.setApplicationMenu(menu);
};

// IPC handlers
ipcMain.handle('get-app-version', () => {
    return app.getVersion();
});

ipcMain.handle('get-app-name', () => {
    return 'Printa Signages';
});

// Handle any uncaught exceptions
process.on('uncaughtException', (error) => {
    console.error('Uncaught Exception:', error);
    stopLaravelServer();
    app.quit();
});
