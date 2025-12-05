#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <Servo.h>

const char* ssid = "Foxy";
const char* password = "dimfaq123";
const char* serverUrl = "http://192.168.23.27:8000";
const char* deviceName = "nodemcu";

#define PIN_DS18B20 D4
#define PIN_TRIG D5
#define PIN_ECHO D6
#define PIN_SERVO D2
#define TURB_PIN A0

OneWire oneWire(PIN_DS18B20);
DallasTemperature sensors(&oneWire);

Servo feeder;
int STOP_US = 1500;
int CW_US = 1700;

int SINGLE_REV_MS = 700;
int REV_COUNT = 4;
int GAP_BETWEEN_REVS_MS = 80;
int MAX_SPIN_TOTAL_MS = 20000;

unsigned long lastSend = 0;
unsigned long sendInterval = 10000;
unsigned long lastCmdPoll = 0;
unsigned long cmdPollInterval = 3000;

int feedEventFlag = 0;

long readUltrasonicCm() {
  digitalWrite(PIN_TRIG, LOW);
  delayMicroseconds(2);
  digitalWrite(PIN_TRIG, HIGH);
  delayMicroseconds(10);
  digitalWrite(PIN_TRIG, LOW);
  unsigned long duration = pulseIn(PIN_ECHO, HIGH, 30000UL);
  if (duration == 0) return 0;
  long distanceCm = (long)((duration * 0.034) / 2.0);
  return distanceCm;
}

void setup() {
  Serial.begin(115200);
  delay(200);
  Serial.println("\n=== SMART AQUARIUM - AUTO 3x FULL REV ON START ===");

  sensors.begin();
  feeder.attach(PIN_SERVO);
  feeder.writeMicroseconds(STOP_US);
  delay(100);

  pinMode(PIN_TRIG, OUTPUT);
  pinMode(PIN_ECHO, INPUT);
  digitalWrite(PIN_TRIG, LOW);

  Serial.println("Connecting to WiFi...");
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int tries = 0;
  while (WiFi.status() != WL_CONNECTED && tries < 30) {
    delay(500);
    Serial.print(".");
    tries++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Connected!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("RSSI: ");
    Serial.println(WiFi.RSSI());
  } else {
    Serial.println("\nWiFi Failed to connect.");
  }

  Serial.print("SINGLE_REV_MS = "); Serial.println(SINGLE_REV_MS);
  Serial.print("REV_COUNT = "); Serial.println(REV_COUNT);
  Serial.print("CW_US = "); Serial.println(CW_US);
  Serial.print("STOP_US = "); Serial.println(STOP_US);
}

void loop() {
  unsigned long now = millis();

  feeder.writeMicroseconds(STOP_US);

  if (now - lastSend >= sendInterval) {
    readAndSendData();
    lastSend = now;
    feedEventFlag = 0;
  }

  if (now - lastCmdPoll >= cmdPollInterval) {
    pollCommand();
    lastCmdPoll = now;
  }

  delay(10);
}

void readAndSendData() {
  Serial.println("\n=== READING SENSOR DATA ===");

  sensors.requestTemperatures();
  float tempC = sensors.getTempCByIndex(0);
  Serial.print("Temperature: ");
  Serial.print(tempC);
  Serial.println(" °C");

  int turbRaw = analogRead(TURB_PIN);
  Serial.print("Turbidity Raw: ");
  Serial.println(turbRaw);

  long dist = readUltrasonicCm();
  Serial.print("Water Distance: ");
  Serial.print(dist);
  Serial.println(" cm");

  String postData = "";
  postData += "device=" + String(deviceName);
  postData += "&temp_c=" + String(tempC);
  postData += "&turbidity_raw=" + String(turbRaw);
  postData += "&distance_cm=" + String(dist);
  postData += "&feed_event=" + String(feedEventFlag);

  Serial.println("\nSending POST to server...");
  Serial.println(postData);

  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;
    String fullUrl = String(serverUrl) + "/receive_data.php";
    http.begin(client, fullUrl.c_str());
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    int code = http.POST(postData);
    Serial.print("Server Response Code: ");
    Serial.println(code);

    if (code > 0) {
      String resp = http.getString();
      Serial.print("Server Response: ");
      Serial.println(resp);
    } else {
      Serial.print("HTTP POST failed, error: ");
      Serial.println(http.errorToString(code));
    }
    http.end();
  } else {
    Serial.println("WiFi NOT connected. Data NOT sent.");
  }
}

void pollCommand() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n[CMD] WiFi disconnected, skipping command poll.");
    return;
  }

  Serial.println("\n[CMD] Checking for new command...");

  WiFiClient client;
  HTTPClient http;
  String fullUrl = String(serverUrl) + "/get_command.php?device=" + String(deviceName);
  http.begin(client, fullUrl.c_str());

  int httpCode = http.GET();
  Serial.print("Command HTTP Code: ");
  Serial.println(httpCode);

  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    Serial.println("Command Payload: " + payload);

    int feedIndex = payload.indexOf("\"feed\":");
    if (feedIndex < 0) feedIndex = payload.indexOf("feed:");

    if (feedIndex >= 0) {
      int valStart = payload.indexOf(":", feedIndex);
      if (valStart >= 0) {
        valStart++;
        int commaPos = payload.indexOf(",", valStart);
        int bracePos = payload.indexOf("}", valStart);
        int valEnd = -1;
        if (commaPos >= 0 && bracePos >= 0) valEnd = min(commaPos, bracePos);
        else if (commaPos >= 0) valEnd = commaPos;
        else if (bracePos >= 0) valEnd = bracePos;

        String valStr;
        if (valEnd > valStart) valStr = payload.substring(valStart, valEnd);
        else valStr = payload.substring(valStart);

        valStr.trim();
        int st = 0; while (st < valStr.length() && (valStr[st] < '0' || valStr[st] > '9')) st++;
        int ed = valStr.length() - 1; while (ed >= 0 && (valStr[ed] < '0' || valStr[ed] > '9')) ed--;
        String numOnly = "0";
        if (ed >= st) numOnly = valStr.substring(st, ed + 1);

        int feedCmd = numOnly.toInt();
        Serial.print("Parsed feedCmd = ");
        Serial.println(feedCmd);

        if (feedCmd == 1) {
          startFeedingFullRevs(REV_COUNT);
        } else {
          Serial.println("STOP command received (no-op, this auto-mode stops after spins).");
        }
      } else {
        Serial.println("[CMD] can't find ':' after feed key.");
      }
    } else {
      Serial.println("[CMD] No feed key found in payload.");
    }
  } else {
    Serial.print("[CMD] GET failed or no response: ");
    Serial.println(http.errorToString(httpCode));
  }
  http.end();
}

void startFeedingFullRevs(int revCount) {
  Serial.print(">> START received -> performing ");
  Serial.print(revCount);
  Serial.println(" full revolutions then stop.");

  feedEventFlag = 1;

  unsigned long total = 0;
  for (int i = 1; i <= revCount; i++) {
    Serial.print("  Full rev "); Serial.print(i); Serial.print(" of "); Serial.println(revCount);
    feeder.writeMicroseconds(CW_US);
    delay(SINGLE_REV_MS);
    feeder.writeMicroseconds(STOP_US);
    delay(GAP_BETWEEN_REVS_MS);

    total += SINGLE_REV_MS + GAP_BETWEEN_REVS_MS;
    if (total >= MAX_SPIN_TOTAL_MS) {
      Serial.println("  Reached MAX_SPIN_TOTAL_MS cap, stopping early.");
      break;
    }
  }

  feeder.writeMicroseconds(STOP_US);
  Serial.println(">> Full-rev sequence finished, servo STOPPED.");
}
