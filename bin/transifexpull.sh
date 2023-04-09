#!/bin/sh

TX=`which tx`
PWD=`dirname "$0"`

cd $PWD/..

# In 'translator' mode files will contain empty translated texts
# where translation is not available, we'll remove these later

# Note: there's a bug in txclib, so if the command below doesn't
# work see https://github.com/transifex/transifex-client/commit/a80320735973dd608b48520bf3b89ad53e2b088b

$TX pull -a -f --mode translator

do_clean()
{
    # do not cleanup en_US files
    echo "$1" | grep -v en_US > /dev/null || return

    echo "Cleaning $1"

    # remove untranslated/empty texts
    perl -pi -e "s/^\\\$(labels|messages)\[[^]]+\]\s+=\s+'';\n//" $1
    perl -pi -e "s/^\\\$(labels|messages)\[[^]]+\]\s+=\s+\"\";\n//" $1
    # remove variable initialization
    perl -pi -e "s/^\\\$(labels|messages)\s*=\s*\[\];\n//" $1
    # remove (one-line) comments
    perl -pi -e "s/^\\/\\/.*//" $1
    # remove empty lines (but not in file header)
    perl -ne 'print if ($. < 18 || length($_) > 1)' $1 > $1.tmp
    mv $1.tmp $1
}

# clean up translation files
for file in program/localization/*/*.inc; do
    do_clean $file
done
for file in plugins/*/localization/*.inc; do
    do_clean $file
done

# remove empty localization files
for file in program/localization/*/labels.inc; do grep -q -E '\$labels' $file || rm $file; done
for file in program/localization/*/timezones.inc; do grep -q -E '\$labels' $file || rm $file; done
for file in program/localization/*/messages.inc; do grep -q -E '\$messages' $file || rm $file; done
for file in plugins/*/localization/*.inc; do grep -q -E '\$(labels|messages)' $file || rm $file; done
