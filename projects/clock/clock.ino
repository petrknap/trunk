/**
 * Hardware based on WeMos D1 R2 & mini - https://github.com/esp8266/arduino#installing-with-boards-manager
 */
#include <NTPClient.h>            // https://github.com/arduino-libraries/NTPClient
#include <ESP8266WiFi.h>          // https://github.com/esp8266/Arduino/tree/master/libraries/ESP8266WiFi
#include <WiFiUdp.h>              // https://github.com/esp8266/Arduino/tree/master/libraries/ESP8266WiFi
#include <LedControlSPIESP8266.h> // https://github.com/labsud/LedControlSpipESP8266
#include <FC16.h>                 // https://github.com/ridercz/Altairis-ESP8266-FC16
#include <TimeLib.h>              // https://github.com/PaulStoffregen/Time
#include <Timezone.h>             // https://github.com/JChristensen/Timezone

#include "config.h"

const int displayMessageScrollingDelay = 100 - DISPLAY_MESSAGE_SCROLLING_SPEED + 25;
const int ntpResync = NTP_RESYNC * 1000;

WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, NTP_SERVER_NAME, 0, ntpResync);
TimeChangeRule timeRuleWinter = {"W", TZ_DST_END_WEEK, TZ_DST_END_DOW, TZ_DST_END_MONTH, TZ_DST_END_HOUR, TZ_OFFSET/60};
TimeChangeRule timeRuleSummer = {"S", TZ_DST_BEGIN_WEEK, TZ_DST_BEGIN_DOW, TZ_DST_BEGIN_MONTH, TZ_DST_BEGIN_HOUR, TZ_DST_OFFSET/60};
Timezone timezone(timeRuleWinter, timeRuleSummer);
FC16 display = FC16(DISPLAY_CS_PIN, DISPLAY_COUNT_OF_DISPLAYS);
int lastWiFiStatus = WL_DISCONNECTED;
bool wasSynchronized = false;
int lastSecond = -1;

void setup() {
  // Setup display
  display.begin();
  display.setIntensity(DISPLAY_INTENSITY);
  display.clearDisplay();
  display.setText("Connecting to WiFi...");
  display.update();

  // Start Wi-Fi
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
}

void loop() {
  // Display message if not connected and not synchronized
  bool isConnected = checkWiFiStatus();
  if (!isConnected && !wasSynchronized) {
    display.update();
    delay(displayMessageScrollingDelay);
    return;
  }

  // Do nothing if time was not synchronized at least once
  bool isSynchronized = timeClient.update();
  if (!wasSynchronized) wasSynchronized = isSynchronized;
  if (!wasSynchronized && millis() > NTP_TIMEOUT * 1000) {
    display.setText("NO NTP");
    display.update();
    delay(30000);
    return;
  }

  // Display current time otherwise
  time_t currentTime = timeClient.getEpochTime();
  byte currentSecond = second(currentTime);
  if (currentSecond != lastSecond) {
    currentTime = timezone.toLocal(currentTime);
    lastSecond = currentSecond;
    display.setClock(hour(currentTime), minute(currentTime), currentSecond);
  }
}

bool checkWiFiStatus() {
  int currentWiFiStatus =  WiFi.status();
  if (currentWiFiStatus != lastWiFiStatus) {
    lastWiFiStatus = currentWiFiStatus;
    switch (currentWiFiStatus) {
      case WL_DISCONNECTED:
        timeClient.end();       // Stop NTP client when disconnected
        WiFi.reconnect();       // Attempt to reconnect
        break;
      case WL_CONNECTED:
        display.clearDisplay();
        display.setText("SYNC...");
        display.update();       // Show message about sync in progress
        timeClient.begin();     // Start network client when connected
        break;
      case WL_NO_SSID_AVAIL:
        display.setText("WiFi network cannot be found!");
        break;
      case WL_CONNECT_FAILED:
        display.setText("Incorrect WiFi password!");
        break;
      default:
        display.setText("Internal Error!");
        break;
    }
  }
  return currentWiFiStatus == WL_CONNECTED;
}
