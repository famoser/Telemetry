CREATE TABLE applications (
  id          INTEGER PRIMARY KEY,
  guid        TEXT,
  name        TEXT,
  description TEXT,
  projectUrl  TEXT
);

CREATE TABLE users (
  id             INTEGER PRIMARY KEY,
  guid           TEXT,
  device_name    TEXT,
  system_name    TEXT,
  meta_data      TEXT,
  application_id TEXT,
  create_date    INTEGER
);

CREATE TABLE events (
  id          INTEGER PRIMARY KEY,
  user_guid   INTEGER,
  event_id    INTEGER,
  create_date INTEGER
);

CREATE TABLE logs (
  id          INTEGER PRIMARY KEY,
  user_guid   INTEGER,
  message     TEXT,
  log_level   TEXT,
  location    TEXT,
  handled     INTEGER,
  create_date INTEGER
);