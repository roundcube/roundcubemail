# No default target to avoid this being mistaken as sufficient preparation for
# running the code (these styles depend on bootstrap, which needs to be
# downloaded, too; see bin/install-jsdeps.sh in the repo's root).

css:
	npx lessc --clean-css="--s1 --advanced" styles/styles.less > styles/styles.min.css
	npx lessc --clean-css="--s1 --advanced" styles/print.less > styles/print.min.css
	npx lessc --clean-css="--s1 --advanced" styles/embed.less > styles/embed.min.css
