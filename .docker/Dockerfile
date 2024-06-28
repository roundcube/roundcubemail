# Use our production-targetted image as a simple way to get a php-installation
# with all requried dependencies.
FROM roundcube/roundcubemail:latest

RUN apt-get update && apt-get upgrade -y

RUN apt-get install -y --no-install-recommends tini nodejs npm aspell aspell-en aspell-de

# Create working directory
WORKDIR /app

COPY . .

ENV TESTS_MODE desktop
VOLUME /app

# Run the entrypoint script from the mounted code to allow modifications.
# Use tini to pass on process signals correctly (required to abort with CTRL-C,
# etc.).
ENTRYPOINT ["tini", "--", "/app/.docker/docker-entrypoint.sh"]

# By default run the browser tests.
CMD ["vendor/bin/phpunit", "-c", "tests/Browser/phpunit.xml", "--fail-on-warning", "--fail-on-risky", "--exclude-group=failsonga"]
