#include <stdio.h>
#include <string.h>
#include <unistd.h>

// set the UID this script will run as (root user)
#define UID 0
#define CMD "/usr/sbin/dbmail-users"
//#define RCOK 0x100 --> use:
#define RCOK 0
//instead, because the return of the system() execution, never return 0x100 for me. As I read on 'man system'
// the return it's -1 on error, and the return status of the command otherwise
//I had to edit this file too:
// - drivers/dbmail.php:
//      change this:  function password_save($currpass, $newpass)
//      to this:      function save($currpass, $newpass)
//



/* INSTALLING:
  gcc -o chgdbmailusers chgdbmailusers.c
  chown root.apache chgdbmailusers
  strip chgdbmailusers
  chmod 4550 chgdbmailusers
*/

main(int argc, char *argv[])
{
  int cnt,rc,cc;
  char cmnd[255];

  strcpy(cmnd, CMD);

  if (argc > 1)
  {
    for (cnt = 1; cnt < argc; cnt++)
    {
      strcat(cmnd, " ");
      strcat(cmnd, argv[cnt]);
    }
  }
  else
  {
    fprintf(stderr, "__ %s:  failed %d  %d\n", argv[0], rc, cc);
    return 255;
  }

  cc = setuid(UID);
  rc = system(cmnd);

  if ((rc != RCOK) || (cc != 0))
  {
    fprintf(stderr, "__ %s:  failed %d  %d\n", argv[0], rc, cc);
    return 1;
  }

  return 0;
}
