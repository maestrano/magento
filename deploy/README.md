# Magento by Maestrano
This version of Dolibarr is customized to provide Single Sing-On and Connec!™ data sharing. By default, these options are not enabled so an instance of the application can be launched in a Docker container and be run as-is.
More information on [Maestrano SSO](https://maestrano.com) and [Connec!™ data sharing](https://maestrano.com/connec)

## Build Docker container with default Magento installation
`sudo docker build .`

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
  --add-host application.maestrano.io:172.17.42.1 \
  --add-host connec.maestrano.io:172.17.42.1 \
  maestrano/magento:latest
 ```
