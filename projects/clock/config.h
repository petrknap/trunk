// Display options
#define DISPLAY_MESSAGE_SCROLLING_SPEED 85 // range <1,100>
#define DISPLAY_CS_PIN D8
#define DISPLAY_COUNT_OF_DISPLAYS 4 // range <1,8>
#define DISPLAY_INTENSITY 1 // range <1,16>

// NTP options
#define NTP_SERVER_NAME "pool.ntp.org"
#define NTP_RESYNC 28800 // seconds, every 8 hour
#define NTP_TIMEOUT 30 // seconds

// Time zone options
#define TZ_DST_BEGIN_MONTH Mar
#define TZ_DST_BEGIN_WEEK Last
#define TZ_DST_BEGIN_DOW Sun
#define TZ_DST_BEGIN_HOUR 1 // UTC
#define TZ_DST_END_MONTH Oct
#define TZ_DST_END_WEEK Last
#define TZ_DST_END_DOW Sun
#define TZ_DST_END_HOUR 1 // UTC
#define TZ_DST_OFFSET 7200 // seconds, +02:00
#define TZ_OFFSET 3600 // seconds, +01:00

// WiFi options
#define WIFI_SSID "redefined in config.local.h"
#define WIFI_PASSWORD "redefined in config.local.h"



#include "config.local.h"
