## Manejo de la base de datos en docker

1. Bajar la imagen
   
   ```bash
   docker pull mysql
   ```
   
   Nota: es necesario tener mysql-server en la maquina linux
   
   `sudo apt install mysql-server`

2. Crear el contenedor con las credenciales
   
   ```bash
   docker run --name database -e MYSQL_ROOT_PASSWORD=123123 -d mysql
   ```

3. Obtener la ip del contenedor
   
   ```bash
   docker inspect database | grep IPAddress
   ```

4. Establecer la conexion
   
   ```bash
   mysql -u root -h <IPAddress> -p123123
   ```
   
   Nota: de esta forma se pueden crear varios contenedores cada uno con ip distinta, haciendo que sea similar a tener una o varias conexiones remotas
   
   Para hacer la conexion en laravel se hace de esta manera
   
   ```javascript
   DB_CONNECTION=mysql
   DB_HOST=<IPAddress>
   DB_PORT=3306
   DB_DATABASE=asistencia_db
   DB_USERNAME=root
   DB_PASSWORD=123123
   ```
