#!/bin/bash
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
NC='\033[0m'
echo -e "${GREEN}Updating to Centipede Pi version${NC}"

echo -e "${YELLOW}Replacing HTML files${NC}"
if [ "$(pwd)" == "/var/www" ]; then
  echo "You seem to have already installed them..."
else
  sudo rm -r /var/www/html
  sudo mkdir /var/www/html
  sudo chown pi:pi /var/www/html
  cp -r html/* /var/www/html/
fi
echo ...done.

echo -e "${YELLOW}Updating libraries and installing Tor (and Vim)${NC}"
sudo apt-get update
sudo apt-get -y install tor vim
echo ...done.

echo -e "${YELLOW}Configuring Tor${NC}"
sudo mkdir -p /etc/tor/hidden_service
sudo chown www-data:www-data /etc/tor/hidden_service
if [ "$(grep '^HiddenServiceDir /etc/tor/hidden_service/$' /etc/tor/torrc)" == "" ]; then
  sed -e '0,/#HiddenServiceDir/ s/#HiddenServiceDir/HiddenServiceDir\ \/etc\/tor\/hidden_service\/\nHiddenServicePort\ 80\ 127.0.0.1:80\nHiddenServicePort\ 8000\ 127.0.0.1:8000\n\n#HiddenServiceDir/' /etc/tor/torrc > /tmp/torrc
  sudo chown root:root /tmp/torrc
  sudo mv /tmp/torrc /etc/tor/torrc
else
  echo Looks like Tor was already configured.
fi
echo ...done.

echo -e "${YELLOW}Creating ~/.ssh${NC}"
mkdir -p ~/.ssh
touch ~/.ssh/authorized_keys
echo ...done.

echo -e "${YELLOW}Updating sudoers${NC}"
if [ "$(sudo grep chpasswd /etc/sudoers.d/sudo_centipede)" == "" ]; then
  echo "www-data ALL=(ALL) NOPASSWD: /usr/sbin/update-rc.d ssh defaults, /usr/sbin/update-rc.d -f ssh remove, /bin/cp, /usr/sbin/chpasswd" > /tmp/sudo_centipede
  sudo mv /tmp/sudo_centipede /etc/sudoers.d/sudo_centipede
  sudo chown root:root /etc/sudoers.d/sudo_centipede
  sudo chmod 440 /etc/sudoers.d/sudo_centipede
fi
echo ...done.


echo -e "${GREEN}Welcome to the Centipede Pi version!!! Rebooting to finalize install.${NC}"
sudo shutdown -r now
