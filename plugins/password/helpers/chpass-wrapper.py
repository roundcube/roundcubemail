#!/usr/bin/env python
#
# for configuration see plugins/password/config.inc.php.dist
#
# CHPASSWD:
#
# for use with chpasswd this wrapper has to be called with SUDO, see README 
# $config['password_chpasswd_cmd'] = 'sudo /pathto/chpass-wrapper.py -ftpusers';
#
# EXPECT:
#
# to use expect set password_expect_params in config.inc.php, NO sudo!
# $config['password_chpasswd_cmd'] = '/pathto/chpass-wrapper.py -ftpusers';
# $config['password_expect_params'] = '-ssh -host hostname'; 
# 
# you need an writeable .ssh dir in webserver homedir for ssh
# you can test this by do ssh user@host as webserver
#
# passwd-expect MUST be in the same directory as this wrapper
#
# PARAMETER: 
# -expect           use expect (set by driver if expect_params set) 
# -ftpusers         blacklist users in /etc/ftpusers
#
# expect only:
# -expscript script name of expect script (default passwd-expect)
# -host hostname    connect to hostname (default localhost)
# -ssh              use ssh protocol
# all addional parameters are passed to passwd-expect, see there.
#

import os, sys, pwd, re
import subprocess, signal

###############
# startup, prepare values and parameters
# path to executables
CHPASSBIN = '/usr/sbin/chpasswd'
EXPECTBIN = '/usr/bin/expect'

# expect script, has to be in the same directory as this script
expscript = 'passwd-expect'
# path to this script
PATH = os.path.dirname(os.path.realpath(sys.argv[0]))

# other defaults:
ftpusers=False  # do not blacklist from /etc/ftpusers
expect=False    # use chpasswd
hostname='localhost'    # change on localhost
scriptargs = ''         # no additional args

#############################
# process args from command line
count=1
while(count < len(sys.argv)):
  # we need hostname, also pass
  if sys.argv[count] == '-host':
    hostname = sys.argv[count+1]

  # local only args, do not pass
  try:
    if sys.argv[count] == '-ftpusers':
      ftpusers = True
      count += 1
      continue

    if sys.argv[count] == '-chpasswd':
      expect = False
      count += 1
      continue

    if sys.argv[count] == '-expect':
      expect = True
      count += 1
      continue

    if sys.argv[count] == '-expscript':
      expect = True
      expscript = sys.argv[count+1]
      count += 2
      continue

  except ValueError:
    continue

  # pass all other args
  scriptargs += ' ' + sys.argv[count]
  count += 1


##############################
# here we go ...
# read username:password \n oldpasswd 
try:
  username, password = sys.stdin.readline().rstrip('\r\n').split(':', 1)
  oldpassw = sys.stdin.readline().rstrip('\r\n')
except ValueError, e:
  sys.exit('Malformed input from roundcube')


# BLACKLIST user to disable password change
BLACKLIST = [
    # add blacklisted users here if you don't can/wan't
    # use /etc/ftpusers, eg.
    # 'ftp','root','www'
]

# append users from /etc/ftpusers to BLACKLIST
try:
  if ftpusers:
    with open("/etc/ftpusers", "r") as readftp:
      for line in readftp:
        if line.startswith('#'):
           continue
        BLACKLIST.append(line.rstrip('\n'))

except IOError:
  pass


# check if blacklisted
if username in BLACKLIST:
    sys.exit('Changing password for user %s is forbidden (blacklisted)!' %
             username)

# check if a system user (UID<1000)
if hostname == 'localhost':
  try:
    user = pwd.getpwnam(username)
  except KeyError, e:
    sys.exit('No such user: %s' % username)

  if user.pw_uid < 1000:
    sys.exit('Changing password for user %s is forbidden (system user)!' %
             username)

####################
# ready to change password ...
if expect != True:
    # CHPASSWD, very simple :-)
    handle = subprocess.Popen(CHPASSBIN, stdin = subprocess.PIPE)
    handle.communicate('%s:%s\n' % (username, password))

else:
    # EXPECT
    if scriptargs == '':
       scriptargs = ' -ssh -host ' + hostname

    # expect expect script has to be in same directory, HACK: log to stdout
    cmd = EXPECTBIN + ' ' + PATH + '/' + expscript + scriptargs + ' -log \|cat'

    # call expect
    handle = subprocess.Popen( cmd, shell=True, stdin = subprocess.PIPE)
    handle.communicate('%s\n%s\n%s\n' % (username, oldpassw, password))

# send back return value from popen
sys.exit(handle.returncode)
