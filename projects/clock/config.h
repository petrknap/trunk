// Display options
#define DISPLAY_MESSAGE_SCROLLING_SPEED 85 // range <1,100>
#define DISPLAY_CS_PIN D8
#define DISPLAY_COUNT_OF_DISPLAYS 4 // range <1,8>
#define DISPLAY_INTENSITY 1 // range <1,16>

// NTP options
#define NTP_SERVER_NAME "pool.ntp.org"
#define NTP_TIME_OFFSET 7200 // seconds, +02:00
#define NTP_RESYNC 28800 // seconds, every 8 hour
#define NTP_TIMEOUT 30 // seconds

// WiFi options
#define WIFI_SSID "redefined in config.local.h"
#define WIFI_PASSWORD "redefined in config.local.h"



#include "config.local.h"
