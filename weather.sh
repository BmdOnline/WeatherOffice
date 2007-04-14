#!/bin/sh
#-------------------------------------------------------------------
# Dreambox Display Script
#
export LD_LIBRARY_PATH=/var/bin/tuxwet
     
/var/bin/tuxwet/tuxwetter "TXTPLAIN=Lokales Wetter,http://192.168.10.100/weather/maindb.php"

