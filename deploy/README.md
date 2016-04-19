# Magento by Maestrano
This version of Magento is customized to provide Single Sing-On and Connec!™ data sharing. By default, these options are not enabled so an instance of the application can be launched in a Docker container and be run as-is.
More information on [Maestrano SSO](https://maestrano.com) and [Connec!™ data sharing](https://maestrano.com/connec)

## Build Docker container with default Magento installation
`docker build -t maestrano/magento:latest .`

## Run Magento default version (without SSO and Connec!™ data sharing)
### Start the container
This requires having Docker installed, see: https://docs.docker.com/engine/installation
```bash
sudo docker run -it maestrano/magento:latest

# This will start the container and display logs
...
PLAY RECAP *********************************************************************
localhost                  : ok=21   changed=10   unreachable=0    failed=0   

 * Starting MySQL database server mysqld                                                                                                                                                                [ OK ] 
 * Starting web server apache2                                                                                                                                                                                  * 
root@3bac5e153f12:/etc/ansible
```

### Find the container IP address
The local IP of the container can be retrieved using the command `ifconfig` from the previously launched
```bash
root@3bac5e153f12:/etc/ansible# ifconfig
eth0      Link encap:Ethernet  HWaddr 02:42:ac:11:00:02  
          inet addr:172.17.0.2  Bcast:0.0.0.0  Mask:255.255.0.0
          inet6 addr: fe80::42:acff:fe11:2/64 Scope:Link
...
```
The Magento UI can then be accessed from http://172.17.0.2

### Find the mounted magento directory on the host machine
To do so, run the following command on your host machine and set the correct container name or ID (eg: 3bac5e153f12)
```
docker inspect --format '{{ range .Mounts }}{{ if eq .Destination "/var/lib/magento" }}{{ .Source }}{{ end }}{{ end }}' 3bac5e153f12
> /var/lib/docker/volumes/bd6de63efd656527b03d8c8025be817c48cb35ff61009d0c7a912dbd1d6e4b2f/_data
```

This directory contains the Magento files and are modifiable on your local machine
```
ls -la /var/lib/docker/volumes/bd6de63efd656527b03d8c8025be817c48cb35ff61009d0c7a912dbd1d6e4b2f/_data
total 12
drwxr-xr-x  3 root     root     4096 Mar  9 13:52 .
drwxr-xr-x  3 root     root     4096 Mar  9 13:52 ..
drwxr-xr-x 17 www-data www-data 4096 Mar  6 14:03 webapp
```

## Activate Magento customisation on start (SSO and Connec!™ data sharing)
This is achieved by specifying Maestrano environment variables

```bash
sudo docker run -it \
  -e "MNO_SSO_ENABLED=true" \
  -e "MNO_CONNEC_ENABLED=true" \
  -e "MNO_MAESTRANO_ENVIRONMENT=local" \
  -e "MNO_SERVER_HOSTNAME=magento.app.dev.maestrano.io" \
  -e "MNO_API_KEY=e876260b50146136ec393b662edc6d91e453a0dbae1facad335b33fb763ead99" \
  -e "MNO_API_SECRET=9309cffc-2cb2-4423-92ea-e1ff64894241" \
  -e "MNO_APPLICATION_VERSION=mno-develop" \
  -e "MNO_POWER_UNITS=4" \
  --add-host application.maestrano.io:172.17.42.1 \
  --add-host connec.maestrano.io:172.17.42.1 \
  maestrano/magento:latest
 ```

## Docker Hub
The image can be pulled down from [Docker Hub](https://registry.hub.docker.com/u/maestrano/magento/)
**maestrano/magento:stable**: Production version

**maestrano/magento:latest**: Develomment version
