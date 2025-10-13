## Docker Deploy

Ejecutar los siguientes comandos en una terminal linux como wsl2

```bash
# navegar a la carpeta del proyeco 'asistencia-uniguajira'
docker build -t attendance-app .

# para ver las imagenes creadas usa
docker images

# para crear el contenedor usa el siguiente comando
docker run -d -p 8080:80 --name asistencia-container attendance-app

# verifica los contenedores activos
docker ps

# Obten la ip de tu maquina virtual y copia la primera IP que aparece
hostname -I
```

**En tu navegador de Windows, visita:**

http://<IP>:8080

**Recrear el contenedor para actualizarlo**

```bash
# eliminar el contenedor si ya existe
docker rm -f asistencia-container

# volver a crear la imagen para actualizarlo
docker build -t attendance-app .

# para crear el contenedor usa el siguiente comando
docker run -d -p 8080:80 --name asistencia-container attendance-app
```


**Eliminar imagenes huerfanas**
```bash
sudo docker image prune -f
```



**Cuando se tiene un docker-compose.yml**
```bash
# eliminar lo construido
docker compose down

# construir lo definido en el archivo
docker compose up -d --build
```
