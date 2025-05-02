import paho.mqtt.client as mqtt
import mysql.connector
from mysql.connector import Error
import json
import logging
import signal
import sys
from datetime import datetime, timedelta

# Logging configuration
logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
logger = logging.getLogger(__name__)

# MQTT settings
broker = "broker.emqx.io"
port = 1883
subscribe_topic = "sensorsData"
pump_topic = "pump_control"
light_topic = "light_control"

# Database settings
db_config = {
    'host': 'localhost',
    'user': 'phong_IOT',
    'password': 'phongdeptrai',
    'database': 'smart_plant_system'
}

# Database connection
connection = None


def connect_database():
    """Connect to the database."""
    try:
        conn = mysql.connector.connect(**db_config)
        if conn.is_connected():
            logger.info("Connected to the database")
        return conn
    except Error as e:
        logger.error(f"Error connecting to database: {e}")
        return None


def insert_sensor_data(cursor, connection, sen_id, value, state_col, state_value):
    """Insert sensor data into the monitor table."""
    try:
        query = f"INSERT INTO monitor (sen_id, value, {state_col}) VALUES (%s, %s, %s)"
        cursor.execute(query, (sen_id, value, state_value))
        connection.commit()
        logger.info(f"Inserted data: sen_id={sen_id}, value={value}, {state_col}={state_value}")
    except Error as e:
        logger.error(f"Error inserting sensor data: {e}")


def humidity_update(connection, humid_result, humidity_value, cursor, mqtt_client):
    """Update humidity state."""
    sen_id_humid, thres_min_humid, thres_max_humid = humid_result
    logger.info(f"Humidity sen_id: {sen_id_humid}, thres_min: {thres_min_humid}, thres_max: {thres_max_humid}")

    if humidity_value >= thres_max_humid:
        pump_state = "off"
    elif humidity_value <= thres_min_humid:
        pump_state = "on"
    else:
        query_last_state = "SELECT pump_state FROM monitor WHERE sen_id = %s ORDER BY time_stamp DESC LIMIT 1"
        cursor.execute(query_last_state, (sen_id_humid,))
        last_state_result = cursor.fetchone()
        pump_state = last_state_result[0] if last_state_result else "off"

    insert_sensor_data(cursor, connection, sen_id_humid, humidity_value, "pump_state", pump_state)
    mqtt_client.publish(pump_topic, pump_state)
    logger.info(f"Published pump_state '{pump_state}' to topic '{pump_topic}'")


def temperature_update(connection, temp_result, temperature_value, cursor, mqtt_client):
    """Update temperature state."""
    sen_id_temp, thres_min_temp, thres_max_temp = temp_result
    logger.info(f"Temperature sen_id: {sen_id_temp}, thres_min: {thres_min_temp}, thres_max: {thres_max_temp}")

    if temperature_value >= thres_max_temp:
        light_state = "off"
    elif temperature_value <= thres_min_temp:
        light_state = "on"
    else:
        query_last_state = "SELECT light_state FROM monitor WHERE sen_id = %s ORDER BY time_stamp DESC LIMIT 1"
        cursor.execute(query_last_state, (sen_id_temp,))
        last_state_result = cursor.fetchone()
        light_state = last_state_result[0] if last_state_result else "off"

    insert_sensor_data(cursor, connection, sen_id_temp, temperature_value, "light_state", light_state)
    mqtt_client.publish(light_topic, light_state)
    logger.info(f"Published light_state '{light_state}' to topic '{light_topic}'")


def process_schedule_and_check(cursor, mqtt_client, humid_result, temp_result, humidity_value, temperature_value):
    """Process schedules and check if any schedule matches the current time."""
    try:
        # Fetch current timestamp
        current_time = datetime.now()

        # Retrieve all schedules from schedule_table
        cursor.execute("SELECT schedule_id, action_type, scheduled_date, scheduled_time FROM schedule_table")
        schedules = cursor.fetchall()

        for schedule in schedules:
            schedule_id, action_type, scheduled_date, scheduled_time = schedule
            sen_id_humid, thres_max_humid = humid_result
            sen_id_temp, thres_max_temp = temp_result
            
            # Parse schedule date and time
            scheduled_datetime = datetime.strptime(f"{scheduled_date} {scheduled_time}", "%Y-%m-%d %H:%M:%S")

            # Compare with the current timestamp
            if current_time >= scheduled_datetime and current_time <= (scheduled_datetime + timedelta(seconds=59)):
                # Determine pump_state based on humidity and thres_max
                if "pump" in action_type:
                    pump_state = "on" if humidity_value < thres_max_humid else "off"
                    mqtt_client.publish(pump_topic, pump_state)
                    logger.info(f"Executed scheduled action: Turned '{pump_state}' pump (schedule_id={schedule_id})")

                # Determine light_state based on temperature and thres_max
                if "led" in action_type:
                    light_state = "on" if temperature_value < thres_max_temp else "off"
                    mqtt_client.publish(light_topic, light_state)
                    logger.info(f"Executed scheduled action: Turned '{light_state}' LED (schedule_id={schedule_id})")

                # Mark schedule as executed (optional)
                return True  # Indicating a schedule was executed

        return False  # No schedules matched

    except Exception as e:
        logger.error(f"Error processing schedule: {e}")
        return False



def on_message(client, userdata, message):
    """Handle incoming MQTT messages."""
    global connection

    try:
        # Decode and parse JSON payload
        sensor_data = json.loads(message.payload.decode())
        temperature_value = sensor_data.get("temperature")
        humidity_value = sensor_data.get("humidity")
        logger.info(f"Received: Temperature={temperature_value}°C, Humidity={humidity_value}%")

        if temperature_value is None or humidity_value is None:
            logger.warning("Invalid data in message")
            return

        if not connection or not connection.is_connected():
            connection = connect_database()
            if not connection:
                logger.error("Database connection unavailable")
                return

        cursor = connection.cursor()
        
        # Step 1: Execute the schedule
        # Retrieve thresholds for humidity and temperature
        cursor.execute("SELECT sen_id, thres_max FROM sensors_table WHERE type = 'humid' LIMIT 1")
        humid_result = cursor.fetchone()
        
        cursor.execute("SELECT sen_id, thres_max FROM sensors_table WHERE type = 'temperature' LIMIT 1")
        temp_result = cursor.fetchone()

        schedule_executed = process_schedule_and_check(cursor, client, humid_result, temp_result, humidity_value, temperature_value)

        # Step 2: If the schedule was executed, skip further processing
        if schedule_executed:
            logger.info("Schedule executed, skipping further processing for this message.")
            return

        # Step 3: Continue with normal message processing
        # Retrieve mode
        cursor.execute("SELECT mode FROM plants_table WHERE plant_id = 1")
        mode = cursor.fetchone()[0]
        logger.info(f"Plant mode: {mode}")

        # -------------AUTO mode------------------
        if mode == 'auto':
            cursor.execute("SELECT sen_id, thres_min, thres_max FROM sensors_table WHERE type = 'humid' LIMIT 1")
            humid_result = cursor.fetchone()
            if humid_result:
                humidity_update(connection, humid_result, humidity_value, cursor, client)

            cursor.execute("SELECT sen_id, thres_min, thres_max FROM sensors_table WHERE type = 'temperature' LIMIT 1")
            temp_result = cursor.fetchone()
            if temp_result:
                temperature_update(connection, temp_result, temperature_value, cursor, client)
        # ------------- MANUAL mode---------------
        elif mode == 'manual':
            # For Humidity
            cursor.execute("SELECT sen_id FROM sensors_table WHERE type = 'humid' LIMIT 1")
            humid_result = cursor.fetchone()
            if humid_result:
                humid_sen_id = humid_result[0]
                # Retrieve last pump_state for the humidity sensor
                query_last_pump_state = "SELECT pump_state FROM monitor WHERE sen_id = %s ORDER BY time_stamp DESC LIMIT 1"
                cursor.execute(query_last_pump_state, (humid_sen_id,))
                last_pump_state_result = cursor.fetchone()
                pump_state = last_pump_state_result[0] if last_pump_state_result else "off"
                
                # Insert and publish pump_state
                insert_sensor_data(cursor, connection, humid_sen_id, humidity_value, "pump_state", pump_state)
                client.publish(pump_topic, pump_state)
                logger.info(f"Published pump_state '{pump_state}' to topic '{pump_topic}' in MANUAL mode")

            # For Temperature
            cursor.execute("SELECT sen_id FROM sensors_table WHERE type = 'temperature' LIMIT 1")
            temp_result = cursor.fetchone()
            if temp_result:
                temp_sen_id = temp_result[0]
                # Retrieve last light_state for the temperature sensor
                query_last_light_state = "SELECT light_state FROM monitor WHERE sen_id = %s ORDER BY time_stamp DESC LIMIT 1"
                cursor.execute(query_last_light_state, (temp_sen_id,))
                last_light_state_result = cursor.fetchone()
                light_state = last_light_state_result[0] if last_light_state_result else "off"

                # Insert and publish light_state
                insert_sensor_data(cursor, connection, temp_sen_id, temperature_value, "light_state", light_state)
                client.publish(light_topic, light_state)
                logger.info(f"Published light_state '{light_state}' to topic '{light_topic}' in MANUAL mode")

                
    except Exception as e:
        logger.error(f"Error processing message: {e}")

def graceful_shutdown(signal, frame):
    """Gracefully shut down the program."""
    global connection
    logger.info("Shutting down...")
    if connection and connection.is_connected():
        connection.close()
    sys.exit(0)


def main():
    global connection
    connection = connect_database()
    if not connection:
        return

    # MQTT setup
    client = mqtt.Client(client_id="PythonSubscriber")
    client.on_message = on_message
    client.user_data_set(connection)
    client.connect(broker, port)
    client.subscribe(subscribe_topic)

    # Handle graceful shutdown
    signal.signal(signal.SIGINT, graceful_shutdown)
    signal.signal(signal.SIGTERM, graceful_shutdown)

    logger.info("Starting MQTT loop")
    client.loop_forever()


if __name__ == "__main__":
    main()
