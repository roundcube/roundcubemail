#!/usr/bin/env python
#
# extended wrapper to /user/sbin/chpasswd
#  - more password and user checks
#  - file based blacklist (/etc/ftpusers)
#  - timeout for reading and writing


# 0 no checks, 1 >= 8 char, 2 digits, 3 upper and lowercase, 4 special char
POLICY = 3
TIMEOUT= 10

import os, sys, pwd, re
import subprocess, signal


# get args for script 
scriptargs = ''
count=1
while(count < len(sys.argv)):
  # local only args, do not pass
  try:
    if sys.argv[count] == '-policy':
      count += 2
      POLICY=int(sys.argv[count-1])
      continue
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


"""
    Verify the strength of 'password'
    A password is considered strong if:
        8 characters length or more
        1 digit or more
        1 symbol or more
        1 uppercase letter or more
        1 lowercase letter or more
"""

# enforcing password policy
if POLICY > 0:
  if len(oldpassw) < 3:
    sys.exit('Old password to short or not known!')

  if len(password) < 8:
    sys.exit('Password contains less than 8 characters!')

  if re.search(r"[A-Zia-z]", password) is None:
      sys.exit('Password contains no character!')

if POLICY > 1:
  # look for digits
  if re.search(r"\d", password) is None:
      sys.exit('Password contains no digits!')

if POLICY > 2:
  # look for uppercase
  if re.search(r"[A-Z]", password) is None:
      sys.exit('Password contains no UPPERCASE character!')

  # look for lowercase
  if re.search(r"[a-z]", password) is None:
      sys.exit('Password contains no lowercase character!')

if POLICY > 3:
  # look for symbols
  if re.search(r"[ !#$%&'()*+,-./[\\\]^_`{|}~"+r'"]', password) is None:
      sys.exit('Password contains no symbol/special character!')


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
