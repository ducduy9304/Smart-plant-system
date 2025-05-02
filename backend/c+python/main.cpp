#include <WiFi.h>
#include <PubSubClient.h>
#include <OneWire.h>
#include <DallasTemperature.h>

// Wi-Fi and MQTT configuration
const char* ssid = "phongsaudoi";
const char* password = "phonghehehe";
const char* mqtt_server = "broker.emqx.io";

#define ONE_WIRE_BUS 33
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);

WiFiClient espClient;
PubSubClient client(espClient);

#define RELAY_PIN 25
#define LIGHT_PIN 26

const char* publish_topic = "sensorsData";
const char* pump_topic = "pump_control";
const char* light_topic = "light_control";

void initializeGPIO(int pin, int initialState) {
    pinMode(pin, OUTPUT);
    digitalWrite(pin, initialState);
}

void callback(char* topic, byte* payload, unsigned int length) {
    String message;
    for (unsigned int i = 0; i < length; i++) {
        message += (char)payload[i];
    }

    Serial.printf("Message received on topic %s: %s\n", topic, message.c_str());

    if (strcmp(topic, pump_topic) == 0) {
        digitalWrite(RELAY_PIN, message == "on" ? HIGH : LOW);
        Serial.println(message == "on" ? "Relay ON" : "Relay OFF");
    } else if (strcmp(topic, light_topic) == 0) {
        digitalWrite(LIGHT_PIN, message == "on" ? HIGH : LOW);
        Serial.println(message == "on" ? "Light ON" : "Light OFF");
    }
}

String readSensors() {
    sensors.requestTemperatures();
    float temperature = sensors.getTempCByIndex(0);
    if (temperature == DEVICE_DISCONNECTED_C) temperature = 0.0;

    float sensor_value = analogRead(32);
    float humidity = 100 - (sensor_value * 100 / 4095);
    humidity = constrain(humidity, 0, 100);

    return "{\"temperature\": " + String(temperature, 2) + ", \"humidity\": " + String(humidity, 2) + "}";
}

void reconnect() {
    while (!client.connected()) {
        Serial.print("Attempting MQTT connection...");
        String clientId = "ESP32Client-" + String(random(0xffff), HEX);
          if (client.connect(clientId.c_str())) {
            Serial.println("connected");
            client.subscribe(pump_topic);
            client.subscribe(light_topic);
        } else {
            Serial.printf("failed, rc=%d\n", client.state());
            delay(5000);
        }
    }
}

void setup() {
    Serial.begin(9600);
    initializeGPIO(RELAY_PIN, LOW);
    initializeGPIO(LIGHT_PIN, LOW);

    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nConnected to WiFi");

    client.setServer(mqtt_server, 1883);
    client.setCallback(callback);
    sensors.begin();
}

void loop() {
    if (!client.connected()) reconnect();
    client.loop();

    static unsigned long lastPublishTime = 0;
    if (millis() - lastPublishTime > 1000) {
        lastPublishTime = millis();
        String payload = readSensors();
        if (!client.publish(publish_topic, payload.c_str())) {
            Serial.println("Failed to publish sensor data");
        } else {
            Serial.println("Published: " + payload);
        }
    }
}
