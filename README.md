# 🛠️ Enshrouded Server Control Panel

**Panel de administración avanzado** para servidores dedicados del juego **Enshrouded**, desarrollado por **Azzlaer** junto a **ChatGPT (OpenAI)** para la comunidad de **[LatinBattle.com](https://latinbattle.com)**.  
Diseñado para Windows, totalmente funcional con XAMPP o WAMP.

---

## 📋 Descripción General

Este panel te permite **administrar tu servidor de Enshrouded** de forma visual y profesional.  
Entre sus principales funciones:

- 🌐 **Gestor FTP integrado** (subir, editar, eliminar y comprimir archivos).
- 📜 **Consola en tiempo real** con botones para iniciar, detener, limpiar y archivar logs.
- 🧠 **Monitor de jugadores online**, detectando sesiones activas mediante el log del servidor.
- 💾 **Sistema de backups automáticos y manuales** con control de versiones.
- 📈 **Dashboard dinámico** con información del host, disco, RAM, CPU y estado del servidor.
- 🧰 **Herramientas del servidor** para mantenimiento y gestión avanzada.
- ⚙️ **Compatibilidad total con Windows 10/11 y Enshrouded Dedicated Server.**

---

## 🖼️ Capturas de Pantalla

![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto01.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto02.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto03.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto04.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto05.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto06.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto07.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto08.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto09.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto10.png)
![Preview](https://github.com/Azzlaer/Panel_Enshrouded/blob/main/imagenes/foto11.png)


```
📊 Dashboard Principal
🧰 Herramientas de servidor
📜 Consola de logs
👥 Jugadores Online
```

---

## ⚙️ Requisitos del Sistema

- **Sistema Operativo:** Windows 10/11 o Windows Server  
- **Servidor Web:** Apache (XAMPP, WAMP o IIS)  
- **PHP:** Versión 8.0 o superior  
- **Extensiones PHP:**  
  - `zip`
  - `json`
  - `fileinfo`
  - `mbstring`
- **Permisos:** Acceso de lectura/escritura en las carpetas `data/`, `backups/`, y `logs/`.

---

## 🚀 Instalación

1. Clona o descarga este repositorio en tu servidor local:
   ```bash
   git clone https://github.com/tuusuario/enshrouded-panel.git
   ```

2. Copia los archivos dentro del directorio raíz de tu servidor web (por ejemplo, `htdocs/esh`).

3. Configura tu archivo `config.php` con las rutas y parámetros adecuados:
   ```php
   $server_port = 15636; // Puerto del servidor Enshrouded
   $enshrouded_server_path = "D:\EnshroudedServer";
   $backup_directory = "D:\Backups\Enshrouded";
   $server_log_path = "D:\EnshroudedServer\logs\enshrouded.log";
   ```

4. Abre tu navegador y visita:
   ```
   http://localhost/esh/
   ```

5. Inicia sesión con tu cuenta configurada en el sistema.

---

## 🧩 Estructura del Proyecto

```
📁 enshrouded-panel/
│
├── 📂 ajax/
│   ├── backup_server.php
│   ├── clear_log.php
│   ├── archive_log.php
│   ├── read_log_incremental.php
│   ├── generate_system_info.php
│   └── ...
│
├── 📂 data/
│   ├── system_info.json
│   ├── online_state.json
│   ├── online_history.json
│   └── ...
│
├── 📂 pages/
│   ├── dashboard.php
│   ├── server_console.php
│   ├── server_tools.php
│   ├── ftp_manager.php
│   ├── online_users.php
│   └── ...
│
├── 📂 backups/
├── 📂 logs/
├── config.php
├── index.php
└── README.md
```

---

## 💡 Funcionalidades Destacadas

| Sección | Descripción |
|----------|-------------|
| **Dashboard** | Muestra el estado del servidor, recursos del host, y backups recientes. |
| **Consola del servidor** | Permite ver los logs en tiempo real, limpiarlos, archivarlos o pausar su lectura. |
| **Gestor FTP** | Administra archivos del servidor: editar, eliminar, comprimir o descargar. |
| **Usuarios Online** | Detecta jugadores conectados y muestra tiempo activo con alertas automáticas. |
| **Herramientas de Servidor** | Ejecuta backups, limpia versiones antiguas y muestra historial. |

---

## 🧠 Créditos

Desarrollado por **Azzlaer** junto a **ChatGPT (OpenAI)**  
para **[LatinBattle.com](https://latinbattle.com)** ❤️  

Inspirado en la pasión por la comunidad **latina de Enshrouded**.

---

## 📜 Licencia

Este proyecto se distribuye bajo la licencia **MIT**.  
Puedes modificarlo y adaptarlo libremente, manteniendo los créditos originales:

```
Desarrollado por Azzlaer & ChatGPT (OpenAI) para LatinBattle.com
```

---

## 🤝 Contribuir

Si deseas colaborar con mejoras o traducciones:
1. Crea un fork del repositorio.
2. Realiza tus cambios en una nueva rama.
3. Envía un pull request explicando tus aportes.

---

## 🌐 Sitio Oficial

🔗 [https://latinbattle.com](https://latinbattle.com)  
💬 Discord: próximamente...

---

> _"Hecho con ❤️ por Azzlaer & ChatGPT para la comunidad gamer latina."_
