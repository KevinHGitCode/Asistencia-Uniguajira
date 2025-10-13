docker rm -f $(docker ps -aq)
docker rmi -f $(docker images -q)
docker volume rm $(docker volume ls -q)

docker ps -a      # no debe mostrar contenedores
docker images     # no debe mostrar imágenes
docker volume ls  # no debe mostrar volúmenes

