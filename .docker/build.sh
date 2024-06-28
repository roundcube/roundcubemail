#!/bin/sh

# Run this from the repo's root directory: `$> ./.docker/build.sh`.

exec docker build -f .docker/Dockerfile -t roundcubemail-testrunner .
