from clickhouse_driver import Client
import os

CLICKHOUSE_HOST = os.getenv("CLICKHOUSE_HOST", "clickhouse")
CLICKHOUSE_PORT = int(os.getenv("CLICKHOUSE_PORT", "9000"))
CLICKHOUSE_USER = os.getenv("CLICKHOUSE_USER", "default")
CLICKHOUSE_PASSWORD = os.getenv("CLICKHOUSE_PASSWORD", "password")

print(f"Connecting to {CLICKHOUSE_HOST}:{CLICKHOUSE_PORT} as {CLICKHOUSE_USER}...")

client = Client(host=CLICKHOUSE_HOST, port=CLICKHOUSE_PORT, user=CLICKHOUSE_USER, password=CLICKHOUSE_PASSWORD)

try:
    print("--- RAW DATA (Last 10) ---")
    data = client.execute("SELECT user_id, url, event_type FROM user_activities ORDER BY ts DESC LIMIT 10")
    for row in data:
        print(f"User: {row[0]}, URL: '{row[1]}', Event: {row[2]}")
        
    print("\n--- TEST EXACT QUERY ---")
    query = """
        SELECT DISTINCT
            toInt64(extract(url, '/catalog/([0-9]+)')) AS product_id
        FROM user_activities
        WHERE user_id = 1
          AND event_type = 'page_view'
          AND url LIKE '%/catalog/%'
        ORDER BY ts DESC
        LIMIT 5
    """
    try:
        results = client.execute(query)
        print(f"Results: {results}")
    except Exception as query_err:
        print(f"Query Failed: {query_err}")

    print("\n--- TEST GROUP BY QUERY (ALTERNATIVE) ---")
    query_alt = """
        SELECT
            toInt64(extract(url, '/catalog/([0-9]+)')) AS product_id
        FROM user_activities
        WHERE user_id = 1
          AND event_type = 'page_view'
          AND url LIKE '%/catalog/%'
        GROUP BY product_id
        ORDER BY max(ts) DESC
        LIMIT 5
    """
    try:
        results = client.execute(query_alt)
        print(f"Results Alt: {results}")
    except Exception as query_err:
        print(f"Query Alt Failed: {query_err}")

except Exception as e:
    print(f"Error: {e}")
