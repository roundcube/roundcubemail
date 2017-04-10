#!/usr/bin/env python
#
# use passwd-expect to login to host and change password
# you need to install expect on the webserver host
# i.e. "apt-get install expect" or "yast -i expect"
#
# you need an writeable .ssh dir in webserver homedir
# you can test this by do ssh user@host as webserver
#
# Parameter:
# -ssh           use ssh for connetion to host (default)
# -host hostname connect to hostname (default localhost)
# - policy #     0-4 enable password local password checks
# -timeout #     0 - 99 time in s to wait for response
#
# all other parameters are passed to passwd-expect, see there.
#
"""
// 2017-02-13: Remarks by Kay Marquardt kay@rrr.de
// allowing sudo chpasswd directly opens a security hole!
// any script on the webserver can change password for every user, incl. root

// IMHO the most flexible and secure method for users with interactive shell access is to use ssh with an expect script
// I modifed the chpasss driver to provide the old password needed, additionally it pass the script response in case of error.

//    1. I wrote a wrapper (this script) for expect script provided by this plugin. 
//    2. move wrapper out of default location to a random place
//    3. change permissons of wrapper to root:www 770 to avoid changes by user or webserver
//    4. I add some security meassures(see README) 
//    5. remove sudo rules you may have applied (see README)
"""

# path to ecpect and script name (has to be in the same dir as this script)
# "which expect" show the path to expect programm
expect = '/usr/bin/expect'
script = 'passwd-expect'

TIMEOUT= 10

import os, sys, pwd, re
import subprocess, signal


# get args for script and extract hostname for us
hostname='localhost'
scriptargs = ''
count=1
while(count < len(sys.argv)):
  # get hostname
  if sys.argv[count] == '-host':
    hostname = sys.argv[count+1]
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
      oldpassw = sys.stdin.readline()
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
if hostname == 'localhost':
  try:
    user = pwd.getpwnam(username)
  except KeyError, e:
    sys.exit('No such user: %s' % username)

  if user.pw_uid < 1000:
    sys.exit('Changing the password for user %s is forbidden (system user)!' %
             username)

elif len(username) < 3:
  # non local users should have at least 3 charactes
  sys.exit('Changing the password for user %s is forbidden (short user)!' %
             username)

# enforcing password policy
if len(oldpassw) < 3:
    sys.exit('Old password to short or not known!')

if len(password) < 8:
    sys.exit('Password contains less than 8 characters!')

path= os.path.dirname(os.path.realpath(sys.argv[0]))

# script has to be in same directory
if scriptargs == '': scriptargs = ' -ssh -host ' + hostname
cmd = expect + ' ' + os.path.dirname(os.path.realpath(sys.argv[0])) + '/' + script + scriptargs + ' -log \|cat'

# set timeout
signal.alarm(TIMEOUT)
try:
  handle = subprocess.Popen( cmd, shell=True, stdin = subprocess.PIPE)
  handle.communicate('%s\n%s\n%s' % (username, oldpassw.rstrip('\r\n'), password))
except TimeoutException:
  sys.exit('Timeout while changing password (wrong old password)')
else:
  # clear timeout
  signal.alarm(0)


sys.exit(handle.returncode)
 
