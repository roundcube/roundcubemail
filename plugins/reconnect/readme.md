# RoundCube Reconnect Plugin

RoundCube reconnect Plugin is a small plugin that will try to reconnect to an
IMAP server, if there is no explicit error code replied. If there is a know
failure like wrong password, no additional attempts are triggered. This should
help in cases, when the connection to the IMAP server is not 100% stable.

## Configuration

You can specify the maximum attempts to connect the IMAP server.

  // Maximum attempts to connect the IMAP server
  $config['reconnect_imap_max_attempts'] = 5;
