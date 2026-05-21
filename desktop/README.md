# Windows Offline Desktop Build

This folder contains the production desktop packaging layer for the Laravel app.

Build flow:

```powershell
.\scripts\build-installer.ps1
```

Output:

```text
dist\Printa Signages Setup <version>.exe
```

Runtime behavior:

- Copies the Laravel app to the user's app data folder on first launch.
- Starts embedded MariaDB on port `3307`.
- Starts Apache on port `8000` and binds to `0.0.0.0` for LAN access.
- Runs Laravel migrations and seeds on first install.
- Shows the LAN URL inside the app, for example `http://192.168.x.x:8000`.
- Creates database backups in `Documents\Printa Signages\backups` when the app closes.
- Stops Apache, PHP fallback server, and MariaDB when the desktop app exits.

The installer does not require Laragon, XAMPP, Composer, Node.js, or PHP on the client laptop. Those tools are only needed on the developer machine that builds the installer.
