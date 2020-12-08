CREATE TABLE signals (
  date timestamp with time zone DEFAULT NULL,
  nodeid integer NOT NULL DEFAULT 0,
  netdev integer NOT NULL DEFAULT 0,
  channel smallint NOT NULL DEFAULT 0,
  software varchar(10) NOT NULL DEFAULT 0,
  rxsignal smallint NOT NULL DEFAULT 0,
  txsignal smallint NOT NULL DEFAULT 0,
  rxrate smallint NOT NULL DEFAULT 0,
  txrate smallint NOT NULL DEFAULT 0,
  rxccq smallint NOT NULL DEFAULT 0,
  txccq smallint NOT NULL DEFAULT 0,
  rxbytes bigint DEFAULT NULL,
  txbytes bigint DEFAULT NULL,
  UNIQUE (date,nodeid,netdev)
);
CREATE INDEX signals_nodeid_idx ON signals (nodeid);
CREATE INDEX signals_netdev_idx ON signals (netdev);

INSERT INTO uiconfig (section, var, value, description, disabled) VALUES ('mikrotik','user','admin','Użytkownik przy podłączaniu do Mikrotika',0);
INSERT INTO uiconfig (section, var, value, description, disabled) VALUES ('mikrotik','password','admin','Hasło przy podłączaniu do Mikrotika',0);

INSERT INTO dbinfo VALUES ('dbversion_MikrotikPlugin','2015092800');
