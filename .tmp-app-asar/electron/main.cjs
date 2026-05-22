const { app, BrowserWindow, Menu, dialog, ipcMain, shell, clipboard } = require('electron');
const { spawn, execFile } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');
const net = require('net');

const APP_NAME = 'Printa Signages';
const HTTP_PORT = 8000;

let mainWindow;
let phpProcess;
let resolvedAppUrl = `http://127.0.0.1:${HTTP_PORT}`;
let resolvedLanUrl = null;
let runtimePaths;

const isDev = !app.isPackaged;

function appRoot() {
  return isDev ? path.resolve(__dirname, '..') : process.resourcesPath;
}

function getRuntimePaths() {
  const root = appRoot();
  const dataRoot = path.join(app.getPath('userData'), 'runtime');
  const laravelSource = isDev ? root : path.join(root, 'laravel-app');

  return {
    root,
    dataRoot,
    appData: app.getPath('userData'),
    laravelSource,
    laravelApp: isDev ? root : path.join(dataRoot, 'laravel-app'),
    backups: path.join(app.getPath('documents'), APP_NAME, 'backups'),
    configFile: path.join(app.getPath('userData'), 'config.json'),
    php: isDev ? 'php' : path.join(root, 'runtime', 'php', 'php.exe'),
    sqliteDatabase: path.join(dataRoot, 'database', 'database.sqlite'),
  };
}

function ensureDirectory(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function syncLaravelApp(source, target) {
  ensureDirectory(target);
  fs.cpSync(source, target, {
    recursive: true,
    force: true,
    filter: (file) => {
      const base = path.basename(file);
      const relative = path.relative(source, file).replace(/\\/g, '/');

      return ![
        'node_modules',
        '.git',
        'desktop',
        'electron',
        'tests',
        'dist',
      ].includes(base) && ![
        '.env',
        'public/storage',
      ].includes(relative);
    },
  });

  ensureLaravelStorage(target);
}

function ensureLaravelStorage(target) {
  [
    'storage/app/public',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
  ].forEach((dir) => ensureDirectory(path.join(target, dir)));
}

function writeJsonConfig() {
  if (fs.existsSync(runtimePaths.configFile)) {
    return;
  }

  fs.writeFileSync(
    runtimePaths.configFile,
    JSON.stringify({
      appName: APP_NAME,
      httpPort: HTTP_PORT,
      database: runtimePaths.sqliteDatabase,
      installedAt: new Date().toISOString(),
      backupFolder: runtimePaths.backups,
    }, null, 2),
  );
}

function ensureLaravelEnv() {
  const envPath = path.join(runtimePaths.laravelApp, '.env');
  const envExample = path.join(runtimePaths.laravelApp, '.env.desktop');

  if (!fs.existsSync(envPath)) {
    if (fs.existsSync(envExample)) {
      fs.copyFileSync(envExample, envPath);
    } else {
      fs.writeFileSync(envPath, '');
    }
  }

  const env = fs.readFileSync(envPath, 'utf8');
  const replacements = {
    APP_NAME: APP_NAME,
    APP_ENV: 'production',
    APP_DEBUG: 'false',
    APP_URL: resolvedAppUrl,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: runtimePaths.sqliteDatabase.replace(/\\/g, '/'),
    DB_FOREIGN_KEYS: 'true',
    SESSION_DRIVER: 'database',
    CACHE_STORE: 'database',
    QUEUE_CONNECTION: 'database',
    FILESYSTEM_DISK: 'local',
  };

  let next = env;
  for (const [key, value] of Object.entries(replacements)) {
    const line = `${key}=${quoteEnv(value)}`;
    const pattern = new RegExp(`^${key}=.*$`, 'm');
    next = pattern.test(next) ? next.replace(pattern, line) : `${next.trimEnd()}\n${line}\n`;
  }

  fs.writeFileSync(envPath, next);
}

function quoteEnv(value) {
  return /\s/.test(value) ? `"${value}"` : value;
}

function getLanIp() {
  const interfaces = os.networkInterfaces();

  for (const addresses of Object.values(interfaces)) {
    for (const address of addresses || []) {
      if (address.family === 'IPv4' && !address.internal) {
        return address.address;
      }
    }
  }

  return '127.0.0.1';
}

function waitForPort(port, host = '127.0.0.1', timeoutMs = 30000) {
  const start = Date.now();

  return new Promise((resolve, reject) => {
    const attempt = () => {
      const socket = net.createConnection(port, host);
      socket.once('connect', () => {
        socket.destroy();
        resolve();
      });
      socket.once('error', () => {
        socket.destroy();
        if (Date.now() - start > timeoutMs) {
          reject(new Error(`Timed out waiting for ${host}:${port}`));
          return;
        }
        setTimeout(attempt, 500);
      });
    };

    attempt();
  });
}

function execFilePromise(file, args, options = {}) {
  return new Promise((resolve, reject) => {
    execFile(file, args, options, (error, stdout, stderr) => {
      if (error) {
        error.stdout = stdout;
        error.stderr = stderr;
        reject(error);
        return;
      }
      resolve({ stdout, stderr });
    });
  });
}

async function prepareRuntime() {
  runtimePaths = getRuntimePaths();
  const lanIp = getLanIp();
  resolvedLanUrl = lanIp === '127.0.0.1' ? null : `http://${lanIp}:${HTTP_PORT}`;

  ensureDirectory(runtimePaths.dataRoot);
  ensureDirectory(runtimePaths.backups);
  ensureDirectory(path.dirname(runtimePaths.sqliteDatabase));
  writeJsonConfig();

  if (!isDev) {
    syncLaravelApp(runtimePaths.laravelSource, runtimePaths.laravelApp);
    if (!fs.existsSync(runtimePaths.sqliteDatabase)) {
      fs.writeFileSync(runtimePaths.sqliteDatabase, '');
    }
    ensureLaravelEnv();
  }
}

async function runArtisan(args) {
  await execFilePromise(runtimePaths.php, ['artisan', ...args], {
    cwd: runtimePaths.laravelApp,
    windowsHide: true,
    env: {
      ...process.env,
      APP_ENV: 'production',
    },
  });
}

async function migrateAndSeedFirstRun() {
  if (isDev) {
    return;
  }

  const marker = path.join(runtimePaths.dataRoot, '.database-ready');

  await runArtisan(['storage:link']);
  await runArtisan(['config:clear']);

  if (!fs.existsSync(marker)) {
    await runArtisan(['key:generate', '--force']);
    await runArtisan(['migrate', '--force']);
    await runArtisan(['db:seed', '--force']);
    fs.writeFileSync(marker, new Date().toISOString());
    return;
  }

  await runArtisan(['migrate', '--force']);
}

function writeApacheConfig() {
  const laravelPublic = path.join(runtimePaths.laravelApp, 'public').replace(/\\/g, '/');
  const phpDir = path.dirname(runtimePaths.php).replace(/\\/g, '/');
  const apacheRoot = path.dirname(path.dirname(runtimePaths.apache)).replace(/\\/g, '/');
  const serverRoot = apacheRoot.replace(/\\/g, '/');

  const config = `
ServerRoot "${serverRoot}"
Listen 0.0.0.0:${HTTP_PORT}
ServerName localhost:${HTTP_PORT}
DocumentRoot "${laravelPublic}"

LoadModule access_compat_module modules/mod_access_compat.so
LoadModule actions_module modules/mod_actions.so
LoadModule alias_module modules/mod_alias.so
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule dir_module modules/mod_dir.so
LoadModule env_module modules/mod_env.so
LoadModule headers_module modules/mod_headers.so
LoadModule mime_module modules/mod_mime.so
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule setenvif_module modules/mod_setenvif.so

LoadModule php_module "${phpDir}/php8apache2_4.dll"
PHPIniDir "${phpDir}"
AddHandler application/x-httpd-php .php

<Directory "${laravelPublic}">
    AllowOverride All
    Require all granted
    DirectoryIndex index.php index.html
</Directory>

ErrorLog "${runtimePaths.dataRoot.replace(/\\/g, '/')}/apache-error.log"
PidFile "${runtimePaths.dataRoot.replace(/\\/g, '/')}/apache.pid"
`.trim();

  fs.writeFileSync(runtimePaths.apacheConf, config);
}

async function startWebServer() {
  if (!isDev && !fs.existsSync(runtimePaths.php)) {
    throw new Error(`Portable PHP was not found at ${runtimePaths.php}`);
  }

  phpProcess = spawn(runtimePaths.php, [
    '-S',
    `0.0.0.0:${HTTP_PORT}`,
    '-t',
    'public',
    'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php',
  ], {
    cwd: runtimePaths.laravelApp,
    windowsHide: true,
    stdio: isDev ? 'inherit' : 'ignore',
  });
  await waitForPort(HTTP_PORT);
}

async function createBackup() {
  if (!runtimePaths || !fs.existsSync(runtimePaths.sqliteDatabase)) {
    return;
  }

  const stamp = new Date().toISOString().replace(/[:.]/g, '-');
  const backupPath = path.join(runtimePaths.backups, `printa-signages-${stamp}.sqlite`);

  await fs.promises.copyFile(runtimePaths.sqliteDatabase, backupPath).catch(() => {});
}

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1366,
    height: 820,
    minWidth: 1100,
    minHeight: 720,
    show: false,
    autoHideMenuBar: true,
    icon: path.join(appRoot(), 'assets', 'icon.ico'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.cjs'),
      nodeIntegration: false,
      contextIsolation: true,
    },
  });

  mainWindow.once('ready-to-show', () => mainWindow.show());
  mainWindow.loadURL(resolvedAppUrl);
}

function createMenu() {
  const template = [
    {
      label: 'File',
      submenu: [
        { label: 'Open Backup Folder', click: () => shell.openPath(runtimePaths.backups) },
        {
          label: resolvedLanUrl ? `Copy Wi-Fi URL: ${resolvedLanUrl}` : 'Wi-Fi URL unavailable',
          enabled: Boolean(resolvedLanUrl),
          click: () => clipboard.writeText(resolvedLanUrl),
        },
        { type: 'separator' },
        { label: 'Exit', accelerator: 'Alt+F4', click: () => app.quit() },
      ],
    },
    {
      label: 'View',
      submenu: [
        { role: 'reload' },
        { role: 'togglefullscreen' },
      ],
    },
    {
      label: 'Application',
      submenu: [
        {
          label: `Local URL: ${resolvedAppUrl}`,
          click: () => {
            clipboard.writeText(resolvedAppUrl);
          },
        },
        {
          label: 'Show Local URL',
          click: () => dialog.showMessageBox(mainWindow, {
            type: 'info',
            title: 'Local Access',
            message: resolvedLanUrl ? `This device: ${resolvedAppUrl}\nWi-Fi: ${resolvedLanUrl}` : resolvedAppUrl,
            detail: 'This desktop build runs locally and does not require Laragon, XAMPP, Composer, Node.js, or an internet connection on the client device.',
          }),
        },
      ],
    },
  ];

  Menu.setApplicationMenu(Menu.buildFromTemplate(template));
}

async function stopProcess(child) {
  if (!child || child.killed) {
    return;
  }

  if (process.platform === 'win32') {
    await execFilePromise('taskkill', ['/pid', String(child.pid), '/T', '/F']).catch(() => {});
    return;
  }

  child.kill();
}

async function shutdown() {
  await createBackup();
  await stopProcess(phpProcess);
}

app.whenReady().then(async () => {
  try {
    await prepareRuntime();
    await migrateAndSeedFirstRun();
    await startWebServer();
    createWindow();
    createMenu();
  } catch (error) {
    dialog.showErrorBox(APP_NAME, `${error.message}\n\nThe application will close.`);
    app.quit();
  }
});

app.on('before-quit', async (event) => {
  if (app.__isShuttingDown) {
    return;
  }

  event.preventDefault();
  app.__isShuttingDown = true;
  await shutdown();
  app.quit();
});

app.on('window-all-closed', () => app.quit());

ipcMain.handle('desktop:getNetworkInfo', () => ({
  appUrl: resolvedAppUrl,
  lanUrl: resolvedLanUrl,
  backupFolder: runtimePaths.backups,
  database: runtimePaths.sqliteDatabase,
}));
