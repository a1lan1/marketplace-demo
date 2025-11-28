CREATE TABLE IF NOT EXISTS events
(
    `ts` DateTime,
    `user_id` Nullable(UInt64),
    `event_type` String,
    `page` Nullable(String)
)
ENGINE = MergeTree()
ORDER BY ts;

CREATE MATERIALIZED VIEW IF NOT EXISTS events_mv
TO events
AS SELECT
    ts,
    user_id,
    event_type,
    JSON_VALUE(CAST(data AS String), '$.page') AS page
FROM events_raw;
