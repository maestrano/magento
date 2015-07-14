FROM maestrano/docker-lamp:latest
MAINTAINER Maestrano <it@maestrano.com>

RUN apt-get -y update && apt-get -y upgrade

# Add ansible configuration
ADD ansible /etc/ansible
WORKDIR /etc/ansible

# Install Dolibarr
RUN ansible-playbook -i hosts site.yml

# Map Magento mutable volumes
VOLUME ["/var/lib/magento"]
