#define _GNU_SOURCE
#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>

// set the UID this script will run as (root user)
#define UID 0

/* INSTALLING:
  gcc -o chgvirtualminpasswd chgvirtualminpasswd.c
  chown root.virtual-server-user-group chgvirtualminpasswd
  strip chgvirtualminpasswd
  chmod 4550 chgvirtualminpasswd
*/

int main(int argc, char *argv[])
{

  // Check if user, old password and new password are passed
  if (argc < 4) {
    fprintf(stderr, "You must supply 3 arguments : user, old password, new password.\n");
    exit(EXIT_FAILURE);
  }

  char *user = argv[1];
  char *oldpass = argv[2];
  char *newpass = argv[3];
  
  // Build command with args
  char *cmd_with_args;
  asprintf( &cmd_with_args, "/usr/sbin/virtualmin change-password %s %s %s", user, oldpass, newpass);
  
  // Run password change command
  int rc, cc;
  cc = setuid(UID);
  rc = system(cmd_with_args);
  if ((rc != 0) || (cc != 0)) {
    fprintf(stderr, "__ %s:  failed %d  %d\n", argv[0], rc, cc);
    return 1;
  }

  return 0;
}
