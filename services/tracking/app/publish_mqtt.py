import json
import os
import sys
from datetime import datetime, timezone

try:
    import paho.mqtt.client as mqtt  # type: ignore
except Exception:
    print("paho-mqtt n'est pas installé. Faites: pip install paho-mqtt", file=sys.stderr)
    sys.exit(1)


def main() -> None:
    host = os.getenv("MQTT_HOST", "localhost")
    port = int(os.getenv("MQTT_PORT", "1883"))
    topic = os.getenv("MQTT_TOPIC", "setrag/tracking/position")

    payload = {
        "train_id": os.getenv("TRAIN_ID", "SETRAG-TEST"),
        "latitude": float(os.getenv("LAT", "0.3901")),
        "longitude": float(os.getenv("LON", "9.4544")),
        "speed_kmh": float(os.getenv("SPEED", "60")),
        "bearing_deg": float(os.getenv("BEARING", "90")),
        "timestamp_utc": datetime.now(timezone.utc).isoformat(),
    }

    client = mqtt.Client()
    client.connect(host, port, keepalive=10)
    client.loop_start()
    try:
        client.publish(topic, json.dumps(payload))
    finally:
        client.loop_stop()
        client.disconnect()


if __name__ == "__main__":
    main()


