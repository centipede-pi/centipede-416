# centipede-416
A custom image for the Centipede 416 e-stim device

First, make sure you make a backup of your current SD card and/or have a copy of the stock image handy - this software is currently in
a public preview state and not guaranteed to work (especially with Apple devices).

In order to install we assume you have ssh access to your device. Log in and run:

 * git clone https://github.com/centipede-pi/centipede-416
 * cd centipede-416
 * chmod +x install.sh
 * ./install.sh
  
Your device will update and then reboot to the new image. Most of the changes besides the new color scheme) are found in the Options tab
and allows for more advanced management and security. Some features:

* Change the pi user password.
* Disable the WiFi interface.
* Change the SSH server port and add a public key.
* Easy theming.

Perhaps most interesting is the ability to configure a .onion address for your Centipede, allowing you to log in securely from around 
the world using end-to-encryption and hiding the address of your Centipede in the process. This requires use of the Tor network, and
can be done using the Tor browser from a desktop, Orbot from an Android device, or Kronymous from your Chrome browser:
* Tor Browser: https://www.torproject.org/download/download-easy.html.en
* Orbot: https://guardianproject.info/apps/orbot/
* Kronymous: https://chrome.google.com/webstore/detail/kronymous-access-internet/dfdhngcahhplaibahkkjhdklhihbaikl?hl=en
