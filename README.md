# 📚 Asistencias Uniguajira

Sistema web para la gestión y control de asistencias en la Universidad de La Guajira.

[🔗 Accede a la aplicación](https://asistencia-uniguajira.onrender.com/)

---

## 🚀 Características principales

- Registro y control de asistencias para estudiantes y docentes.
- Panel de administración intuitivo.
- Reportes y estadísticas en tiempo real.
- Gestión de usuarios y roles.
- Notificaciones automáticas.

## 🛠️ Tecnologías utilizadas

- **Backend:** Laravel
- **Frontend:** Blade, Tailwind
- **Base de datos:** MySQL
- **Despliegue:** Render

## ⚡ Instalación local

1. Clona el repositorio:
   ```bash
   git clone https://github.com/tu-usuario/Asistencia-Uniguajira.git
   cd Asistencia-Uniguajira
   ```

2. Instala dependencias:
   ```bash
   composer install
   npm install
   ```

3. Copia el archivo de entorno y configura tus variables:
   ```bash
   cp .env.example .env
   ```

4. Genera la clave de la aplicación:
   ```bash
   php artisan key:generate
   ```

5. Configura la base de datos en el archivo `.env`.

6. Ejecuta migraciones y seeders:
   ```bash
   php artisan migrate --seed
   ```

7. Inicia el servidor de desarrollo:
   ```bash
   php artisan serve
   ```

## 📝 Contribuciones

¡Las contribuciones son bienvenidas! Por favor, abre un issue o un pull request para sugerencias o mejoras.

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

---

Desarrollado con ❤️ por el equipo de Uniguajira.
