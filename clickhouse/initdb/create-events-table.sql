CREATE TABLE IF NOT EXISTS events_raw
(
    `ts` DateTime,
    `user_id` Nullable(UInt64),
    `event_type` String,
    `url` String,
    `data` JSON
)
ENGINE = Kafka
SETTINGS
    kafka_broker_list = 'kafka:9092',
    kafka_topic_list = 'user_activity',
    kafka_group_name = 'clickhouse-events-group',
    kafka_format = 'JSONEachRow',
    kafka_num_consumers = 1;
