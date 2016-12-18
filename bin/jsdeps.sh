#!/bin/sh

# Required programs

CURL=`which curl`
WGET=`which wget`
SHASUM=`which sha1sum`
UNZIP=`which unzip`

PWD=`dirname "$0"`
WHAT="$1"

# Downloads definition

JQUERY_VERSION="3.1.1"
JQUERY_URL="https://code.jquery.com/jquery-$JQUERY_VERSION.min.js"
JQUERY_SHA="f647a6d37dc4ca055ced3cf64bbc1f490070acba"
JQUERY_PATH="$PWD/../program/js/jquery.min.js"

JSTZ_VERSION="6c427658686c664da52c6a87cd62ec910baab276" #1.0.6
JSTZ_URL="https://bitbucket.org/pellepim/jstimezonedetect/raw/$JSTZ_VERSION/dist/jstz.min.js"
JSTZ_SHA="4291cd3b259d2060460c2a6ab99f428d3c0c9537"
JSTZ_PATH="$PWD/../program/js/jstz.min.js"

PKEY_VERSION="0e011cb18907a1adc0313aa92e69cd8858e1ef66"
PKEY_URL="https://raw.githubusercontent.com/diafygi/publickeyjs/$PKEY_VERSION/publickey.js"
PKEY_SHA="d0920e190754e024c4be76ad5bbc7e76b2e37a4d"
PKEY_PATH="$PWD/../program/js/publickey.js"

TINYMCE_VERSION="4.3.13"
TINYMCE_URL="http://download.ephox.com/tinymce/community/tinymce_$TINYMCE_VERSION.zip"
TINYMCE_SHA="28631746784453daf8baa10f2c8982aac5e32aa7"
TINYMCE_PATH="$PWD/../program/js/tinymce"
TINYMCE_LANGS="https://tinymce-services.azurewebsites.net/1/i18n/download?langs=ar,hy,az,eu,be,bs,bg_BG,ca,zh_CN,zh_TW,hr,cs,cs_CZ,da,nl,en_CA,en_GB,eo,et,fo,fi,fr_FR,fr_CH,gd,gl,ka_GE,de,de_AT,el,he_IL,hi_IN,hu_HU,is_IS,id,ga,it,ja,kab,km_KH,ko_KR,ku,ku_IQ,lv,lt,lb,mk_MK,ml_IN,nb_NO,oc,fa,fa_IR,pl,pt_BR,pt_PT,ro,ru,sk,sl_SI,es,es_MX,sv_SE,tg,ta,ta_IN,tt,th_TH,tr,tr_TR,ug,uk,uk_UA,vi,vi_VN,cy"

OPENPGP_VERSION="1.6.2"
OPENPGP_URL="https://github.com/openpgpjs/openpgpjs/archive/v$OPENPGP_VERSION.zip"
OPENPGP_SHA="70662ccd317a3e5221132778ec7bdf46342ab3fb"
OPENPGP_PATH="$PWD/../plugins/enigma/openpgp.min.js"

CM_VERSION="5.21.0"
CM_URL="http://codemirror.net/codemirror-$CM_VERSION.zip"
CM_SHA="3b767c2e3acd6796e54ed19ed2ac0755fcf87984"
CM_PATH="$PWD/../plugins/managesieve/codemirror.zip"

################################################################################

if [ -z "$SHASUM" ]; then
    echo "Sha1sum is required"
    exit 1
fi

if [ -z "$UNZIP" ]; then
    echo "Unzip is required"
    exit 1
fi

if [ -n "$CURL" ]; then
    GET=$CURL
    OPT="-o"
elif [ -n "$WGET" ]; then
    GET=$WGET
    OPT="-nv -O"
else
    echo "Curl or wget is required"
    exit 1
fi

if [ "$WHAT" = "jquery" ] || [ "$WHAT" = "" ]; then
    echo "Downloading jQuery..."

    $GET $JQUERY_URL $OPT $JQUERY_PATH
    if [ ! -f $JQUERY_PATH ]; then
        echo "ERROR: Failed to get $JQUERY_URL"
        exit 1
    fi

    SUM=`$SHASUM $JQUERY_PATH | cut -d " " -f 1`
    if [ "$SUM" != "$JQUERY_SHA" ]; then
        echo "ERROR: Incorrect SHA of $JQUERY_PATH. Expected: $JQUERY_SHA, got: $SUM"
        exit 1
    fi

    echo "Installing jQuery..."
    echo "Done"
fi

if [ "$WHAT" = "jstz" ] || [ "$WHAT" = "" ]; then
    echo "Downloading jsTimezoneDetect..."

    $GET $JSTZ_URL $OPT $JSTZ_PATH
    if [ ! -f $JSTZ_PATH ]; then
        echo "ERROR: Failed to get $JSTZ_URL"
        exit 1
    fi

    SUM=`$SHASUM $JSTZ_PATH | cut -d " " -f 1`
    if [ "$SUM" != "$JSTZ_SHA" ]; then
        echo "ERROR: Incorrect SHA of $JSTZ_PATH. Expected: $JSTZ_SHA, got: $SUM"
        exit 1
    fi

    echo "Installing jsTimezoneDetect..."
    echo "Done"
fi

if [ "$WHAT" = "publickey" ] || [ "$WHAT" = "" ]; then
    echo "Downloading publickey.js..."

    $GET $PKEY_URL $OPT $PKEY_PATH
    if [ ! -f $PKEY_PATH ]; then
        echo "ERROR: Failed to get $PKEY_URL"
        exit 1
    fi

    SUM=`$SHASUM $PKEY_PATH | cut -d " " -f 1`
    if [ "$SUM" != "$PKEY_SHA" ]; then
        echo "ERROR: Incorrect SHA of $PKEY_PATH. Expected: $PKEY_SHA, got: $SUM"
        exit 1
    fi

    echo "Installing publickey.js..."
    echo "Done"
fi

if [ "$WHAT" = "tinymce" ] || [ "$WHAT" = "" ]; then
    echo "Downloading TinyMCE..."

    $GET $TINYMCE_URL $OPT "$TINYMCE_PATH.zip"
    if [ ! -f "$TINYMCE_PATH.zip" ]; then
        echo "ERROR: Failed to get $TINYMCE_URL"
        exit 1
    fi

    SUM=`$SHASUM "$TINYMCE_PATH.zip" | cut -d " " -f 1`
    if [ "$SUM" != "$TINYMCE_SHA" ]; then
        echo "ERROR: Incorrect SHA of $TINYMCE_PATH.zip. Expected: $TINYMCE_SHA, got: $SUM"
        exit 1
    fi

    echo "Installing TinyMCE..."

    $UNZIP -q "$TINYMCE_PATH.zip" -d "$TINYMCE_PATH-$TINYMCE_VERSION"

    if [ -d "$TINYMCE_PATH" ]; then
        rm -drf "$TINYMCE_PATH"
    fi

    mkdir "$TINYMCE_PATH"
    mv -f "$TINYMCE_PATH-$TINYMCE_VERSION/tinymce/js/tinymce" "$TINYMCE_PATH/../"
    # cleanup
    rm -f "$TINYMCE_PATH/license.txt"
    rm -f "$TINYMCE_PATH/jquery.tinymce.min.js"
    rm -rf "$TINYMCE_PATH-$TINYMCE_VERSION"
    rm -f "$TINYMCE_PATH.zip"

    echo "Done"

    echo "Downloading TinyMCE localization..."

    $GET $TINYMCE_LANGS $OPT "$TINYMCE_PATH.zip"

    echo "Installing TinyMCE localization..."

    $UNZIP -q "$TINYMCE_PATH.zip" -d "$TINYMCE_PATH"
    # cleanup
    rm -f "$TINYMCE_PATH.zip"

    echo "Done"
fi

if [ "$WHAT" = "openpgp" ] || [ "$WHAT" = "" ]; then
    echo "Downloading OpenPGP.js..."

    $GET $OPENPGP_URL $OPT "$OPENPGP_PATH.zip"
    if [ ! -f "$OPENPGP_PATH.zip" ]; then
        echo "ERROR: Failed to get $OPENPGP_URL"
        exit 1
    fi

    SUM=`$SHASUM "$OPENPGP_PATH.zip" | cut -d " " -f 1`
    if [ "$SUM" != "$OPENPGP_SHA" ]; then
        echo "ERROR: Incorrect SHA of $OPENPGP_PATH.zip. Expected: $OPENPGP_SHA, got: $SUM"
        exit 1
    fi

    echo "Installing OpenPGP.js..."

    $UNZIP -pq "$OPENPGP_PATH.zip" "openpgpjs-$OPENPGP_VERSION/dist/openpgp.min.js" > $OPENPGP_PATH
    # cleanup
    rm -f "$OPENPGP_PATH.zip"

    echo "Done"
fi

if [ "$WHAT" = "codemirror" ] || [ "$WHAT" = "" ]; then
    echo "Downloading CodeMirror..."

    $GET $CM_URL $OPT "$CM_PATH"
    if [ ! -f "$CM_PATH" ]; then
        echo "ERROR: Failed to get $CM_URL"
        exit 1
    fi

    SUM=`$SHASUM "$CM_PATH" | cut -d " " -f 1`
    if [ "$SUM" != "$CM_SHA" ]; then
        echo "ERROR: Incorrect SHA of $CM_PATH. Expected: $CM_SHA, got: $SUM"
        exit 1
    fi

    echo "Installing CodeMirror..."

    DIR=`dirname "$CM_PATH"`

    # extract only files we use
    $UNZIP -q "$CM_PATH" -d "$DIR" "codemirror-$CM_VERSION/lib/codemirror.css"
    $UNZIP -q "$CM_PATH" -d "$DIR" "codemirror-$CM_VERSION/lib/codemirror.js"
    $UNZIP -q "$CM_PATH" -d "$DIR" "codemirror-$CM_VERSION/addon/selection/active-line.js"
    $UNZIP -q "$CM_PATH" -d "$DIR" "codemirror-$CM_VERSION/mode/sieve/sieve.js"

    if [ -d "$DIR/codemirror" ]; then
        rm -drf "$DIR/codemirror"
    fi

    mv -f "$DIR/codemirror-$CM_VERSION" "$DIR/codemirror"
    #cleanup
    rm -f "$CM_PATH"

    echo "Done"
fi
