#!/usr/bin/env python
#
# extended wrapper to /user/sbin/chpasswd
#  - more password and user checks
#  - file based blacklist (/etc/ftpusers)
#  - timeout for reading and writing

TIMEOUT= 10

import os, sys, pwd, re
import subprocess, signal


# get args for script 
scriptargs = ''
count=1
while(count < len(sys.argv)):
  # local only args, do not pass
  try:
    if sys.argv[count] == '-timeout':
      count += 2
      TIMEOUT=int(sys.argv[count-1])
      continue
  except ValueError:
    continue
  # pass all other args
  scriptargs += ' ' + sys.argv[count]
  count += 1

# read username:password\noldpasswd with timeout
class TimeoutException(Exception):   # Custom exception class
    pass
def timeout_handler(signum, frame):   # Custom signal handler
    raise TimeoutException
signal.signal(signal.SIGALRM, timeout_handler)

# set timeout 
signal.alarm(TIMEOUT)
try:
    try:
      username, password = sys.stdin.readline().split(':', 1)
    except ValueError, e:
      sys.exit('Malformed input')

except TimeoutException:
  sys.exit('Timeout while reading input')
else:
  # clear timeout
  signal.alarm(0)

# add user to BLACKLIST and/or /etc/ftpusers to disable password change
BLACKLIST = [
    # add blacklisted users here
    'ftp',
]

# add /etc/ftpusers to BLACKLIST if exist
try:
  with open("/etc/ftpusers", "r") as ins:
    for line in ins:
      if line.startswith('#'):
         continue
      BLACKLIST.append(line.rstrip('\n'))

except IOError:
  # only catch error and continue
  pass

# check if user is blacklisted for password change
if username in BLACKLIST:
    sys.exit('Changing password for user %s is forbidden (user blacklisted)!' %
             username)


# check if user exit and is allowed to chage password
try:
    user = pwd.getpwnam(username)
except KeyError, e:
    sys.exit('No such user: %s' % username)

if user.pw_uid < 1000:
    sys.exit('Changing the password for user %s is forbidden (system user)!' %
             username)

elif len(username) < 4:
  # users should have at least 3 charactes
  sys.exit('Changing the password for user %s is forbidden (short user)!' %
             username)

if len(password) < 8:
    sys.exit('Password contains less than 8 characters!')

# set timeout
signal.alarm(TIMEOUT)
try:
  handle = subprocess.Popen('/usr/sbin/chpasswd', stdin = subprocess.PIPE)
  handle.communicate('%s:%s' % (username, password))
except TimeoutException:
  sys.exit('Timeout while changing password')
else:
  # clear timeout
  signal.alarm(0)

sys.exit(handle.returncode)
